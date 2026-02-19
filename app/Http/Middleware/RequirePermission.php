<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has the required permission.
 * Usage: RequirePermission:sales.invoices.create
 */
class RequirePermission
{
    public function __construct(
        private PermissionResolver $permissionResolver,
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        foreach ($permissions as $permission) {
            if (! $this->permissionResolver->can($user, $permission)) {
                abort(403, "Missing permission: {$permission}");
            }
        }

        return $next($request);
    }
}
