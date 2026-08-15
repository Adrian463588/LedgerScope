<?php

declare(strict_types=1);

namespace App\Http\Controllers\Future;

use App\Contracts\Integrations\ExternalIntegrationAdapter;
use App\Services\Integrations\IntegrationRegistry;
use Inertia\Inertia;
use Inertia\Response;

final class FutureIntegrationController
{
    public function __construct(private readonly IntegrationRegistry $registry) {}

    public function __invoke(): Response
    {
        $statuses = array_map(
            static fn (ExternalIntegrationAdapter $adapter): array => $adapter->status()->toArray(),
            $this->registry->all(),
        );

        return Inertia::render('Integrations', [
            'statuses' => $statuses,
        ]);
    }
}
