<?php

declare(strict_types=1);

namespace App\Modules;

use App\Models\TenantModule;

/**
 * Central registry for all ERP modules.
 * Tracks which modules are registered and provides access to their metadata.
 */
class ModuleRegistry
{
    /** @var array<string, ModuleServiceProvider> */
    private array $modules = [];

    public function register(string $id, ModuleServiceProvider $provider): void
    {
        $this->modules[$id] = $provider;
    }

    public function get(string $id): ?ModuleServiceProvider
    {
        return $this->modules[$id] ?? null;
    }

    /**
     * Get all registered module providers.
     *
     * @return array<string, ModuleServiceProvider>
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Check if a module is registered in the application.
     */
    public function isRegistered(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    /**
     * Check if a module is active for the current tenant.
     */
    public function isActive(string $id): bool
    {
        if (! $this->isRegistered($id)) {
            return false;
        }

        $tenantModule = TenantModule::query()
            ->where('module', $id)
            ->first();

        return $tenantModule?->is_active ?? false;
    }

    /**
     * Get navigation items from all active modules.
     *
     * @return list<array{label: string, route: string, icon: string, order?: int, module: string}>
     */
    public function navigationItems(): array
    {
        $items = [];

        foreach ($this->modules as $id => $provider) {
            if (! $this->isActive($id)) {
                continue;
            }

            foreach ($provider->navigationItems() as $item) {
                $item['module'] = $id;
                $items[] = $item;
            }
        }

        usort($items, fn ($a, $b) => ($a['order'] ?? 50) <=> ($b['order'] ?? 50));

        return $items;
    }

    /**
     * Get dashboard widgets from all active modules.
     *
     * @return list<array{component: string, order?: int, span?: int, module: string}>
     */
    public function dashboardWidgets(): array
    {
        $widgets = [];

        foreach ($this->modules as $id => $provider) {
            if (! $this->isActive($id)) {
                continue;
            }

            foreach ($provider->dashboardWidgets() as $widget) {
                $widget['module'] = $id;
                $widgets[] = $widget;
            }
        }

        usort($widgets, fn ($a, $b) => ($a['order'] ?? 50) <=> ($b['order'] ?? 50));

        return $widgets;
    }

    /**
     * Collect all permissions from all registered modules.
     *
     * @return array<string, array<string, string>> Keyed by module ID.
     */
    public function allPermissions(): array
    {
        $permissions = [];

        foreach ($this->modules as $id => $provider) {
            $modulePermissions = $provider->permissions();
            if (! empty($modulePermissions)) {
                $permissions[$id] = $modulePermissions;
            }
        }

        return $permissions;
    }
}
