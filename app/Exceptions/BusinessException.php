<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Base Business Exception Class
 *
 * All custom business exceptions should extend this class
 */
abstract class BusinessException extends Exception
{
    /**
     * HTTP status code
     */
    protected int $statusCode = 400;

    /**
     * Error code for API responses
     */
    protected string $errorCode;

    /**
     * User-friendly message (Vietnamese)
     */
    protected string $userMessage;

    /**
     * Constructor
     *
     * @param  string  $message  Technical message (for logs)
     * @param  string|null  $userMessage  User-friendly message (for display)
     * @param  int|null  $code  Error code
     * @param  \Throwable|null  $previous  Previous exception
     */
    public function __construct(
        string $message = '',
        ?string $userMessage = null,
        ?int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->userMessage = $userMessage ?? $message;
        $this->errorCode = $this->getDefaultErrorCode();
    }

    /**
     * Get default error code
     */
    protected function getDefaultErrorCode(): string
    {
        $className = class_basename($this);

        return strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', str_replace('Exception', '', $className)));
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get user-friendly message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Render exception for HTTP response
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderJson();
        }

        // Web request - redirect back with error
        return $this->renderWeb();
    }

    /**
     * Render JSON response
     */
    protected function renderJson(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->userMessage,
                'technical_message' => config('app.debug') ? $this->getMessage() : null,
            ],
        ], $this->statusCode);
    }

    /**
     * Render web response
     */
    protected function renderWeb(): RedirectResponse
    {
        return redirect()->back()
            ->withInput()
            ->with('error', $this->userMessage);
    }

    /**
     * Report exception
     */
    public function report(): bool
    {
        // Log the exception
        \Log::error(static::class.': '.$this->getMessage(), [
            'error_code' => $this->errorCode,
            'user_message' => $this->userMessage,
            'trace' => $this->getTraceAsString(),
        ]);

        return true;
    }
}
