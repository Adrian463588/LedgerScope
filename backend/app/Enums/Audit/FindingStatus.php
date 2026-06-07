<?php

declare(strict_types=1);

namespace App\Enums\Audit;

enum FindingStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case PendingReview = 'pending_review';
    case ManagementResponsePending = 'management_response_pending';
    case ActionPlanAgreed = 'action_plan_agreed';
    case Overdue = 'overdue';
}
