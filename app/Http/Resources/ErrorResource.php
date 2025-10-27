<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class ErrorResource
{
    /**
     * Create a not found error response
     */
    public static function notFound(string $message = 'Resource not found', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, 404);
    }

    /**
     * Create a bad request error response
     */
    public static function badRequest(string $message = 'Bad request', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, 400);
    }

    /**
     * Create an unauthorized error response
     */
    public static function unauthorized(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }

        return response()->json($response, 401);
    }

    /**
     * Create a forbidden error response
     */
    public static function forbidden(string $message = 'Forbidden', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }

        return response()->json($response, 403);
    }

    /**
     * Create a server error response
     */
    public static function serverError(string $message = 'Internal server error', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['error'] = $data;
        }

        return response()->json($response, 500);
    }

    /**
     * Create an unprocessable entity error response (422)
     */
    public static function unprocessableEntity(string $message = 'Unprocessable entity', $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }

        return response()->json($response, 422);
    }

    /**
     * Create a custom error response
     */
    public static function custom(string $message, int $statusCode = 400, $data = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }

        return response()->json($response, $statusCode);
    }
}
