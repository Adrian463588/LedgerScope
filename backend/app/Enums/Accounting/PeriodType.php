<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum PeriodType: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';
}
