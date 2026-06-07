<?php

declare(strict_types=1);

namespace App\Jobs\Imports;

use App\Imports\AccountsImport;
use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

final class ImportAccountsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $companyId,
        private readonly int $batchId,
        private readonly string $filePath
    ) {}

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (! $batch) {
            return;
        }

        try {
            $realPath = Storage::disk('local')->path($this->filePath);
            Excel::import(new AccountsImport($this->companyId, $batch), $realPath);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
