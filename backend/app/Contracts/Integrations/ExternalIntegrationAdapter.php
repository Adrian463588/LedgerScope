<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

interface ExternalIntegrationAdapter
{
    public function key(): string;

    public function status(): IntegrationStatus;

    public function execute(IntegrationRequest $request): IntegrationResult;
}
