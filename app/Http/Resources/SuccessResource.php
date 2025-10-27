<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class SuccessResource
{
    /**
     * Create a success response with custom message
     */
    public static function message(string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Create a deleted success response
     */
    public static function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return self::message($message, 200);
    }

    /**
     * Create a created success response (without returning resource)
     */
    public static function created(string $message = 'Resource created successfully'): JsonResponse
    {
        return self::message($message, 201);
    }

    /**
     * Create an updated success response (without returning resource)
     */
    public static function updated(string $message = 'Resource updated successfully'): JsonResponse
    {
        return self::message($message, 200);
    }

    /**
     * Create an accepted response (for async operations)
     */
    public static function accepted(string $message = 'Request accepted for processing'): JsonResponse
    {
        return self::message($message, 202);
    }

    /**
     * Create a no content response
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
