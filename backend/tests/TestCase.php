<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property \App\Models\User $admin
 * @property \App\Models\User $superAdmin
 * @property \App\Models\User $lead
 * @property \App\Models\Company $company
 * @property \App\Models\Engagement $engagement
 * @property \App\Services\Audit\WorkingPaperService $service
 */
abstract class TestCase extends BaseTestCase
{
    //
}
