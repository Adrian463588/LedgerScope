<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Audit\DocumentRequestService;
use Illuminate\Console\Command;

/**
 * EPIC 10: Mark overdue document requests.
 * Scheduled to run daily at midnight.
 */
final class MarkOverdueDocumentRequests extends Command
{
    protected $signature = 'pbc:mark-overdue';

    protected $description = 'Mark requested document requests with past due dates as overdue.';

    public function __construct(private readonly DocumentRequestService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->service->markOverdue();

        $this->info("Marked {$count} document request(s) as overdue.");

        return self::SUCCESS;
    }
}
