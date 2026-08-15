<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\ChartOfAccount;
use App\Models\ImportBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class AccountsImport implements ToCollection, WithHeadingRow
{
    private array $errors = [];

    private int $successCount = 0;

    private int $failedCount = 0;

    public function __construct(
        private readonly int $companyId,
        private readonly ImportBatch $batch,
    ) {}

    public function collection(Collection $rows): void
    {
        $this->batch->update([
            'status' => 'processing',
            'started_at' => now(),
            'total_rows' => $rows->count(),
        ]);

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Heading is row 1
            $code = isset($row['account_code']) ? trim((string) $row['account_code']) : null;
            $name = isset($row['account_name']) ? trim((string) $row['account_name']) : null;
            $type = isset($row['account_type']) ? trim(strtolower((string) $row['account_type'])) : null;
            $description = isset($row['description']) ? trim((string) $row['description']) : null;
            $parentCode = isset($row['parent_code']) ? trim((string) $row['parent_code']) : null;

            if (empty($code) || empty($name) || empty($type)) {
                $this->failedCount++;
                $this->errors[] = "Row {$rowNum}: Account Code, Name, and Type are required.";

                continue;
            }

            $validTypes = ['asset', 'liability', 'equity', 'revenue', 'cost_of_goods_sold', 'expense', 'other_income', 'other_expense'];
            if (! in_array($type, $validTypes, true)) {
                $this->failedCount++;
                $this->errors[] = "Row {$rowNum}: Invalid account type '{$type}'. Must be one of ".implode(', ', $validTypes);

                continue;
            }

            try {
                DB::transaction(function () use ($code, $name, $type, $description, $parentCode, $rowNum): void {
                    // Check duplicate code
                    $existing = ChartOfAccount::where('company_id', $this->companyId)
                        ->where('account_code', $code)
                        ->first();

                    if ($existing) {
                        throw new \Exception("Account code '{$code}' already exists.");
                    }

                    $parentId = null;
                    if (! empty($parentCode)) {
                        $parent = ChartOfAccount::where('company_id', $this->companyId)
                            ->where('account_code', $parentCode)
                            ->first();
                        if ($parent) {
                            $parentId = $parent->id;
                        } else {
                            $this->errors[] = "Row {$rowNum} Warning: Parent account with code '{$parentCode}' not found. Set as top-level.";
                        }
                    }

                    ChartOfAccount::create([
                        'company_id' => $this->companyId,
                        'account_code' => $code,
                        'account_name' => $name,
                        'account_type' => $type,
                        'description' => $description,
                        'parent_id' => $parentId,
                        'is_active' => true,
                    ]);

                    $this->successCount++;
                });
            } catch (\Throwable $e) {
                $this->failedCount++;
                $this->errors[] = "Row {$rowNum}: ".$e->getMessage();
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
