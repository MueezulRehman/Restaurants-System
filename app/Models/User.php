<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'password',
        'monthly_salary',
        'is_active',
        'joined_at',
        'restaurant_id',
        'module_access',
        'last_login_at',
        'last_logout_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'date',
        'monthly_salary' => 'decimal:2',
        'module_access' => 'array',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isRestaurantManager(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }

    /**
     * Whether this user is a plain "manager" account (as opposed to the
     * restaurant "admin"/owner). Managers are the ones subject to
     * per-module access grants set by the admin.
     */
    public function isManagerRole(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * List of module keys this manager has explicitly been granted access
     * to (set by the restaurant admin in Staff management). Always an
     * array, never null.
     */
    public function getModuleAccessList(): array
    {
        return is_array($this->module_access) ? $this->module_access : [];
    }

    /**
     * The restaurant this user is currently acting on behalf of.
     *
     * For restaurant staff (admin/manager) this is always their own
     * restaurant. For a super admin, this is the restaurant they've
     * "entered" via Tenancy::enter() — or null if they're on a genuine
     * platform-wide screen and haven't entered any restaurant.
     *
     * Controllers that need "the restaurant I'm working with right now"
     * should call this instead of reading ->restaurant directly, so they
     * work correctly both for real restaurant staff and for an
     * impersonating super admin.
     */
    public function effectiveRestaurant(): ?Restaurant
    {
        if ($this->isSuperAdmin()) {
            return \App\Support\Tenancy::impersonatedRestaurant();
        }

        return $this->restaurant;
    }

    public function effectiveRestaurantId(): ?int
    {
        return $this->effectiveRestaurant()?->id;
    }

    /**
     * Whether this user can use a given module right now.
     *
     * - A super admin only has module access while they've entered a
     *   specific restaurant (Tenancy) — and then only for modules that
     *   restaurant actually has enabled, exactly like that restaurant's
     *   own admin would see.
     * - Restaurant admins (owners) are only limited by whether the
     *   restaurant itself has the module enabled.
     * - Managers additionally need to have been explicitly granted that
     *   module by the admin — no grant means no access, even if the
     *   restaurant has the module switched on.
     */
    public function hasModuleAccess(string $moduleKey): bool
    {
        $restaurant = $this->effectiveRestaurant();

        if (! $restaurant) {
            return false;
        }

        // Business must have the module enabled first
        if (! $restaurant->isModuleEnabled($moduleKey)) {
            return false;
        }

        // Super admin (impersonating) or restaurant owner/admin: full business modules
        if ($this->isSuperAdmin() || $this->role === 'admin') {
            return true;
        }

        // Managers require explicit grants from the restaurant admin.
        $granted = $this->getModuleAccessList();

        if ($granted === []) {
            return false;
        }

        if (in_array($moduleKey, $granted, true)) {
            return true;
        }

        // Bundle aliases (one grant unlocks related module keys)
        $aliasMap = [
            'pharmacy' => ['medical', 'inventory', 'stock', 'pos', 'medical-records', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'pharmacy', 'medicines'],
            'general_store' => ['inventory', 'stock', 'pos', 'categories', 'variants', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'general_store', 'menu'],
            'restaurant' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'customers', 'cashbook', 'expenses', 'reports', 'tables', 'feedback', 'allergies'],
            'inventory' => ['stock', 'menu', 'categories', 'variants', 'inventory'],
            'menu' => ['menu', 'categories', 'inventory'],
            'stock' => ['stock', 'inventory'],
        ];

        foreach ($granted as $grant) {
            $expanded = $aliasMap[$grant] ?? [];
            if (in_array($moduleKey, $expanded, true)) {
                return true;
            }
        }

        return false;
    }

    public function canGenerateReportType(string $type): bool
    {
        if (! $this->hasModuleAccess('reports')) {
            return false;
        }

        return match ($type) {
            'orders', 'sales' => $this->hasModuleAccess('orders'),
            'inventory' => $this->hasModuleAccess('stock'),
            'financial' => $this->hasModuleAccess('cashbook') || $this->hasModuleAccess('expenses'),
            'staff' => $this->hasModuleAccess('staff') || $this->hasModuleAccess('hr'),
            'delivery' => $this->hasModuleAccess('delivery'),
            default => false,
        };
    }

    public function getAvailableReportTypes(): array
    {
        $types = [];

        if ($this->canGenerateReportType('orders')) {
            $types['orders'] = 'Orders';
            $types['sales'] = 'Sales';
        }

        if ($this->canGenerateReportType('inventory')) {
            $types['inventory'] = 'Inventory';
        }

        if ($this->canGenerateReportType('financial')) {
            $types['financial'] = 'Financial';
        }

        if ($this->canGenerateReportType('staff')) {
            $types['staff'] = 'Staff';
        }

        if ($this->canGenerateReportType('delivery')) {
            $types['delivery'] = 'Delivery';
        }

        return $types;
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }
}
