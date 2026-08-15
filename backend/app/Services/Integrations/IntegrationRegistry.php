<?php

declare(strict_types=1);

namespace App\Services\Integrations;

use App\Contracts\Integrations\ExternalIntegrationAdapter;
use InvalidArgumentException;

final class IntegrationRegistry
{
    /** @param iterable<ExternalIntegrationAdapter> $adapters */
    public function __construct(private readonly iterable $adapters) {}

    /** @return list<ExternalIntegrationAdapter> */
    public function all(): array
    {
        return array_values(is_array($this->adapters) ? $this->adapters : iterator_to_array($this->adapters));
    }

    public function get(string $key): ExternalIntegrationAdapter
    {
        foreach ($this->all() as $adapter) {
            if ($adapter->key() === $key) {
                return $adapter;
            }
        }

        throw new InvalidArgumentException("Unknown integration [{$key}].");
    }
}
