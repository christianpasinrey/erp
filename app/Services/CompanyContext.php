<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;

/**
 * Holds the currently active company for the request lifecycle.
 * Registered as a singleton in the service container.
 */
class CompanyContext
{
    private ?Company $company = null;

    public function set(Company $company): void
    {
        $this->company = $company;
    }

    public function get(): ?Company
    {
        return $this->company;
    }

    public function id(): ?int
    {
        return $this->company?->id;
    }

    public function currencyCode(): string
    {
        return $this->company?->currency_code ?? 'EUR';
    }

    public function check(): bool
    {
        return $this->company !== null;
    }

    public function reset(): void
    {
        $this->company = null;
    }
}
