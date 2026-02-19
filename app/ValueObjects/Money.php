<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable Money value object.
 * Stores amounts as integer cents to avoid floating-point issues.
 */
final class Money implements JsonSerializable, Stringable
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency = 'EUR',
    ) {}

    /**
     * Create from a decimal value (e.g., 19.99 → 1999 cents).
     */
    public static function fromDecimal(float|string $value, string $currency = 'EUR', int $precision = 2): self
    {
        return new self(
            amount: (int) round((float) $value * (10 ** $precision)),
            currency: $currency,
        );
    }

    /**
     * Create a zero-amount Money instance.
     */
    public static function zero(string $currency = 'EUR'): self
    {
        return new self(0, $currency);
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float|int $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function divide(float|int $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide by zero.');
        }

        return new self((int) round($this->amount / $divisor), $this->currency);
    }

    /**
     * Convert to decimal for display (e.g., 1999 → 19.99).
     */
    public function toDecimal(int $precision = 2): float
    {
        return $this->amount / (10 ** $precision);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function greaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function lessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount < $other->amount;
    }

    public function negate(): self
    {
        return new self(-$this->amount, $this->currency);
    }

    public function abs(): self
    {
        return new self(abs($this->amount), $this->currency);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public function __toString(): string
    {
        return number_format($this->toDecimal(), 2, '.', '') . ' ' . $this->currency;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
