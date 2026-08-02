<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<h2>SAM Production Sync Debugger</h2>";

spl_autoload_register(function ($class) {
    $prefix = 'TrendHunter\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use TrendHunter\Database;

try {
    echo "Connecting to DB...<br>";
    $db = Database::getConnection();
    echo "DB Connected. Driver: " . Database::getDriverType() . "<br>";
    
    echo "Running checkAndSeedNicheProducts...<br>";
    Database::checkAndSeedNicheProducts($db);
    echo "Niche products checked.<br>";
    
    echo "Creating statements...<br>";
    $checkStmt = $db->prepare("SELECT id FROM products WHERE marketplace = ? AND external_id = ?");
    $insertStmt = $db->prepare("INSERT INTO products (marketplace, external_id, title, url, image_url, price, original_price, sales_count_est, reviews_count, rating, store_name, shipping_type, category, trend_score, competition_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $updateStmt = $db->prepare("UPDATE products SET title = ?, url = ?, image_url = ?, price = ?, original_price = ?, sales_count_est = ?, reviews_count = ?, rating = ?, store_name = ?, shipping_type = ?, category = ?, trend_score = ?, competition_level = ? WHERE marketplace = ? AND external_id = ?");
    echo "Statements created.<br>";

    $item = [
        'title' => 'Test Item ' . rand(1, 100),
        'category' => 'Eletrônicos',
        'marketplace' => 'Mercado Livre',
        'min_price' => 10.00,
        'max_price' => 50.00
    ];
    
    $prefix = ($item['marketplace'] === 'Mercado Livre') ? 'MLB' : 'SHP';
    $extId = $prefix . '_TEST_' . rand(1000, 9999);
    $price = 29.90;
    $origPrice = 39.90;
    $sales = 100;
    $reviews = 10;
    $rating = 4.5;
    $score = 90;
    $store = 'Test Store';
    $shipping = 'Fulfillment';
    $level = 'low';
    $imgUrl = 'http://example.com/img.png';
    $url = 'http://example.com/product';
    
    echo "Running insert...<br>";
    $insertStmt->execute([
        $item['marketplace'], $extId, $item['title'], $url, $imgUrl, $price, $origPrice, $sales, $reviews, $rating, $store, $shipping, $item['category'], $score, $level
    ]);
    echo "Insert complete. ID: " . $db->lastInsertId() . "<br>";
    
    echo "Running clean up...<br>";
    $db->exec("DELETE FROM products WHERE external_id = '$extId'");
    echo "Clean up complete.<br>";
    
    echo "<h3 style='color:green;'>All systems are green!</h3>";
} catch (\Exception $e) {
    echo "<h3 style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
