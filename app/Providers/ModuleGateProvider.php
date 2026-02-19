<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\ModuleRegistry;
use App\Services\PermissionResolver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ModuleGateProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Superadmin bypasses all gates
        Gate::before(function (User $user) {
            if ($user->is_superadmin) {
                return true;
            }

            return null;
        });

        // Register module-based gates after modules are loaded
        $this->app->booted(function () {
            $registry = app(ModuleRegistry::class);
            $resolver = app(PermissionResolver::class);

            foreach ($registry->allPermissions() as $moduleId => $permissions) {
                foreach (array_keys($permissions) as $permission) {
                    $fullPermission = "{$moduleId}.{$permission}";

                    Gate::define($fullPermission, function (User $user) use ($resolver, $fullPermission) {
                        return $resolver->can($user, $fullPermission);
                    });
                }
            }
        });
    }
}
