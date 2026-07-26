<?php
declare(strict_types=1);

namespace TrendHunter;

use TrendHunter\Database;
use TrendHunter\Helpers\Validator;
use PDO;
use Exception;

class Auth {
    /**
     * Start the session safely
     */
    private static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate user login
     */
    public static function login(string $email, string $password): bool {
        self::startSession();

        if (!Validator::validateEmail($email)) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email, password_hash, role, dark_mode FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Prevent Session Fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['dark_mode'] = (bool)$user['dark_mode'];

            return true;
        }

        return false;
    }

    /**
     * Register a new user
     */
    public static function register(string $name, string $email, string $password): bool {
        if (empty($name) || !Validator::validateEmail($email) || !Validator::validatePassword($password)) {
            return false;
        }

        $db = Database::getConnection();
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // Email already in use
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, dark_mode) VALUES (?, ?, 'user', 1)");
        return $stmt->execute([trim($name), $email, $passwordHash]);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is administrator
     */
    public static function isAdmin(): bool {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Force redirect to login page if user is not authenticated
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Automatically log in as the default Admin SAM user without requiring manual login
     */
    public static function autoLoginDefaultUser(): bool {
        self::startSession();
        if (self::isLoggedIn()) {
            return true;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT id, name, email, role, dark_mode FROM users ORDER BY id ASC LIMIT 1");
            $user = $stmt->fetch();

            if (!$user) {
                // Create default Admin SAM user
                $db->exec("INSERT INTO users (name, email, password_hash, role, dark_mode) 
                           VALUES ('Fernando Paiva - SAM Admin', 'admin@sam.fernandopaiva.com.br', '" . password_hash('F3rn@nd0P190983FE', PASSWORD_BCRYPT) . "', 'admin', 1)");
                $user = [
                    'id' => (int)$db->lastInsertId(),
                    'name' => 'Fernando Paiva - SAM Admin',
                    'email' => 'admin@sam.fernandopaiva.com.br',
                    'role' => 'admin',
                    'dark_mode' => 1
                ];
            }

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['dark_mode'] = (bool)$user['dark_mode'];

            return true;
        } catch (\Exception $e) {
            // Fallback session if database is unavailable
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Fernando Paiva - SAM Admin';
            $_SESSION['user_email'] = 'admin@sam.fernandopaiva.com.br';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['dark_mode'] = true;
            return true;
        }
    }

    /**
     * Force redirect if not an administrator
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: index.php?error=unauthorized');
            exit;
        }
    }

    /**
     * Terminate user session
     */
    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Get current logged-in user profile details
     */
    public static function getCurrentUser(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'dark_mode' => $_SESSION['dark_mode'] ?? true,
        ];
    }

    /**
     * Update user's preference theme mode
     */
    public static function toggleDarkMode(bool $enabled): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userId = $_SESSION['user_id'];
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
        $success = $stmt->execute([(int)$enabled, $userId]);

        if ($success) {
            $_SESSION['dark_mode'] = $enabled;
        }

        return $success;
    }
}
