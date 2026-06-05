<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class ApiResponse
{
    /**
     * Return a successful JSON response.
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operation successful.',
        int $statusCode = 200,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($payload, $statusCode);
    }

    /**
     * Return a paginated JSON response.
     */
    public static function paginated(
        LengthAwarePaginator|ResourceCollection $data,
        string $message = 'Resources loaded.',
    ): JsonResponse {
        if ($data instanceof ResourceCollection) {
            $paginator = $data->resource;
        } else {
            $paginator = $data;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data instanceof ResourceCollection ? $data->items() : $data->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Return a created (201) response.
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully.',
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * Return a no-content (204) response.
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a validation error (422) response.
     *
     * @param  array<string, list<string>>  $errors
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Return an authorization error (403) response.
     */
    public static function forbidden(
        string $message = 'You do not have permission to perform this action.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    /**
     * Return a not-found (404) response.
     */
    public static function notFound(
        string $message = 'Resource not found.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Return a domain error (422) response — for business rule violations.
     */
    public static function domainError(
        string $message,
        int $statusCode = 422,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Return a server error (500) response — never expose stack traces.
     */
    public static function serverError(
        string $message = 'An unexpected error occurred. Please try again later.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }

    /**
     * Return an unauthorized (401) response.
     */
    public static function unauthorized(
        string $message = 'Unauthenticated.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}
