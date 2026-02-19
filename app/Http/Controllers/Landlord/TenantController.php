<?php

declare(strict_types=1);

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreTenantRequest;
use App\Http\Requests\Landlord\UpdateTenantModulesRequest;
use App\Http\Requests\Landlord\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Modules\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::with('domains')->get()->map(fn (Tenant $tenant) => [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'plan' => $tenant->plan,
            'max_users' => $tenant->max_users,
            'max_companies' => $tenant->max_companies,
            'is_active' => $tenant->is_active,
            'trial_ends_at' => $tenant->trial_ends_at?->toISOString(),
            'domains' => $tenant->domains->pluck('domain'),
            'created_at' => $tenant->created_at->toISOString(),
        ]);

        return Inertia::render('Landlord/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Landlord/Tenants/Create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $domain = $data['domain'];
        unset($data['domain']);

        $tenant = Tenant::create($data);
        $tenant->domains()->create(['domain' => $domain]);

        return redirect()->route('landlord.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' created.");
    }

    public function edit(string $tenantId): Response
    {
        $tenant = Tenant::with('domains')->findOrFail($tenantId);

        return Inertia::render('Landlord/Tenants/Edit', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->plan,
                'max_users' => $tenant->max_users,
                'max_companies' => $tenant->max_companies,
                'is_active' => $tenant->is_active,
                'trial_ends_at' => $tenant->trial_ends_at?->format('Y-m-d'),
                'domains' => $tenant->domains->pluck('domain'),
            ],
        ]);
    }

    public function update(UpdateTenantRequest $request, string $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update($request->validated());

        return redirect()->route('landlord.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' updated.");
    }

    public function destroy(string $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['is_active' => false]);

        return redirect()->route('landlord.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' deactivated.");
    }

    public function modules(string $tenantId, ModuleRegistry $moduleRegistry): Response
    {
        $tenant = Tenant::findOrFail($tenantId);

        // Get all registered modules from the application
        $registeredModules = collect($moduleRegistry->all())->map(fn ($provider, $id) => [
            'id' => $id,
            'name' => $provider->moduleName(),
        ])->values()->all();

        // Also include well-known modules that may not be booted in central context
        $knownModules = [
            ['id' => 'ai', 'name' => 'AI Assistant'],
            ['id' => 'contacts', 'name' => 'Contacts'],
            ['id' => 'settings', 'name' => 'Settings'],
            ['id' => 'sales', 'name' => 'Sales'],
            ['id' => 'purchasing', 'name' => 'Purchasing'],
            ['id' => 'inventory', 'name' => 'Inventory'],
            ['id' => 'accounting', 'name' => 'Accounting'],
            ['id' => 'crm', 'name' => 'CRM'],
            ['id' => 'hrm', 'name' => 'HRM'],
            ['id' => 'manufacturing', 'name' => 'Manufacturing'],
            ['id' => 'projects', 'name' => 'Projects'],
        ];

        // Merge: registered modules override known modules
        $allModules = collect($knownModules);
        foreach ($registeredModules as $mod) {
            if (! $allModules->contains('id', $mod['id'])) {
                $allModules->push($mod);
            }
        }

        // Get active modules from tenant DB
        $activeModules = [];
        $tenant->run(function () use (&$activeModules) {
            $activeModules = TenantModule::pluck('is_active', 'module')->toArray();
        });

        return Inertia::render('Landlord/Tenants/Modules', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'modules' => $allModules->map(fn ($mod) => [
                ...$mod,
                'is_active' => $activeModules[$mod['id']] ?? false,
            ])->values()->all(),
        ]);
    }

    public function updateModules(UpdateTenantModulesRequest $request, string $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $modules = $request->validated('modules');

        $tenant->run(function () use ($modules) {
            foreach ($modules as $moduleId => $isActive) {
                TenantModule::updateOrCreate(
                    ['module' => $moduleId],
                    [
                        'is_active' => (bool) $isActive,
                        'activated_at' => $isActive ? now() : null,
                    ],
                );
            }
        });

        return redirect()->route('landlord.tenants.modules', $tenantId)
            ->with('success', 'Modules updated.');
    }
}
