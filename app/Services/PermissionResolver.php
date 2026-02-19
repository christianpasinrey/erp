<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Resolves permissions for a user in the context of a specific company.
 */
class PermissionResolver
{
    public function __construct(
        private CompanyContext $companyContext,
    ) {}

    /**
     * Check if the user can perform a specific action.
     *
     * @param string $permission Format: module.resource.action (e.g., sales.invoices.create)
     */
    public function can(User $user, string $permission): bool
    {
        if ($user->is_superadmin) {
            return true;
        }

        $companyId = $this->companyContext->id();
        if (! $companyId) {
            return false;
        }

        return $user->hasPermission($permission, $companyId);
    }

    /**
     * Check if the user can perform any of the given actions.
     *
     * @param list<string> $permissions
     */
    public function canAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all effective permissions for a user in the current company.
     *
     * @return list<string>
     */
    public function allPermissions(User $user): array
    {
        if ($user->is_superadmin) {
            return ['*'];
        }

        $companyId = $this->companyContext->id();
        if (! $companyId) {
            return [];
        }

        $roles = $user->rolesForCompany($companyId)->get();

        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $role->permissions ?? []);
        }

        return array_values(array_unique($permissions));
    }
}
