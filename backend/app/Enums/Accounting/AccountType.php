<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Cogs = 'cost_of_goods_sold';
    case Expense = 'expense';
    case OtherIncome = 'other_income';
    case OtherExpense = 'other_expense';

    /**
     * Returns true if the account is a credit-normal account.
     */
    public function isCreditNormal(): bool
    {
        return match ($this) {
            self::Liability, self::Equity, self::Revenue, self::OtherIncome => true,
            default => false,
        };
    }
}
