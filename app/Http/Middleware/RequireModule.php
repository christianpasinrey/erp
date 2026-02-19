<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures that the specified module is active for the current tenant.
 * Usage: RequireModule:sales or RequireModule:sales,inventory
 */
class RequireModule
{
    public function __construct(
        private ModuleRegistry $moduleRegistry,
    ) {}

    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        foreach ($modules as $module) {
            if (! $this->moduleRegistry->isActive($module)) {
                abort(403, "Module '{$module}' is not active for this tenant.");
            }
        }

        return $next($request);
    }
}
