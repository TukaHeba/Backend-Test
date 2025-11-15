<?php

use App\Http\Middleware\CheckRoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'role' => CheckRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                if ($e instanceof HttpResponseException) {
                    return $e->getResponse();
                }
                if ($e instanceof ValidationException) {
                    return null;
                }
                return handleApiException($request, $e);
            }
        });
    })->create();

/**
 * Handle API exceptions and return standardized JSON response
 */
function handleApiException(Request $request, Throwable $e)
{
    Log::error('API Exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'url' => $request->fullUrl(),
        'method' => $request->method(),
    ]);

    // Determine status code
    $statusCode = match (true) {
        $e instanceof AuthenticationException => 401,
        $e instanceof AuthorizationException => 403,
        $e instanceof ModelNotFoundException => 404,
        $e instanceof HttpException => $e->getStatusCode(),
        default => 500,
    };

    $response = [
        'success' => false,
        'error_code' => getErrorCode($statusCode),
        'message' => getErrorMessage($statusCode),
    ];

    return response()->json($response, $statusCode);
}

/**
 * Get error code based on status code
 */
function getErrorCode(int $statusCode): string
{
    return match ($statusCode) {
        400 => 'BAD_REQUEST',
        401 => 'UNAUTHENTICATED',
        403 => 'UNAUTHORIZED',
        404 => 'NOT_FOUND',
        422 => 'VALIDATION_ERROR',
        503 => 'SERVICE_UNAVAILABLE',
        default => 'INTERNAL_SERVER_ERROR',
    };
}

/**
 * Get error message based on status code
 */
function getErrorMessage(int $statusCode): string
{
    return match ($statusCode) {
        400 => 'Invalid request.',
        401 => 'Authentication required.',
        403 => 'You do not have permission to access this resource.',
        404 => 'The requested resource could not be found.',
        422 => 'The given data was invalid.',
        503 => 'Service temporarily unavailable.',
        default => 'An error occurred in the server.',
    };
}
