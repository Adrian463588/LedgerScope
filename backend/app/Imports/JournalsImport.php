<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\Accounting\JournalSourceType;
use App\Enums\Accounting\JournalStatus;
use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\ValueObjects\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class JournalsImport implements ToCollection, WithHeadingRow
{
    private array $errors = [];
    private int $successCount = 0;
    private int $failedCount = 0;

    public function __construct(
        private readonly int $companyId,
        private readonly ImportBatch $batch,
        private readonly int $userId
    ) {}

    public function collection(Collection $rows): void
    {
        $this->batch->update([
            'status' => 'processing',
            'started_at' => now(),
            'total_rows' => $rows->count(),
        ]);

        // Group rows by reference, or (date + description) if reference is empty
        $groups = [];
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $row['row_num'] = $rowNum;

            $date = isset($row['journal_date']) ? trim((string) $row['journal_date']) : '';
            $ref = isset($row['reference']) ? trim((string) $row['reference']) : '';
            $desc = isset($row['description']) ? trim((string) $row['description']) : '';

            $groupKey = ! empty($ref) ? 'ref_' . $ref : 'date_desc_' . md5($date . '_' . $desc);
            $groups[$groupKey][] = $row;
        }

        foreach ($groups as $groupKey => $groupRows) {
            $firstRow = $groupRows[0];
            $rowNums = array_column($groupRows, 'row_num');
            $rowNumbersStr = 'Rows ' . implode(', ', $rowNums);

            $dateStr = isset($firstRow['journal_date']) ? trim((string) $firstRow['journal_date']) : '';
            $ref = isset($firstRow['reference']) ? trim((string) $firstRow['reference']) : null;
            $desc = isset($firstRow['description']) ? trim((string) $firstRow['description']) : '';

            if (empty($dateStr) || empty($desc)) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: Journal Date and Description are required.";
                continue;
            }

            try {
                $date = Carbon::parse($dateStr);
            } catch (\Throwable) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: Invalid date format '{$dateStr}'.";
                continue;
            }

            // Find matching accounting period
            $period = AccountingPeriod::where('company_id', $this->companyId)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();

            if (! $period) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: No accounting period found for date {$date->toDateString()}.";
                continue;
            }

            if (! $period->isOpen()) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: Accounting period for date {$date->toDateString()} is locked or closed.";
                continue;
            }

            if (count($groupRows) < 2) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: Journal entry must have at least 2 lines.";
                continue;
            }

            try {
                DB::transaction(function () use ($period, $date, $ref, $desc, $groupRows): void {
                    // Check debit/credit balance
                    $totalDebit = Money::zero('IDR');
                    $totalCredit = Money::zero('IDR');
                    $linesToCreate = [];

                    foreach ($groupRows as $row) {
                        $code = isset($row['account_code']) ? trim((string) $row['account_code']) : '';
                        if (empty($code)) {
                            throw new \Exception("Account code is missing on one of the lines.");
                        }

                        $account = ChartOfAccount::where('company_id', $this->companyId)
                            ->where('account_code', $code)
                            ->first();

                        if (! $account) {
                            throw new \Exception("Account with code '{$code}' not found.");
                        }

                        if (! $account->is_active) {
                            throw new \Exception("Account '{$code}' is inactive.");
                        }

                        $debit = isset($row['debit']) ? (string) $row['debit'] : '0';
                        $credit = isset($row['credit']) ? (string) $row['credit'] : '0';
                        $lineDesc = isset($row['line_description']) ? trim((string) $row['line_description']) : null;

                        $totalDebit = $totalDebit->add(new Money($debit, 'IDR'));
                        $totalCredit = $totalCredit->add(new Money($credit, 'IDR'));

                        $linesToCreate[] = [
                            'account_id' => $account->id,
                            'description' => $lineDesc,
                            'debit' => $debit,
                            'credit' => $credit,
                            'currency' => 'IDR',
                        ];
                    }

                    if (! $totalDebit->equals($totalCredit)) {
                        throw new \Exception("Journal does not balance. Debit: {$totalDebit} | Credit: {$totalCredit}");
                    }

                    /** @var JournalEntry $journal */
                    $journal = JournalEntry::create([
                        'company_id' => $this->companyId,
                        'accounting_period_id' => $period->id,
                        'description' => $desc,
                        'journal_date' => $date->toDateString(),
                        'reference' => $ref,
                        'source_type' => JournalSourceType::Import->value,
                        'status' => JournalStatus::Draft->value,
                        'created_by' => $this->userId,
                    ]);

                    foreach ($linesToCreate as $lineData) {
                        $journal->lines()->create($lineData);
                    }

                    $this->successCount += count($groupRows);
                });
            } catch (\Throwable $e) {
                $this->failedCount += count($groupRows);
                $this->errors[] = "{$rowNumbersStr}: " . $e->getMessage();
            }
        }

        $this->batch->update([
            'status' => $this->failedCount === $rows->count() ? 'failed' : 'completed',
            'success_rows' => $this->successCount,
            'failed_rows' => $this->failedCount,
            'error_message' => empty($this->errors) ? null : implode("\n", $this->errors),
            'completed_at' => now(),
        ]);
    }
}
