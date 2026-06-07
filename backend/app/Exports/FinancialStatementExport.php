<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\FinancialStatement;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

final class FinancialStatementExport implements FromArray, WithHeadings
{
    public function __construct(private readonly FinancialStatement $statement) {}

    public function headings(): array
    {
        return ['Account Code', 'Account Name', 'Amount'];
    }

    public function array(): array
    {
        $rows = [];
        $data = $this->statement->data ?? [];

        if ($this->statement->statement_type === 'income_statement') {
            $sections = ['revenue', 'cogs', 'expenses', 'other_income', 'other_expenses'];
            foreach ($sections as $section) {
                if (isset($data[$section]['lines'])) {
                    foreach ($data[$section]['lines'] as $line) {
                        $rows[] = [
                            $line['account_code'] ?? '',
                            $line['account_name'] ?? '',
                            $line['amount'] ?? '0.00',
                        ];
                    }
                }
            }
        } elseif ($this->statement->statement_type === 'balance_sheet') {
            $sections = ['assets', 'liabilities_and_equity'];
            foreach ($sections as $section) {
                if (isset($data[$section]['lines'])) {
                    foreach ($data[$section]['lines'] as $line) {
                        $rows[] = [
                            $line['account_code'] ?? '',
                            $line['account_name'] ?? '',
                            $line['amount'] ?? '0.00',
                        ];
                    }
                }
            }
        }

        return $rows;
    }
}
