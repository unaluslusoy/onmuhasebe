<?php

namespace App\Helpers;

/**
 * HTTP Response Helper
 * Handles JSON responses with consistent structure
 */
class Response
{
    /**
     * Send JSON response
     */
    public static function json(
        mixed $data = null,
        int $statusCode = 200,
        array $headers = []
    ): never {
        http_response_code($statusCode);
        
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Success response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): never {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ], $statusCode);
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null
    ): never {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        self::json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): never
    {
        self::error($message, 422, $errors);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error($message, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error($message, 403);
    }

    /**
     * Bad request response
     */
    public static function badRequest(string $message = 'Bad request'): never
    {
        self::error($message, 400);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Not found'): never
    {
        self::error($message, 404);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Internal server error'): never
    {
        self::error($message, 500);
    }

    /**
     * Created response
     */
    public static function created(mixed $data = null, string $message = 'Created'): never
    {
        self::success($data, $message, 201);
    }

    /**
     * No content response
     */
    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }

    /**
     * Payment required response (402)
     */
    public static function paymentRequired(string $message = 'Payment required', mixed $data = null): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ], 402);
    }

    /**
     * Paginated response
     */
    public static function paginated(
        array $items,
        int $total,
        int $page,
        int $perPage,
        string $message = 'Success'
    ): never {
        self::success([
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total)
            ]
        ], $message);
    }
}
