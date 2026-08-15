<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

final readonly class IntegrationResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public string $status,
        public array $data = [],
        public ?string $message = null,
    ) {}

    /** @return array{status: string, data: array<string, mixed>, message: string|null} */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'message' => $this->message,
        ];
    }
}
