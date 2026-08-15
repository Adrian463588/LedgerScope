<?php

declare(strict_types=1);

use App\Support\Decimal;

it('keeps financial arithmetic as decimal strings', function (): void {
    expect(Decimal::add('0.10', '0.20'))->toBe('0.30')
        ->and(Decimal::subtract('10.00', '3.25'))->toBe('6.75')
        ->and(Decimal::multiply('12.50', '2'))->toBe('25.00');
});

it('returns null for an unavailable ratio denominator', function (): void {
    expect(Decimal::divide('10.00', '0.00'))->toBeNull()
        ->and(Decimal::percentage('10.00', '0.00'))->toBeNull();
});

it('formats decimal values without converting them to floats', function (): void {
    expect(Decimal::format('1234567.5'))->toBe('1,234,567.50')
        ->and(Decimal::format('-12.5'))->toBe('-12.50');
});
