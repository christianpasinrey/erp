<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\ModuleRegistry;
use App\Services\CompanyContext;
use App\Services\PermissionResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'erp' => fn () => $this->erpData($request),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function erpData(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Skip ERP data on central domain (no tenant context)
        if (! tenancy()->initialized) {
            return null;
        }

        $companyContext = app(CompanyContext::class);
        $moduleRegistry = app(ModuleRegistry::class);
        $permissionResolver = app(PermissionResolver::class);

        $currentCompany = $companyContext->get();

        return [
            'currentCompany' => $currentCompany ? [
                'id' => $currentCompany->id,
                'name' => $currentCompany->name,
                'legal_name' => $currentCompany->legal_name,
                'currency_code' => $currentCompany->currency_code,
                'country_code' => $currentCompany->country_code,
                'logo_path' => $currentCompany->logo_path,
                'is_active' => $currentCompany->is_active,
            ] : null,
            'companies' => $user->companies()->select([
                'companies.id',
                'companies.name',
                'companies.legal_name',
                'companies.currency_code',
                'companies.country_code',
                'companies.logo_path',
                'companies.is_active',
            ])->get(),
            'activeModules' => collect($moduleRegistry->all())
                ->keys()
                ->filter(fn (string $id) => $moduleRegistry->isActive($id))
                ->values()
                ->all(),
            'moduleNav' => $moduleRegistry->navigationItems(),
            'permissions' => $permissionResolver->allPermissions($user),
        ];
    }
}
