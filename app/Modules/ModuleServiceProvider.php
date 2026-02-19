<?php

declare(strict_types=1);

namespace App\Modules;

use Illuminate\Support\ServiceProvider;

/**
 * Base class for all ERP module service providers.
 * Each module (Sales, Inventory, etc.) extends this and defines its configuration.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
        }

        $this->registerRoutes();
        $this->registerEvents();

        app(ModuleRegistry::class)->register($this->moduleId(), $this);
    }

    /**
     * Unique module identifier (e.g., 'sales', 'inventory', 'hrm').
     */
    abstract public function moduleId(): string;

    /**
     * Human-readable module name.
     */
    abstract public function moduleName(): string;

    /**
     * Module dependencies (other module IDs that must be active).
     *
     * @return list<string>
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * Permissions this module declares.
     * Format: ['resource.action' => 'Description']
     *
     * @return array<string, string>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Frontend navigation items this module contributes.
     *
     * @return list<array{label: string, route: string, icon: string, order?: int}>
     */
    public function navigationItems(): array
    {
        return [];
    }

    /**
     * Dashboard widgets this module provides.
     *
     * @return list<array{component: string, order?: int, span?: int}>
     */
    public function dashboardWidgets(): array
    {
        return [];
    }

    /**
     * Override to register module-specific routes.
     */
    protected function registerRoutes(): void {}

    /**
     * Override to register module-specific event listeners.
     */
    protected function registerEvents(): void {}

    /**
     * Override to register module-specific config.
     */
    protected function registerConfig(): void {}

    /**
     * Override to register module-specific migrations.
     */
    protected function registerMigrations(): void {}
}
