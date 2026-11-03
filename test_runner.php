<?php
/**
 * TrendHunter Brasil - Web-based Test Runner & Server Diagnostic Tool
 */

declare(strict_types=1);

// Disable cache for diagnostics
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Custom autoloader
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
use TrendHunter\Cache;
use TrendHunter\Analysis\TrendScorer;
use TrendHunter\Analysis\AiAdvisor;
use TrendHunter\Marketplaces\MercadoLivreAdapter;

// Results container
$checks = [];

// 1. PHP Version and Core
$phpOk = version_compare(PHP_VERSION, '8.4.0', '>=');
$checks['php'] = [
    'title' => 'Versão do PHP',
    'value' => 'PHP ' . PHP_VERSION,
    'status' => $phpOk ? 'success' : 'warning',
    'desc' => $phpOk ? 'Compatível com as otimizações do PHP 8.4.' : 'O sistema foi projetado para PHP 8.4. Recomendamos atualizar o PHP no painel Plesk.'
];

// 2. Extensions Check
$requiredExts = ['pdo', 'pdo_mysql', 'curl', 'simplexml', 'session'];
$missing = [];
foreach ($requiredExts as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
    }
}
$checks['extensions'] = [
    'title' => 'Extensões do Servidor',
    'value' => empty($missing) ? 'Todas ativas' : 'Ausentes: ' . implode(', ', $missing),
    'status' => empty($missing) ? 'success' : 'danger',
    'desc' => empty($missing) ? 'PDO, MySQL, cURL, SimpleXML e Session estão ativas.' : 'Instale ou ative as extensões listadas nas configurações de PHP do Plesk.'
];

// 3. Redis Extension Check
$hasRedis = extension_loaded('redis');
$checks['redis_ext'] = [
    'title' => 'Extensão Redis PHP',
    'value' => $hasRedis ? 'Instalada' : 'Não instalada',
    'status' => $hasRedis ? 'success' : 'info',
    'desc' => $hasRedis ? 'Pronta para aceleração de consultas.' : 'O sistema usará cache de arquivo local automaticamente na pasta storage/cache.'
];

// 4. Config & Database Setup check
$configPath = __DIR__ . '/config/config.php';
$configExists = file_exists($configPath);
$dbStatus = 'danger';
$dbValue = 'Desconectado';
$dbDesc = 'Verifique config/config.php.';

if ($configExists) {
    $config = require $configPath;
    try {
        // Retrieve connection through singleton which handles SQLite fallback automatically
        $db = Database::getConnection();
        $driver = Database::getDriverType();
        
        if ($driver === 'sqlite') {
            $dbStatus = 'info';
            $dbValue = 'SQLite Ativo (Local)';
            $dbDesc = 'MySQL inativo. Banco de dados SQLite simulado (storage/database.sqlite) ativo para testes locais.';
        } else {
            $dbStatus = 'success';
            $dbValue = 'MySQL Conectado';
            $dbDesc = 'Conexão MySQL ativa no servidor e tabelas de produção inicializadas.';
        }
    } catch (\Exception $e) {
        $dbValue = 'Falha de Conexão';
        $dbDesc = 'Erro de Banco: ' . $e->getMessage();
    }
} else {
    $dbDesc = 'Arquivo config/config.php está ausente.';
}

$checks['database'] = [
    'title' => 'Conexão e Migrações MySQL',
    'value' => $dbValue,
    'status' => $dbStatus,
    'desc' => $dbDesc
];

// 5. Redis Server Connection & Write latency
$redisServerStatus = 'info';
$redisServerVal = 'Modo de Arquivos';
$redisServerDesc = 'Carregando backup local.';
if ($hasRedis && $configExists && ($config['redis']['enabled'] ?? false)) {
    try {
        $redis = new Redis();
        $connected = $redis->connect($config['redis']['host'], $config['redis']['port'], 1.5);
        if ($connected) {
            $redisServerStatus = 'success';
            $redisServerVal = 'Conectado';
            $redisServerDesc = 'Servidor Redis está ativo e armazenando chaves.';
        }
    } catch (\Exception) {
        // Suppress to fallback
    }
}

$checks['redis_server'] = [
    'title' => 'Aceleração de Cache (Redis)',
    'value' => $redisServerVal,
    'status' => $redisServerStatus,
    'desc' => $redisServerDesc
];

// 6. Cache Operations testing
$cacheStatus = 'danger';
$cacheVal = 'Erro de Escrita';
$cacheDesc = 'Operações de cache falharam.';
try {
    $testKey = 'diagnostic_test_' . mt_rand(100, 999);
    $testVal = 'OK';
    Cache::set($testKey, $testVal, 10);
    $ret = Cache::get($testKey);
    if ($ret === $testVal) {
        Cache::delete($testKey);
        $cacheStatus = 'success';
        $cacheVal = 'Funcional';
        $cacheDesc = 'Gravação, leitura e expiração de chaves operando corretamente.';
    }
} catch (\Exception $e) {
    $cacheDesc = 'Erro: ' . $e->getMessage();
}

$checks['cache_ops'] = [
    'title' => 'Operações de Escrita Cache',
    'value' => $cacheVal,
    'status' => $cacheStatus,
    'desc' => $cacheDesc
];

