<?php

declare(strict_types=1);

namespace App\Modules\Ai\Tools;

use App\Services\CompanyContext;
use App\Services\PermissionResolver;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCurrentUser implements Tool
{
    public function __construct(
        private readonly CompanyContext $companyContext,
        private readonly PermissionResolver $permissionResolver,
    ) {}

    public function description(): Stringable|string
    {
        return 'Get information about the currently authenticated user, including their name, email, roles, and permissions in the active company';
    }

    public function schema(\Laravel\Ai\JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $user = Auth::user();

        if (! $user) {
            return 'No user is currently authenticated.';
        }

        $company = $this->companyContext->get();
        $roles = $company
            ? $user->rolesForCompany($company->id)->pluck('name')->toArray()
            : [];

        $permissions = $company
            ? $this->permissionResolver->allPermissions($user, $company->id)
            : [];

        return json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_superadmin' => $user->is_superadmin,
            'locale' => $user->locale,
            'company' => $company?->name,
            'roles' => $roles,
            'permissions' => $permissions,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
