<?php
declare(strict_types=1);

namespace TrendHunter\Helpers;

class Validator {
    /**
     * Sanitize input strings for outputting safely (XSS Prevention)
     */
    public static function sanitize(mixed $data): mixed {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $data;
    }

    /**
     * Generate CSRF Token and store in session
     */
    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validate CSRF Token
     */
    public static function validateCsrfToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate Email format
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password complexity requirements
     */
    public static function validatePassword(string $password): bool {
        // Password must be at least 6 characters long
        return strlen($password) >= 6;
    }

    /**
     * Send standard JSON API response
     */
    public static function jsonResponse(int $statusCode, array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
