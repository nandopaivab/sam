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

<!-- Avantpro Style Ecosystem Quick Access Pillars -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <a href="metrify.php" class="avant-pillar-card">
            <div class="avant-pillar-icon" style="background: rgba(245, 158, 11, 0.14); color: #f59e0b;">
                <i class="fa-solid fa-magnifying-glass-dollar"></i>
            </div>
            <div class="text-truncate">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-white" style="font-size: 13px;">SAM Raio-X • ML & SHP</span>
                    <span class="sidebar-badge sidebar-badge-pro" style="margin:0;">HOT</span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Visitas, vendas e market share</div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="blue_ocean.php" class="avant-pillar-card">
            <div class="avant-pillar-icon" style="background: rgba(6, 225, 204, 0.14); color: #06e1cc;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="text-truncate">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-white" style="font-size: 13px;">SAM Hype • Validador IA</span>
                    <span class="sidebar-badge sidebar-badge-hype" style="margin:0;">IA</span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Nichos Oceano Azul sem concorrência</div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="erp.php" class="avant-pillar-card">
            <div class="avant-pillar-icon" style="background: rgba(99, 102, 241, 0.16); color: #818cf8;">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="text-truncate">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-white" style="font-size: 13px;">SAM Control • Operação</span>
                    <span class="sidebar-badge sidebar-badge-control" style="margin:0;">DRE</span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Margem real, estoque e multi-canal</div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="global_trends.php" class="avant-pillar-card">
            <div class="avant-pillar-icon" style="background: rgba(16, 185, 129, 0.14); color: #10b981;">
                <i class="fa-solid fa-camera-retro"></i>
            </div>
            <div class="text-truncate">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-white" style="font-size: 13px;">Alibaba Lens & Atacado</span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Encontrar fornecedores e importação</div>
            </div>
        </a>
    </div>
</div>

<!-- Executive KPI Cards Section (Avantpro Layout) -->
<div class="row g-3 mb-4">
    <!-- Total parallel searches card -->
    <div class="col-xl-3 col-sm-6">
        <div class="avant-kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-title mb-1">Raio-X & Buscas Executadas</div>
                    <div class="metric-value"><?php echo $searchCount; ?></div>
                </div>
                <div class="avant-kpi-icon bg-purple-glow text-accent-purple">
                    <i class="fa-solid fa-radar fa-compass"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between small mt-2 pt-2 border-top border-light-subtle">
                <span class="text-muted" style="font-size: 11px;">9 Marketplaces conectados</span>
                <span class="badge bg-success-subtle text-success" style="font-size: 10px;"><i class="fa-solid fa-arrow-trend-up me-1"></i>Tempo Real</span>
            </div>
        </div>
    </div>
    
    <!-- Favorites / Curva A card -->
    <div class="col-xl-3 col-sm-6">
        <div class="avant-kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-title mb-1">Catálogo Salvo (Curva A)</div>
                    <div class="metric-value" id="metrics-fav-count"><?php echo $favCount; ?></div>
                </div>
                <div class="avant-kpi-icon bg-turquoise-glow text-accent-turquoise">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between small mt-2 pt-2 border-top border-light-subtle">
                <span class="text-muted" style="font-size: 11px;">Anúncios monitorados com IA</span>
                <a href="saved_products.php" class="text-accent-turquoise text-decoration-none fw-bold" style="font-size: 11px;">Ver todos →</a>
            </div>
        </div>
    </div>
    
    <!-- Active Alerts / Price Protection card -->
    <div class="col-xl-3 col-sm-6">
        <div class="avant-kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-title mb-1">Radar de Preço & Promoções</div>
                    <div class="metric-value" id="metrics-alert-count"><?php echo $alertCount; ?></div>
                </div>
                <div class="avant-kpi-icon" style="background: rgba(245, 158, 11, 0.14); color: #f59e0b;">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between small mt-2 pt-2 border-top border-light-subtle">
                <span class="text-muted" style="font-size: 11px;">Gatilhos de variação e BuyBox</span>
                <span class="badge bg-warning-subtle text-warning" style="font-size: 10px;">Monitorando</span>
            </div>
        </div>
    </div>
    
    <!-- SAM Control / Margin Health card -->
    <div class="col-xl-3 col-sm-6">
        <div class="avant-kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-title mb-1">SAM Control • Status</div>
                    <div class="metric-value text-accent-turquoise" style="font-size: 20px; padding: 3px 0;">Margem Protegida</div>
                </div>
                <div class="avant-kpi-icon" style="background: rgba(16, 185, 129, 0.14); color: #10b981;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between small mt-2 pt-2 border-top border-light-subtle">
                <span class="text-muted" style="font-size: 11px;">Taxas ML/Shopee 2026 atualizadas</span>
                <a href="profit_calculator.php" class="text-success text-decoration-none fw-bold" style="font-size: 11px;">Simular →</a>
            </div>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-star text-warning me-2"></i> Produtos Monitorados (Curva A & Favoritos)</h6>
                <a href="saved_products.php" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-2" style="font-size: 11px;">Abrir Catálogo IA</a>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-bullseye text-danger me-2"></i> Radar de Concorrência & Alertas de Preço</h6>
                <span class="badge bg-purple-glow text-accent-purple" style="font-size: 10px;">CRON Ativo</span>
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
