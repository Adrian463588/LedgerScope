<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

final readonly class IntegrationStatus
{
    public function __construct(
        public string $key,
        public string $mode,
        public bool $configured,
        public string $message,
    ) {}

    /** @return array{key: string, mode: string, configured: bool, message: string} */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'mode' => $this->mode,
            'configured' => $this->configured,
            'message' => $this->message,
        ];
    }
}
