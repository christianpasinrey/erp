<?php

declare(strict_types=1);

namespace App\Modules\Ai\Tools;

use App\Services\CompanyContext;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCompanyInfo implements Tool
{
    public function __construct(
        private readonly CompanyContext $companyContext,
    ) {}

    public function description(): Stringable|string
    {
        return 'Get information about the currently active company (name, legal name, currency, country, timezone, etc.)';
    }

    public function schema(\Laravel\Ai\JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $company = $this->companyContext->get();

        if (! $company) {
            return 'No company is currently selected.';
        }

        return json_encode([
            'id' => $company->id,
            'name' => $company->name,
            'legal_name' => $company->legal_name,
            'tax_id' => $company->tax_id,
            'currency' => $company->currency_code,
            'country' => $company->country_code,
            'locale' => $company->locale,
            'timezone' => $company->timezone,
            'fiscal_year_start' => $company->fiscal_year_start,
            'email' => $company->email,
            'phone' => $company->phone,
            'website' => $company->website,
            'is_active' => $company->is_active,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
