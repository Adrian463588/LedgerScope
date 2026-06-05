<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseAuditableEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Audit event properties — not readonly to allow proper inheritance in PHP 8.x.
     */
    public int $userId;

    public string $action;

    public ?int $companyId;

    public ?string $objectType;

    public ?int $objectId;

    public ?array $before;

    public ?array $after;

    public ?string $ipAddress;

    public ?string $userAgent;

    public ?array $metadata;

    public function __construct(
        int $userId,
        string $action,
        ?int $companyId = null,
        ?string $objectType = null,
        ?int $objectId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $metadata = null,
    ) {
        $this->userId = $userId;
        $this->action = $action;
        $this->companyId = $companyId;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->before = $before;
        $this->after = $after;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->metadata = $metadata;
    }
}
