<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active company from the request (header, session, or default).
 * Must run after authentication.
 */
class ResolveCompany
{
    public function __construct(
        private CompanyContext $companyContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Priority: X-Company-Id header > session > user's default company > first company
        $companyId = $request->header('X-Company-Id')
            ?? $request->session()->get('current_company_id');

        if ($companyId) {
            $company = $user->companies()->where('companies.id', $companyId)->first();
        }

        if (! isset($company) || ! $company) {
            $company = $user->defaultCompany()
                ?? $user->companies()->first();
        }

        if ($company) {
            $this->companyContext->set($company);
            $request->session()->put('current_company_id', $company->id);
        }

        return $next($request);
    }
}
