<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Closure;
use Illuminate\Http\JsonResponse;
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
        LengthAwarePaginator $data,
        string $message = 'Resources loaded.',
        ?Closure $transform = null,
    ): JsonResponse {
        $items = collect($data->items())
            ->map($transform ?? static fn (mixed $item): mixed => $item)
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
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
            'code' => 'validation_failed',
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
            'code' => 'forbidden',
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
            'code' => 'not_found',
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
            'code' => 'domain_error',
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Return a generic error response with optional field or domain errors.
     *
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(
        string $message,
        int $statusCode = 422,
        ?array $errors = null,
        ?string $code = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * Return a server error (500) response — never expose stack traces.
     */
    public static function serverError(
        string $message = 'An unexpected error occurred. Please try again later.',
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'code' => 'server_error',
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
            'code' => 'unauthorized',
            'message' => $message,
        ], 401);
    }

    /**
     * Return a bad request (400) response.
     */
    public static function badRequest(
        string $message = 'Bad request.',
    ): JsonResponse {
        return self::error($message, 400, null, 'bad_request');
    }

    /**
     * Return an explicit response for a feature that is not implemented.
     */
    public static function unavailable(
        string $message = 'This feature is not available yet.',
    ): JsonResponse {
        return self::error($message, 501, null, 'feature_unavailable');
    }
}
