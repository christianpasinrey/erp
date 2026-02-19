<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use App\ValueObjects\Money;
use InvalidArgumentException;

class CurrencyService
{
    /**
     * Convert a Money amount from one currency to another using the latest exchange rate.
     */
    public function convert(Money $amount, string $targetCurrency, ?\DateTimeInterface $date = null): Money
    {
        if ($amount->currency === $targetCurrency) {
            return $amount;
        }

        $rate = $this->getRate($amount->currency, $targetCurrency, $date);

        return new Money(
            amount: (int) round($amount->amount * $rate),
            currency: $targetCurrency,
        );
    }

    /**
     * Get the exchange rate between two currencies.
     */
    public function getRate(string $base, string $target, ?\DateTimeInterface $date = null): float
    {
        $query = ExchangeRate::query()
            ->where('base_currency', $base)
            ->where('target_currency', $target);

        if ($date) {
            $query->where('effective_date', '<=', $date);
        }

        $rate = $query->orderByDesc('effective_date')->first();

        if (! $rate) {
            // Try inverse
            $inverseQuery = ExchangeRate::query()
                ->where('base_currency', $target)
                ->where('target_currency', $base);

            if ($date) {
                $inverseQuery->where('effective_date', '<=', $date);
            }

            $inverse = $inverseQuery->orderByDesc('effective_date')->first();

            if (! $inverse) {
                throw new InvalidArgumentException(
                    "No exchange rate found for {$base}/{$target}"
                );
            }

            return 1 / (float) $inverse->rate;
        }

        return (float) $rate->rate;
    }
}
