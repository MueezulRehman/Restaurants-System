<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'role', 'password',
        'monthly_salary', 'is_active', 'joined_at', 'restaurant_id',
        'module_access',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'date',
        'monthly_salary' => 'decimal:2',
        'module_access' => 'array',
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
     * Whether this user can use a given module right now.
     *
     * - Super admins / restaurant admins (owners) are only limited by
     *   whether the restaurant itself has the module enabled.
     * - Managers additionally need to have been explicitly granted that
     *   module by the admin — no grant means no access, even if the
     *   restaurant has the module switched on.
     */
    public function hasModuleAccess(string $moduleKey): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $restaurant = $this->restaurant;

        if (! $restaurant || ! $restaurant->isModuleEnabled($moduleKey)) {
            return false;
        }

        if (! $this->isManagerRole()) {
            // Restaurant admin/owner: restaurant-level toggle is enough.
            return true;
        }

        return in_array($moduleKey, $this->getModuleAccessList(), true);
    }

    public function getAccessibleModules()
    {
        $restaurant = $this->restaurant;

        if (! $restaurant) {
            return collect();
        }

        $enabledModules = $restaurant->getEnabledModules();

        if (! $this->isManagerRole()) {
            return $enabledModules;
        }

        $grantedKeys = $this->getModuleAccessList();

        return $enabledModules->filter(fn ($module) => in_array($module->key, $grantedKeys, true));
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
