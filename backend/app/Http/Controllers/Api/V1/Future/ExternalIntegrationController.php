<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Future;

use App\Contracts\Integrations\ExternalIntegrationAdapter;
use App\Contracts\Integrations\IntegrationRequest;
use App\Http\Requests\Future\ExecuteIntegrationRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class ExternalIntegrationController
{
    public function __construct(private readonly IntegrationRegistry $registry) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(array_map(
            static fn (ExternalIntegrationAdapter $adapter): array => $adapter->status()->toArray(),
            $this->registry->all(),
        ), 'External integration status loaded.');
    }

    public function execute(ExecuteIntegrationRequest $request, string $integration): JsonResponse
    {
        $validated = $request->validated();

        try {
            $adapter = $this->registry->get($integration);
        } catch (InvalidArgumentException) {
            return ApiResponse::unavailable("Integration [{$integration}] is not registered.");
        }

        $result = $adapter->execute(new IntegrationRequest(
            operation: $validated['operation'],
            parameters: $validated['parameters'] ?? [],
        ));

        return ApiResponse::success($result->toArray(), 'Integration operation completed.');
    }
}
