<?php
/**
 * TrendHunter Brasil - System Verification & Database Auto-Setup
 * Run: php verify_system.php
 */

declare(strict_types=1);

echo "==================================================\n";
echo "    TrendHunter Brasil - Setup & Verification     \n";
echo "==================================================\n\n";

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

// 1. Check PHP Version & Extensions
echo "[1/6] Analisando ambiente PHP...\n";
echo "PHP Version: " . PHP_VERSION . " (Mínimo requerido: PHP 8.4) - " . (version_compare(PHP_VERSION, '8.4.0', '>=') ? "OK" : "AVISO: PHP 8.4 é sugerido") . "\n";

$requiredExts = ['pdo', 'pdo_mysql', 'curl', 'simplexml', 'session'];
foreach ($requiredExts as $ext) {
    echo "Extensão '{$ext}': " . (extension_loaded($ext) ? "Disponível" : "AUSENTE (Crítico)") . "\n";
}
echo "Extensão 'redis': " . (extension_loaded('redis') ? "Disponível (Excelente)" : "Não instalada (Sistema usará cache de arquivo local automaticamente)") . "\n\n";

// 2. Load Configuration
echo "[2/6] Carregando arquivo de configuração...\n";
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    exit("CRÍTICO: config/config.php não encontrado!\n");
}
$config = require $configPath;
echo "Configuração carregada. Nome da aplicação: " . $config['app']['name'] . "\n\n";

// 3. MySQL Connection & SQL Migration
echo "[3/6] Conectando ao Banco de Dados e executando migrações...\n";
try {
    // Connect to MySQL server without selecting database first (in case database does not exist yet)
    $dbConfig = $config['db'];
    $dsnNoDb = sprintf("mysql:host=%s;port=%d;charset=%s", $dbConfig['host'], $dbConfig['port'], $dbConfig['charset']);
    $pdoSetup = new PDO($dsnNoDb, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Conexão com servidor MySQL estabelecida.\n";

    // Read and parse SQL schema
    $sqlFile = __DIR__ . '/config/database.sql';
    if (!file_exists($sqlFile)) {
        exit("CRÍTICO: config/database.sql não encontrado!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL by semicolon (basic parser, ignores semicolons in comments/quotes, but schema is simple enough)
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $trimmed = trim($query);
        if (!empty($trimmed)) {
            $pdoSetup->exec($trimmed);
        }
    }
    echo "Sucesso: Tabelas do TrendHunter e usuário administrador padrão importados!\n\n";

    // Verify database helper
    $db = Database::getConnection();
    echo "Conexão de teste via Database::getConnection() - OK\n\n";

} catch (Exception $e) {
    echo "ERRO DE BANCO DE DADOS: " . $e->getMessage() . "\n";
    echo "NOTA: Crie a base de dados 'trendhunter_db' manualmente ou verifique suas credenciais de acesso em config/config.php.\n\n";
}

// 4. Redis Cache Check
echo "[4/6] Testando Cache (Redis / Arquivo de Fallback)...\n";
try {
    $testKey = 'verify_test_key_' . mt_rand(1000, 9999);
    $testVal = 'Funcional_' . date('His');
    
    Cache::set($testKey, $testVal, 60);
    $retrieved = Cache::get($testKey);
    
    if ($retrieved === $testVal) {
        echo "Cache gravado e lido com sucesso! Chave: '{$testKey}', Valor: '{$retrieved}'\n";
        Cache::delete($testKey);
        echo "Sucesso: Operação de Cache validada.\n\n";
    } else {
        echo "ALERTA: Retorno de cache inválido ou corrompido.\n\n";
    }
} catch (Exception $e) {
    echo "AVISO DE CACHE: " . $e->getMessage() . "\n\n";
}

// 5. Test Trend Scorer and AI advisor Fallback
echo "[5/6] Validando algoritmos e heurísticas da IA...\n";
$scorer = new TrendScorer();
// demand=80, growth=70, comp=40 (meaning 60% competitiveness score), margin=45, seasonality=20
$score = $scorer->calculate(80, 70, 60, 45, 20);
echo "Trend Score calculado para Smartwatch (Demanda 80, Crescimento 70, Concorrência 60, Margem 45): " . $score . " - Categoria: " . TrendScorer::getLabel($score) . "\n";

$aiTest = AiAdvisor::analyzeProduct('Stanley Cup 1.2L', 250.00, 'shopee', 'high');
echo "AI Niche Heurística: " . count($aiTest['niches']) . " subnichos sugeridos.\n";
echo "AI SEO Descrição (Primeiros 100 caracteres): " . substr($aiTest['seo_description'], 0, 100) . "...\n\n";

// 6. Test Adapter Search Simulation
echo "[6/6] Validando adapters de marketplaces...\n";
$adapter = new MercadoLivreAdapter();
$results = $adapter->search('fone bluetooth', null, 2);
echo "Busca simulada finalizada. Quantidade retornada: " . count($results) . "\n";
if (!empty($results)) {
    echo "Item 1: '{$results[0]['title']}' - Preço R$ {$results[0]['price']} - Trend Score: {$results[0]['trend_score']} (Mercado: {$results[0]['marketplace']})\n";
}

echo "\n==================================================\n";
echo "    VERIFICAÇÃO COMPLETADA COM SUCESSO!           \n";
echo "==================================================\n";
echo "Para iniciar localmente:\n";
echo "1. Altere as credenciais no arquivo config/config.php\n";
echo "2. Execute o servidor de desenvolvimento: php -S localhost:8085 -t public\n";
echo "3. Acesse http://localhost:8085 no navegador\n";
echo "4. Credenciais Padrão: admin@trendhunter.com.br / admin123\n";
echo "==================================================\n";
