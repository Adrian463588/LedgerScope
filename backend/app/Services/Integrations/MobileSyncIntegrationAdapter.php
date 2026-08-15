<?php

declare(strict_types=1);

namespace App\Services\Integrations;

final class MobileSyncIntegrationAdapter extends UnavailableExternalIntegrationAdapter
{
    public function key(): string
    {
        return 'mobile_sync';
    }
}
