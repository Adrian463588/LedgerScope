<?php

declare(strict_types=1);

namespace App\Jobs\Imports;

use App\Imports\JournalsImport;
use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

final class ImportJournalsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $companyId,
        private readonly int $batchId,
        private readonly string $filePath,
        private readonly int $userId,
    ) {}

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (! $batch) {
            return;
        }

        try {
            Excel::import(new JournalsImport($this->companyId, $batch, $this->userId), $this->filePath, 'private');
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
