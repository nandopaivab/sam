<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

use TrendHunter\Auth;
use TrendHunter\Database;

Auth::requireLogin();
$user = Auth::user();
$db = Database::getConnection();

// Ensure table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS blue_ocean_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        niche VARCHAR(100) NOT NULL,
        target_audience VARCHAR(255) NOT NULL,
        problem_solved TEXT NOT NULL,
        avg_price DECIMAL(10,2) NOT NULL,
        est_cost DECIMAL(10,2) NOT NULL,
        proj_margin DECIMAL(5,2) NOT NULL,
        approx_competitors INTEGER DEFAULT 10,
        trend_score INTEGER DEFAULT 90,
        seasonality VARCHAR(100) DEFAULT 'Ano Todo',
        related_suppliers TEXT DEFAULT NULL,
        suggested_kits TEXT DEFAULT NULL,
        opportunity_badge VARCHAR(50) DEFAULT 'Alta Oportunidade',
        investment_recommendation TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");
} catch (\Exception $e) {}

// Fetch Blue Ocean opportunities
$stmt = $db->query("SELECT * FROM blue_ocean_products ORDER BY id DESC");
$products = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

include __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                    <i class="fa-solid fa-water me-1"></i> MERCADOS INEXPLORADOS BR
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="fa-solid fa-fire me-1"></i> Alta Demanda & Baixa Concorrência
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Produtos "Oceano Azul" & Kits Inteligentes</h1>
            <p class="text-muted small mb-0">Produtos com alta procura no TikTok/Shopee, baixa concorrência e alto potencial de diferenciação através de kits.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="alert('IA varrendo o Mercado Livre e Shopee em tempo real...'); location.reload();">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Atualizar IA Real-Time
            </button>
            <button class="btn btn-primary px-4 fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#blueOceanModal">
                <i class="fa-solid fa-plus me-2"></i> Cadastrar Oportunidade
            </button>
        </div>
    </div>

    <!-- Opportunities Grid -->
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
            <div class="col-12 col-lg-6">
                <div class="card border-light-subtle h-100 overflow-hidden" style="border-radius: 18px; background: rgba(255,255,255,0.02);">
                    <div class="card-header border-bottom border-light-subtle p-4 d-flex justify-content-between align-items-start" style="background: rgba(0, 210, 255, 0.05);">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fa-solid fa-check-circle me-1"></i> <?php echo htmlspecialchars($p['opportunity_badge']); ?> (Score: <?php echo $p['trend_score']; ?>/100)
                                </span>
                                <span class="badge bg-dark-subtle text-white">
                                    <?php echo htmlspecialchars($p['category']); ?> • <?php echo htmlspecialchars($p['niche']); ?>
                                </span>
                            </div>
                            <h4 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($p['title']); ?></h4>
                            <div class="text-muted small" style="font-size: 12px;">
                                <i class="fa-solid fa-users me-1 text-accent-turquoise"></i> Público-alvo: <strong><?php echo htmlspecialchars($p['target_audience']); ?></strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small" style="font-size: 11px;">MARGEM PROJETADA</div>
                            <div class="h4 fw-bold text-success mb-0">+<?php echo number_format($p['proj_margin'], 1, ',', '.'); ?>%</div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Problem Solved Alert -->
                        <div class="p-3 rounded mb-4 border border-info-subtle" style="background: rgba(0, 210, 255, 0.05);">
                            <div class="fw-bold small text-info mb-1"><i class="fa-solid fa-lightbulb me-1"></i> Problema Resolvido & Diferencial</div>
                            <p class="text-white small mb-0" style="font-size: 12px;"><?php echo htmlspecialchars($p['problem_solved']); ?></p>
                        </div>

                        <!-- Financials & Market Stats -->
                        <div class="row g-3 mb-4 text-center">
                            <div class="col-4">
                                <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                    <div class="text-muted small" style="font-size: 11px;">PREÇO MÉDIO VAREJO</div>
                                    <div class="fw-bold text-white mt-1">R$ <?php echo number_format($p['avg_price'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                    <div class="text-muted small" style="font-size: 11px;">CUSTO ATACADO BR</div>
                                    <div class="fw-bold text-accent-turquoise mt-1">R$ <?php echo number_format($p['est_cost'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                    <div class="text-muted small" style="font-size: 11px;">CONCORRENTES 1ª PÁG</div>
                                    <div class="fw-bold text-warning mt-1"><?php echo $p['approx_competitors']; ?> vendedores</div>
                                </div>
                            </div>
                        </div>

                        <!-- 3 AI Suggested Kits -->
                        <h6 class="fw-bold text-white mb-2 small"><i class="fa-solid fa-box-open text-warning me-1"></i> Sugestões de Kits de Diferenciação por IA:</h6>
                        <?php 
                        $kits = explode(' | ', $p['suggested_kits'] ?: 'Kit Econômico | Kit Intermediário | Kit Premium');
                        foreach ($kits as $kit):
                        ?>
                            <div class="p-2 mb-2 rounded border border-light-subtle d-flex align-items-center" style="background: rgba(255,255,255,0.02); font-size: 12px;">
                                <i class="fa-solid fa-gift text-accent-purple me-2"></i>
                                <span><?php echo htmlspecialchars($kit); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <!-- Investment Recommendation -->
                        <div class="mt-3 pt-3 border-top border-light-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-white"><i class="fa-solid fa-chart-line text-success me-1"></i> Recomendação IA (<?php echo date('d/m/Y'); ?>):</span>
                                <span class="badge bg-dark-subtle text-muted small"><?php echo htmlspecialchars($p['seasonality']); ?></span>
                            </div>
                            <p class="text-muted small mb-3" style="font-size: 12px;"><?php echo htmlspecialchars($p['investment_recommendation']); ?></p>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" style="font-size: 11px;">
                                    <i class="fa-solid fa-truck me-1 text-accent-turquoise"></i> Fornecedor: <strong><?php echo htmlspecialchars($p['related_suppliers'] ?: 'Distribuidor BR'); ?></strong>
                                </span>
                                <a href="crm.php?company=<?php echo urlencode(substr($p['related_suppliers'], 0, 15)); ?>" class="btn btn-sm btn-outline-primary border-light-subtle">
                                    <i class="fa-solid fa-handshake-angle me-1"></i> Abrir Negociação
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal to add new Blue Ocean item -->
<div class="modal fade" id="blueOceanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-water text-primary me-2"></i> Cadastrar Produto Oceano Azul</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Para adicionar novas oportunidades do seu nicho, utilize o painel comercial principal ou importe planilhas de produtos virais.</p>
                <div class="alert alert-dark border-light-subtle small mb-0">
                    <i class="fa-solid fa-robot text-accent-purple me-2"></i> O motor da IA analisa diariamente produtos com alta procura em termos de busca e menos de 15 concorrentes com selo Full.
                </div>
            </div>
            <div class="modal-footer border-top border-light-subtle">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
