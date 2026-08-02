<?php
/**
 * SAM - Exportador de Base de Dados
 */
declare(strict_types=1);

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

Auth::requireLogin();
$db = Database::getConnection();

$type = $_GET['format'] ?? 'csv';

if ($type === 'json') {
    // Export as JSON
    $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=sam_tendencias_export_' . date('Ymd_His') . '.json');
    echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
} elseif ($type === 'sqlite') {
    // Export SQLite file
    $dbType = Database::getDriverType();
    if ($dbType === 'sqlite') {
        $sqlitePath = __DIR__ . '/storage/database.sqlite';
        if (file_exists($sqlitePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=sam_database_' . date('Ymd') . '.sqlite');
            header('Content-Length: ' . filesize($sqlitePath));
            readfile($sqlitePath);
            exit;
        } else {
            echo "Banco de dados SQLite não encontrado.";
            exit;
        }
    } else {
        echo "Banco de dados ativo não é SQLite (MySQL está ativo).";
        exit;
    }
} else {
    // Default: Export as CSV
    $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sam_tendencias_export_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Write BOM for Excel UTF-8 compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Headers
    fputcsv($output, [
        'ID', 'Marketplace', 'ID Externo', 'Título', 'URL', 'URL Imagem', 
        'Preço de Venda', 'Preço Original', 'Estimativa Vendas Mensal', 
        'Contagem de Reviews', 'Avaliação', 'Nome da Loja', 'Tipo de Frete', 
        'Categoria', 'Score de Tendência (0-100)', 'Nível de Concorrência'
    ], ';');
    
    foreach ($products as $p) {
        fputcsv($output, [
            $p['id'],
            $p['marketplace'],
            $p['external_id'],
            $p['title'],
            $p['url'],
            $p['image_url'],
            $p['price'],
            $p['original_price'],
            $p['sales_count_est'],
            $p['reviews_count'],
            $p['rating'],
            $p['store_name'],
            $p['shipping_type'] ?? '',
            $p['category'],
            $p['trend_score'],
            $p['competition_level']
        ], ';');
    }
    
    fclose($output);
    exit;
}
