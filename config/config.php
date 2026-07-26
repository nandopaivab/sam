<?php
/**
 * TrendHunter Brasil - Configuration File
 * Optimized for PHP 8.4
 */

// PHP 8.4 strict types
declare(strict_types=1);

// Prevent direct access to config if included inappropriately
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    exit('Direct access not permitted.');
}

// Start secure session if not started (and not running in CLI mode)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

return [
    'app' => [
        'name' => 'TrendHunter Brasil',
        'version' => '1.0.0',
        'env' => 'production', // development or production
        'base_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    ],
    
    // Firebase Configuration
    'firebase' => [
        'api_key' => 'AIzaSyDzhKsZeowjo1aiTQvMJCKqktWsD-LS17w',
        'auth_domain' => 'mafekidsbr.firebaseapp.com',
        'project_id' => 'mafekidsbr',
        'storage_bucket' => 'mafekidsbr.firebasestorage.app',
        'messaging_sender_id' => '16456704349',
        'app_id' => '1:16456704349:web:4b1d742210ef0f0f4448b4',
        'measurement_id' => 'G-FWQ5PBLPYS',
    ],
    
    // MariaDB / MySQL Configuration
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'fernandop_sam',
        'username' => 'fernandop_sam',
        'password' => 'F3rn@nd0P190983FE',
        'charset' => 'utf8mb4',
    ],

    // Redis Cache Configuration
    'redis' => [
        'enabled' => true,
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'timeout' => 2.5,
        'prefix' => 'trendhunter:',
    ],

    // AI Providers (Gemini / OpenAI) Settings
    'ai' => [
        'provider' => 'gemini', // 'gemini', 'openai', or 'mock'
        'api_key' => getenv('AI_API_KEY') ?: '', // Can also be set directly here if needed
        'gemini' => [
            'model' => 'gemini-1.5-flash',
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
        ],
        'openai' => [
            'model' => 'gpt-4o-mini',
            'api_url' => 'https://api.openai.com/v1/chat/completions',
        ]
    ],

    // General product scanning constraints
    'scanner' => [
        'default_limit' => 20,
        'cache_ttl' => 3600, // 1 hour for product searches
        'trends_ttl' => 43200, // 12 hours for Google Trends data
    ]
];
