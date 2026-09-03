<?php

namespace App\Console\Commands;

use App\Models\BusinessType;
use App\Models\Module;
use App\Models\Restaurant;
use App\Services\ModuleService;
use Illuminate\Console\Command;

/**
 * Audit business types, their modules, and each business's effective modules.
 *
 * Usage:
 *   php artisan modules:audit
 *   php artisan modules:audit --seed
 *
 * @author Mueez Ul Rehman
 */
class ModulesAuditCommand extends Command
{
    protected $signature = 'modules:audit
                            {--seed : Run ModuleService::ensureDefaults() before auditing}';

    protected $description = 'Print business types → modules and each business effective modules';

    public function handle(): int
    {
        if ($this->option('seed')) {
            $this->info('Seeding default modules and business types…');
            ModuleService::ensureDefaults();
        }

        $this->newLine();
        $this->info('=== MODULES CATALOGUE ===');
        $modules = Module::orderBy('sort_order')->get(['id', 'key', 'name', 'is_active']);
        $this->table(
            ['ID', 'Key', 'Name', 'Active'],
            $modules->map(fn ($m) => [$m->id, $m->key, $m->name, $m->is_active ? 'yes' : 'no'])
        );

        $this->newLine();
        $this->info('=== BUSINESS TYPES → MODULES ===');
        $types = BusinessType::with('modules')->orderBy('sort_order')->orderBy('name')->get();

        if ($types->isEmpty()) {
            $this->warn('No business types found. Run with --seed or create types in Admin.');
        }

        foreach ($types as $type) {
            $keys = $type->modules->pluck('key')->sort()->values()->all();
            $this->line(sprintf(
                '• [%s] %s (%s) — %d module(s)',
                $type->is_active ? 'active' : 'inactive',
                $type->name,
                $type->id,
                count($keys)
            ));
            if ($keys === []) {
                $this->warn('    (no modules linked)');
            } else {
                $this->line('    ' . implode(', ', $keys));
            }
        }

        $this->newLine();
        $this->info('=== BUSINESSES → EFFECTIVE MODULES ===');
        $businesses = Restaurant::with(['businessType.modules', 'subscription.plan'])
            ->orderBy('id')
            ->get();

        if ($businesses->isEmpty()) {
            $this->warn('No businesses found.');
        }

        foreach ($businesses as $biz) {
            $typeName = $biz->businessType?->name ?? '—';
            $planName = $biz->subscription?->plan?->name ?? ($biz->plan ?? '—');
            $maxModules = $biz->subscription?->plan?->max_modules;

            $stored = is_array($biz->enabled_modules) ? $biz->enabled_modules : [];
            $effective = $biz->getEnabledModules()->pluck('key')->sort()->values()->all();

            $source = count($stored) > 0 ? 'enabled_modules column' : 'business type fallback';

            $this->line(sprintf(
                '• #%d %s | type=%s | plan=%s | status=%s',
                $biz->id,
                $biz->name,
                $typeName,
                $planName,
                $biz->status
            ));
            $this->line('    source: ' . $source);
            $this->line('    stored keys (' . count($stored) . '): ' . (count($stored) ? implode(', ', $stored) : '—'));
            $this->line('    effective (' . count($effective) . '): ' . (count($effective) ? implode(', ', $effective) : '—'));

            if ($maxModules && $maxModules > 0 && count($effective) > (int) $maxModules) {
                $this->error(sprintf(
                    '    ⚠ OVER PLAN LIMIT: %d modules enabled, plan max is %d',
                    count($effective),
                    (int) $maxModules
                ));
            }
        }

        $this->newLine();
        $this->info('Audit complete.');

        return self::SUCCESS;
    }
}
