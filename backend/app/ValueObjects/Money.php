<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Money ValueObject — wraps bcmath for financial arithmetic.
 *
 * NEVER use native float arithmetic for money. (AGENTS.md §2, Rule 1)
 * All amounts stored as string to avoid IEEE 754 floating-point errors.
 */
final class Money
{
    private readonly string $amount;

    public function __construct(
        string|int $amount,
        private readonly string $currency,
    ) {
        $this->amount = (string) $amount;

        if (! is_numeric($this->amount)) {
            throw new InvalidArgumentException("Invalid money amount: {$this->amount}");
        }
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcadd($this->amount, $other->amount, 2), $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcsub($this->amount, $other->amount, 2), $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency
            && bccomp($this->amount, $other->amount, 2) === 0;
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', 2) === 0;
    }

    public function greaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, 2) > 0;
    }

    public static function zero(string $currency): self
    {
        return new self('0.00', $currency);
    }

    public function lessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, 2) < 0;
    }

    public function negate(): self
    {
        return new self(bcmul($this->amount, '-1', 2), $this->currency);
    }

    public static function fromDecimal(string $amount, string $currency): self
    {
        // Use bcmath to normalise — never cast to float.
        return new self(bcadd($amount, '0', 2), $currency);
    }

    public function __toString(): string
    {
        return "{$this->amount} {$this->currency}";
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} vs {$other->currency}",
            );
        }
    }
}
