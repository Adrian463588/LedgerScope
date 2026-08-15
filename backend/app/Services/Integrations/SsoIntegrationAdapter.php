<?php

declare(strict_types=1);

namespace App\Services\Integrations;

final class SsoIntegrationAdapter extends UnavailableExternalIntegrationAdapter
{
    public function key(): string
    {
        return 'sso';
    }
}
