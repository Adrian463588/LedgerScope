<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

final readonly class IntegrationRequest
{
    /** @param array<string, mixed> $parameters */
    public function __construct(
        public string $operation,
        public array $parameters = [],
    ) {}
}
