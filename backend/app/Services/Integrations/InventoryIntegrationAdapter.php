<?php

declare(strict_types=1);

namespace App\Services\Integrations;

final class InventoryIntegrationAdapter extends UnavailableExternalIntegrationAdapter
{
    public function key(): string
    {
        return 'inventory';
    }
}
