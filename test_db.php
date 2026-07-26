<?php
/**
 * Diagnostic Tool - Database & Files Check
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<html><head><title>SAM Diagnostic Panel</title></head><body style='background:#12131a;color:#fff;font-family:monospace;padding:30px;'>";
echo "<h2>SAM Diagnostic & File System Verification</h2>";
echo "<hr style='border-color:#333;'><pre>";

// 1. Check Autoloader
echo "1. Checking Autoloader... ";
try {
    spl_autoload_register(function ($class) {
        $prefix = 'TrendHunter\\';
        $baseDir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) require $file;
    });
    echo "<span style='color:#06d6a0;'>OK</span>\n";
} catch (\Exception $e) {
    echo "<span style='color:#ef476f;'>FAILED: " . $e->getMessage() . "</span>\n";
}

// 2. Check Database Connection & Driver
echo "2. Checking Database Connection... ";
try {
    $db = \TrendHunter\Database::getConnection();
    $driver = \TrendHunter\Database::getDriverType();
    echo "<span style='color:#06d6a0;'>Connected successfully (Driver: {$driver})</span>\n";
} catch (\Exception $e) {
    echo "<span style='color:#ef476f;'>FAILED: " . $e->getMessage() . "</span>\n";
}

// 3. Check Tables
if (isset($db)) {
    echo "\n3. Checking Tables:\n";
    $tables = ['users', 'products', 'crm_activities', 'activity_logs', 'blue_ocean_products', 'baby_niche_products'];
    foreach ($tables as $t) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM {$t}");
            $count = $stmt->fetchColumn();
            echo "  - Table '{$t}': <span style='color:#06d6a0;'>Exist (Count: {$count})</span>\n";
        } catch (\Exception $e) {
            echo "  - Table '{$t}': <span style='color:#ef476f;'>MISSING or ERROR: " . $e->getMessage() . "</span>\n";
        }
    }
}

// 4. Check Root Files
echo "\n4. Checking Root Page Files existence:\n";
$files = ['index.php', 'crm.php', 'blue_ocean.php', 'baby_niche.php', 'profit_calculator.php', 'audit_logs.php', 'metrify.php', 'shopee.php', 'settings.php', 'templates/header.php', 'templates/footer.php'];
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "  - File '{$f}': <span style='color:#06d6a0;'>Found ({$size} bytes)</span>\n";
    } else {
        echo "  - File '{$f}': <span style='color:#ef476f;'>NOT FOUND on Webroot!</span>\n";
    }
}

echo "</pre><hr style='border-color:#333;'><p style='color:#888;'>SAM Diagnostics completed.</p></body></html>";
