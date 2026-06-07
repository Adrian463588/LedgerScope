<?php

namespace Tests;

use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Audit\WorkingPaperService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property User $admin
 * @property User $superAdmin
 * @property User $lead
 * @property Company $company
 * @property Engagement $engagement
 * @property WorkingPaperService $service
 * @property User $user
 * @property \App\Models\FiscalYear $fy
 * @property \App\Models\AccountingPeriod $period
 * @property \App\Models\TrialBalance $tb
 * @property \App\Models\ChartOfAccount $cash
 * @property \App\Models\ChartOfAccount $revenue
 */
abstract class TestCase extends BaseTestCase
{
    //
}
