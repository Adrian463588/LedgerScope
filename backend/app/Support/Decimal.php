<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Decimal arithmetic for non-currency financial calculations.
 *
 * Values stay as strings throughout the calculation so callers never fall
 * back to IEEE-754 floating point arithmetic.
 */
final class Decimal
{
    public static function normalize(string|int $value, int $scale = 2): string
    {
        self::assertNumeric($value);

        return bcadd((string) $value, '0', $scale);
    }

    public static function add(string $left, string $right, int $scale = 2): string
    {
        return bcadd($left, $right, $scale);
    }

    public static function subtract(string $left, string $right, int $scale = 2): string
    {
        return bcsub($left, $right, $scale);
    }

    public static function multiply(string $left, string $right, int $scale = 2): string
    {
        return bcmul($left, $right, $scale);
    }

    public static function divide(string $numerator, string $denominator, int $scale = 6): ?string
    {
        if (bccomp($denominator, '0', $scale) === 0) {
            return null;
        }

        return bcdiv($numerator, $denominator, $scale);
    }

    public static function percentage(string $numerator, string $denominator, int $scale = 6): ?string
    {
        $ratio = self::divide($numerator, $denominator, $scale);

        return $ratio === null ? null : self::multiply($ratio, '100', $scale);
    }

    public static function compare(string $left, string $right, int $scale = 2): int
    {
        return bccomp($left, $right, $scale);
    }

    public static function absolute(string $value, int $scale = 2): string
    {
        return self::compare($value, '0', $scale) < 0
            ? self::multiply($value, '-1', $scale)
            : self::normalize($value, $scale);
    }

    public static function format(string $value, int $scale = 2): string
    {
        $normalised = self::normalize($value, $scale);
        $negative = str_starts_with($normalised, '-');
        $absolute = ltrim($normalised, '-');
        [$whole, $fraction] = array_pad(explode('.', $absolute, 2), 2, '');
        $whole = ltrim($whole, '0') ?: '0';
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole) ?? $whole;

        return ($negative ? '-' : '').$whole.($scale > 0 ? '.'.$fraction : '');
    }

    private static function assertNumeric(string|int $value): void
    {
        if (! is_numeric((string) $value)) {
            throw new InvalidArgumentException("Invalid decimal value: {$value}");
        }
    }
}
