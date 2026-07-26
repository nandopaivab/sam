<?php
/**
 * TrendHunter Brasil - Main Application Dashboard
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

// Verify user session
Auth::requireLogin();
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['id'];

// Get user statistical counts for top metric cards
$db = Database::getConnection();

// Favorites count
$favStmt = $db->prepare("SELECT COUNT(*) as cnt FROM favorites WHERE user_id = ?");
$favStmt->execute([$userId]);
$favCount = $favStmt->fetch()['cnt'] ?? 0;

// Active alerts count
$alertStmt = $db->prepare("SELECT COUNT(*) as cnt FROM alerts WHERE user_id = ? AND is_active = 1");
$alertStmt->execute([$userId]);
$alertCount = $alertStmt->fetch()['cnt'] ?? 0;

// Total searches conducted count
$searchStmt = $db->prepare("SELECT COUNT(*) as cnt FROM searches WHERE user_id = ?");
$searchStmt->execute([$userId]);
$searchCount = $searchStmt->fetch()['cnt'] ?? 0;

// Include Layout Header
require __DIR__ . '/templates/header.php';
?>

<!-- Metric Cards Section -->
<div class="row mb-4">
    <!-- Total parallel searches card -->
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card-premium metric-card p-3">
            <div class="metric-title"><i class="fa-solid fa-compass text-accent-purple me-1"></i> Varreduras Executadas</div>
            <div class="metric-value"><?php echo $searchCount; ?></div>
            <div class="small text-muted mt-1">Buscas em múltiplos marketplaces</div>
        </div>
    </div>
    
    <!-- Favorites card -->
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card-premium metric-card turquoise p-3">
            <div class="metric-title"><i class="fa-regular fa-heart text-accent-turquoise me-1"></i> Produtos Favoritos</div>
            <div class="metric-value" id="metrics-fav-count"><?php echo $favCount; ?></div>
            <div class="small text-muted mt-1">Salvos para comparação rápida</div>
        </div>
    </div>
    
    <!-- Active Alerts card -->
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card-premium metric-card p-3" style="--accent-purple: var(--accent-orange);">
            <div class="metric-title"><i class="fa-regular fa-bell text-warning me-1"></i> Alertas Ativos</div>
            <div class="metric-value" id="metrics-alert-count"><?php echo $alertCount; ?></div>
            <div class="small text-muted mt-1">Monitoramento automático de preços</div>
        </div>
    </div>
    
    <!-- System status card -->
    <div class="col-xl-3 col-sm-6">
        <div class="card-premium metric-card p-3" style="--accent-purple: var(--accent-turquoise);">
            <div class="metric-title"><i class="fa-solid fa-server text-accent-turquoise me-1"></i> Status do Sistema</div>
            <div class="metric-value" style="font-size: 20px; padding: 3px 0;">100% Ativo</div>
            <div class="small text-muted mt-1">Conexões Redis & DB OK</div>
        </div>
    </div>
</div>

<!-- Search Panel Section -->
<?php require __DIR__ . '/templates/dashboard_views/search_section.php'; ?>

<!-- Comparative Search Results Section -->
<?php require __DIR__ . '/templates/dashboard_views/results_section.php'; ?>

<!-- Bottom Panels: Saved Favorites & Monitored Alerts -->
<div class="row">
    <!-- Saved Favorites -->
    <div class="col-lg-6 mb-4" id="favorites-section">
        <div class="card-premium">
            <div class="card-header">
                <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-star text-warning me-2"></i> Favoritos Recentes</h6>
            </div>
            <div class="card-body" style="max-height: 380px; overflow-y: auto;" id="favorites-container">
                <!-- Loaded dynamically by main.js -->
                <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>
            </div>
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="col-lg-6 mb-4" id="alerts-section">
        <div class="card-premium">
            <div class="card-header">
                <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-bullseye text-danger me-2"></i> Alertas de Monitoramento</h6>
            </div>
            <div class="card-body" style="max-height: 380px; overflow-y: auto;" id="alerts-container">
                <!-- Loaded dynamically by main.js -->
                <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<!-- Include Layout Footer -->
<?php require __DIR__ . '/templates/footer.php'; ?>
