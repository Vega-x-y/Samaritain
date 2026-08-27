<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Exception thrown when PawaPay API calls fail.
 *
 * Stores the HTTP status code and full response body for debugging.
 */
class PawaPayException extends Exception
{
    public function __construct(
        string $message = 'Une erreur est survenue lors du traitement du paiement.',
        public ?int $statusCode = null,
        public ?string $responseBody = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'details' => config('app.debug') ? $this->responseBody : null,
        ], $this->statusCode ?? 500);
    }
}
