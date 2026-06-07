<?php

declare(strict_types=1);

namespace App\Enums\Audit;

enum WorkingPaperStatus: string
{
    case Draft = 'draft';
    case PreparedBy = 'prepared_by';
    case ReviewedBy = 'reviewed_by';
    case Approved = 'approved';
    case Locked = 'locked';
    case NotStarted = 'not_started';
    case Prepared = 'prepared';
    case ReviewNoteOpen = 'review_note_open';
}
