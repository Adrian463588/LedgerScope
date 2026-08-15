<?php

declare(strict_types=1);

namespace App\Services\Integrations;

use App\Contracts\Integrations\ExternalIntegrationAdapter;
use App\Contracts\Integrations\IntegrationRequest;
use App\Contracts\Integrations\IntegrationResult;
use App\Contracts\Integrations\IntegrationStatus;
use App\Exceptions\FeatureUnavailableException;

abstract class UnavailableExternalIntegrationAdapter implements ExternalIntegrationAdapter
{
    final public function status(): IntegrationStatus
    {
        $config = config("ledgerscope.integrations.{$this->key()}", []);
        $declared = is_array($config) && (bool) ($config['enabled'] ?? false);

        return new IntegrationStatus(
            key: $this->key(),
            mode: 'unavailable',
            configured: false,
            message: $declared
                ? 'The provider is declared but no validated adapter or credentials are available.'
                : 'No provider adapter or credentials are configured for this integration.',
        );
    }

    final public function execute(IntegrationRequest $request): IntegrationResult
    {
        $status = $this->status();

        throw new FeatureUnavailableException(
            "Integration [{$status->key}] is unavailable for operation [{$request->operation}]. Configure and validate a provider adapter before use.",
        );
    }
}
