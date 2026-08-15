<?php

namespace Tests;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\FiscalYear;
use App\Models\TrialBalance;
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
 * @property FiscalYear $fy
 * @property AccountingPeriod $period
 * @property TrialBalance $tb
 * @property ChartOfAccount $cash
 * @property ChartOfAccount $revenue
 */
abstract class TestCase extends BaseTestCase
{
    //
}
