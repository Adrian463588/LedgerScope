<?php

use App\Exceptions\FeatureUnavailableException;
use App\Http\Middleware\EnforceSessionTimeout;
use App\Http\Middleware\EnsureCompanyAccess;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum session stateful domains for SPA
        $middleware->statefulApi();
        $middleware->web(append: [HandleInertiaRequests::class]);

        // Register named middleware aliases
        $middleware->alias([
            'company.access' => EnsureCompanyAccess::class,
            'session.timeout' => EnforceSessionTimeout::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return structured JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->renderable(function (FeatureUnavailableException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::unavailable($e->getMessage());
            }

            return null;
        });

        // DomainException → 422
        $exceptions->renderable(function (DomainException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::domainError($e->getMessage());
            }

            return null;
        });

        // AuthenticationException → 401
        $exceptions->renderable(function (AuthenticationException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::unauthorized();
            }

            return null;
        });

        // AuthorizationException → 403
        $exceptions->renderable(function (AuthorizationException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::forbidden($e->getMessage() ?: 'You do not have permission to perform this action.');
            }

            return null;
        });

        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return match ($e->getStatusCode()) {
                    401 => ApiResponse::unauthorized($e->getMessage() ?: 'Unauthenticated.'),
                    403 => ApiResponse::forbidden($e->getMessage() ?: 'You do not have permission to perform this action.'),
                    404 => ApiResponse::notFound($e->getMessage() ?: 'Resource not found.'),
                    default => ApiResponse::error(
                        $e->getMessage() ?: 'Request failed.',
                        $e->getStatusCode(),
                        null,
                        'http_error',
                    ),
                };
            }

            return null;
        });

        // ModelNotFoundException → 404
        $exceptions->renderable(function (ModelNotFoundException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::notFound();
            }

            return null;
        });

        // ValidationException → 422
        $exceptions->renderable(function (ValidationException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return ApiResponse::validationError($e->errors());
            }

            return null;
        });

        $exceptions->renderable(function (Throwable $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                report($e);

                return ApiResponse::serverError();
            }

            return null;
        });
    })->create();
