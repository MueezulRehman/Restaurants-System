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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'date',
        'monthly_salary' => 'decimal:2',
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
