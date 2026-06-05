<?php

declare(strict_types=1);

namespace App\Enums\Reporting;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Generating = 'generating';
    case Completed = 'completed';
    case Failed = 'failed';
    case Approved = 'approved';
    case Expired = 'expired';
}
