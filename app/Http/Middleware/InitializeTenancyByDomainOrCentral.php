<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initializes tenancy on tenant domains, skips on central domains.
 *
 * This allows shared routes (Fortify auth, etc.) to work on both
 * central (erp.test) and tenant (demo.erp.test) domains.
 */
class InitializeTenancyByDomainOrCentral
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getHost(), config('tenancy.central_domains', []))) {
            return $next($request);
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
}
