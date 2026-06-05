<?php

use App\Http\Middleware\EnsureCompanyAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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

        // Register named middleware aliases
        $middleware->alias([
            'company.access' => EnsureCompanyAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return structured JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );

        // DomainException → 422
        $exceptions->renderable(function (\DomainException $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return null;
        });

        // AuthenticationException → 401
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            return null;
        });

        // AuthorizationException → 403
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
                ], 403);
            }

            return null;
        });

        // ModelNotFoundException → 404
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
            }

            return null;
        });

        // ValidationException → 422
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            return null;
        });
    })->create();
