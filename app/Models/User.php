<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'locale',
        'timezone',
        'is_active',
        'is_superadmin',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'is_superadmin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('company_id')
            ->withTimestamps();
    }

    public function defaultCompany(): ?Company
    {
        return $this->companies()
            ->wherePivot('is_default', true)
            ->first();
    }

    /**
     * Get roles for a specific company (plus global roles where company_id is null).
     */
    public function rolesForCompany(int $companyId): BelongsToMany
    {
        return $this->roles()
            ->wherePivot('company_id', $companyId)
            ->orWherePivot('company_id', null);
    }

    /**
     * Check if user has a specific permission in a given company context.
     */
    public function hasPermission(string $permission, ?int $companyId = null): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        $roles = $companyId
            ? $this->rolesForCompany($companyId)->get()
            : $this->roles;

        return $roles->contains(fn (Role $role) => $role->hasPermission($permission));
    }
}