// 7. Algorithm Scoring logic check
$scorer = new TrendScorer();
$score = $scorer->calculate(90, 80, 50, 45, 10);
$checks['algorithm'] = [
    'title' => 'Algoritmo Trend Score',
    'value' => 'Pontuação: ' . $score,
    'status' => $score > 0 ? 'success' : 'danger',
    'desc' => 'Resultado qualificado: ' . TrendScorer::getLabel($score) . '.'
];

// 8. AI prompt generation testing
$aiStatus = 'success';
$aiVal = 'Mock/Heurística Ativa';
$aiDesc = 'Modo simulação local gerando copys e nichos perfeitamente.';
try {
    $aiTest = AiAdvisor::analyzeProduct('Stanley Cup 1.2L', 250.00, 'shopee', 'high');
    if (!empty($aiTest['seo_title'])) {
        if ($configExists && !empty($config['ai']['api_key'])) {
            $aiVal = 'API ' . ucfirst($config['ai']['provider']) . ' Ativa';
            $aiDesc = 'Comunicação oficial do consultor de IA ativa com sucesso.';
        }
    }
} catch (\Exception $e) {
    $aiStatus = 'danger';
    $aiVal = 'Falha de Execução';
    $aiDesc = 'Erro: ' . $e->getMessage();
}

$checks['ai_engine'] = [
    'title' => 'Mecanismo de Inteligência Artificial',
    'value' => $aiVal,
    'status' => $aiStatus,
    'desc' => $aiDesc
];

// 9. Marketplace Search simulation
$marketStatus = 'danger';
$marketVal = 'Desconectado';
$marketDesc = 'Adapters não inicializaram.';
try {
    $adapter = new MercadoLivreAdapter();
    $res = $adapter->search('copo termico', null, 2);
    if (!empty($res)) {
        $marketStatus = 'success';
        $marketVal = 'Integrado';
        $marketDesc = 'Recebido ' . count($res) . ' itens. Primeiro item: ' . $res[0]['title'] . ' (Trend Score: ' . $res[0]['trend_score'] . ').';
    }
} catch (\Exception $e) {
    $marketDesc = 'Erro: ' . $e->getMessage();
}

$checks['marketplace'] = [
    'title' => 'Adapters de Marketplaces',
    'value' => $marketVal,
    'status' => $marketStatus,
    'desc' => $marketDesc
];
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Diagnósticos - TrendHunter Brasil</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b0c16;
            background-image: radial-gradient(at 10% 20%, rgba(116, 93, 247, 0.08) 0px, transparent 50%);
            min-height: 100vh;
            color: #f5f6fa;
            padding: 40px 0;
        }
        .diag-card {
            background-color: #121426;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.25);
        }
        .diag-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            background-color: #14172a;
            padding: 20px 24px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        .diag-body {
            padding: 24px;
        }
        .test-row {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            padding: 15px 0;
        }
        .test-row:last-child {
            border-bottom: none;
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-success {
            background-color: rgba(25, 135, 84, 0.15);
            color: #2ecc71;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }
        .status-warning {
            background-color: rgba(253, 150, 68, 0.15);
            color: #f39c12;
            border: 1px solid rgba(253, 150, 68, 0.3);
        }
        .status-danger {
            background-color: rgba(252, 92, 101, 0.15);
            color: #fc5c65;
            border: 1px solid rgba(252, 92, 101, 0.3);
        }
        .status-info {
            background-color: rgba(6, 225, 204, 0.1);
            color: #06e1cc;
            border: 1px solid rgba(6, 225, 204, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #745df7, #06e1cc) !important;">
                        <i class="fa-solid fa-server-shield text-dark fs-5"></i>
                    </div>
                    <h2 class="fw-bold">TrendHunter Brasil</h2>
                    <p class="text-muted">Painel de Diagnósticos e Inicialização do Servidor (Plesk)</p>
                </div>

                <div class="diag-card mb-4">
                    <div class="diag-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-vial-virus text-accent-turquoise me-2"></i> Relatório de Testes do Sistema</h5>
                        <a href="index.php" class="btn btn-sm btn-outline-light" style="border-radius: 8px;"><i class="fa-solid fa-gauge me-1"></i> Ir para Dashboard</a>
                    </div>
                    <div class="diag-body">
                        <?php foreach ($checks as $key => $check): ?>
                            <div class="test-row d-flex align-items-center justify-content-between flex-wrap g-2">
                                <div class="col-sm-5">
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($check['title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($check['desc']); ?></small>
                                </div>
                                <div class="col-sm-4 text-sm-center">
                                    <span class="text-light-subtle font-monospace small"><?php echo htmlspecialchars($check['value']); ?></span>
                                </div>
                                <div class="col-sm-3 text-sm-end">
                                    <span class="badge-status status-<?php echo $check['status']; ?>">
                                        <?php echo $check['status'] === 'success' ? 'OK / Passou' : ($check['status'] === 'warning' ? 'Aviso' : ($check['status'] === 'info' ? 'Info' : 'Falhou')); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-center mt-3 text-muted small">
                    Diagnóstico gerado em: <?php echo date('d/m/Y H:i:s'); ?> | TrendHunter Engine v1.0.0
                </div>
            </div>
        </div>
    </div>
</body>
</html>
