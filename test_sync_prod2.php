<?php
/**
 * Diagnostic runner 2 including Auth check
 */
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

spl_autoload_register(function ($class) {
    $prefix = 'TrendHunter\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use TrendHunter\Auth;
use TrendHunter\Database;

try {
    echo "Checking isLoggedIn...<br>";
    $loggedIn = Auth::isLoggedIn();
    echo "Is Logged In: " . ($loggedIn ? "YES" : "NO") . "<br>";

    $user = Auth::getCurrentUser();
    echo "Current User: " . print_r($user, true) . "<br>";

    echo "Connecting to DB...<br>";
    $db = Database::getConnection();
    
    echo "Inserting log test...<br>";
    $stmtLog = $db->prepare("INSERT INTO activity_logs (user_id, user_name, module, action_type, target_record, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtLog->execute([$user['id'] ?? 1, $user['name'] ?? 'Usuário', 'Database', 'Sincronização Teste', 'products', 'Teste de log de depuração.', '127.0.0.1']);
    echo "Log test complete.<br>";

    echo "<h3 style='color:green;'>Debugger 2 Green!</h3>";
} catch (\Exception $e) {
    echo "<h3 style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
