<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
    public function __invoke(Request $request, CompanyContext $companyContext): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $company = $request->user()
            ->companies()
            ->where('companies.id', $validated['company_id'])
            ->firstOrFail();

        $companyContext->set($company);
        $request->session()->put('current_company_id', $company->id);

        return back();
    }
}
