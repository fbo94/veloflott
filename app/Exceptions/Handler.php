<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Shared\Domain\DomainException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * Liste des exceptions qui ne doivent pas être reportées.
     */
    protected $dontReport = [
        DomainException::class,
        ValidationException::class,
    ];

    /**
     * Rendu des exceptions en format JSON pour l'API.
     */
    public function render($request, Throwable $e): JsonResponse
    {
        // Exception métier
        if ($e instanceof DomainException) {
            return $this->renderDomainException($e);
        }

        // Validation
        if ($e instanceof ValidationException) {
            return $this->renderValidationException($e);
        }

        // HTTP (404, etc.)
        if ($e instanceof HttpExceptionInterface) {
            return $this->renderHttpException($e);
        }

        // Autres
        return $this->renderGenericException($e);
    }

    private function renderDomainException(DomainException $e): JsonResponse
    {
        $statusCode = $this->mapToHttpStatus($e->errorCode());

        return response()->json([
            'error' => [
                'code' => $e->errorCode(),
                'message' => $e->getMessage(),
                'context' => $e->context(),
            ],
        ], $statusCode);
    }

    private function renderValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Les données fournies sont invalides.',
                'details' => $e->errors(),
            ],
        ], 422);
    }

    protected function renderHttpException(HttpExceptionInterface $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'HTTP_ERROR',
                'message' => $e->getMessage() ?: 'Une erreur est survenue.',
            ],
        ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
    }

    private function renderGenericException(Throwable $e): JsonResponse
    {
        report($e);

        $message = config('app.debug')
            ? $e->getMessage()
            : 'Une erreur inattendue est survenue.';

        return response()->json([
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => $message,
            ],
        ], 500);
    }

    /**
     * Mappe les codes d'erreur métier vers des codes HTTP.
     */
    private function mapToHttpStatus(string $errorCode): int
    {
        return match (true) {
            str_contains($errorCode, 'NOT_FOUND') => 404,
            str_contains($errorCode, 'UNAUTHORIZED') => 401,
            str_contains($errorCode, 'FORBIDDEN') => 403,
            str_contains($errorCode, 'ALREADY_') => 409,
            str_contains($errorCode, 'CANNOT_') => 409,
            default => 400,
        };
    }
}
