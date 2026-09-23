<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
    })
        ->withExceptions(function (Exceptions $exceptions): void {
        // Always render JSON for API routes, even without an Accept header.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $json = fn (string $message, int $status, array $errors = []) => response()->json([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status);

        // Returning nothing (null) lets Laravel handle non-API requests normally.
        // Specific exceptions must be registered before the generic HTTP one.

        $exceptions->render(function (ValidationException $e, Request $request) use ($json) {
            if ($request->is('api/*')) {
                return $json('Validation failed', 422, $e->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($json) {
            if ($request->is('api/*')) {
                return $json('Unauthenticated.', 401);
            }
        });

        // Laravel converts AuthorizationException into AccessDeniedHttpException.
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($json) {
            if ($request->is('api/*')) {
                return $json('You are not authorized to perform this action.', 403);
            }
        });

        // Laravel converts ModelNotFoundException into NotFoundHttpException.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($json) {
            if ($request->is('api/*')) {
                return $json('Resource not found.', 404);
            }
        });

        // 405, 429 (throttle), etc.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($json) {
            if ($request->is('api/*')) {
                $status = $e->getStatusCode();

                return $json($e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error'), $status);
            }
        });

        // Unexpected errors: hide details unless APP_DEBUG=true.
        $exceptions->render(function (Throwable $e, Request $request) use ($json) {
            if ($request->is('api/*') && ! config('app.debug')) {
                return $json('Server error.', 500);
            }
        });
    })->create();