<?php

declare(strict_types=1);

namespace App\Services\Integrations;

final class PayrollIntegrationAdapter extends UnavailableExternalIntegrationAdapter
{
    public function key(): string
    {
        return 'payroll';
    }
}
