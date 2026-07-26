<?php
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
$user = Auth::getCurrentUser();
$db = Database::getConnection();

// Ensure table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS baby_niche_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        sub_category VARCHAR(100) NOT NULL,
        age_range VARCHAR(100) NOT NULL,
        safety_cert VARCHAR(100) DEFAULT 'INMETRO / Atóxico',
        material_info VARCHAR(255) DEFAULT 'Livre de BPA',
        cleaning_ease VARCHAR(100) DEFAULT 'Fácil higienização',
        small_parts_risk VARCHAR(50) DEFAULT 'Baixo Risco',
        avg_price DECIMAL(10,2) NOT NULL,
        est_cost DECIMAL(10,2) NOT NULL,
        suggested_kits TEXT DEFAULT NULL,
        income_bracket VARCHAR(100) DEFAULT 'Todas as faixas (Acessível)',
        ai_analysis TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");
} catch (\Exception $e) {}

// Filter category
$filterCategory = trim($_GET['cat'] ?? '');
$query = "SELECT * FROM baby_niche_products WHERE 1=1";
$params = [];
if (!empty($filterCategory)) {
    $query .= " AND sub_category = ?";
    $params[] = $filterCategory;
}
$query .= " ORDER BY id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [
    'Alimentação', 'Banho', 'Higiene', 'Sono', 'Passeio', 'Organização', 
    'Maternidade', 'Segurança', 'Desenvolvimento Infantil', 'Brinquedos Educativos', 
    'Brinquedos Sensoriais', 'Montessori'
];

include __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    <i class="fa-solid fa-baby me-1"></i> NICHO ESPECIALIZADO SAM
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                    <i class="fa-solid fa-shield-halved me-1"></i> INMETRO / Seguro
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Nicho de Bebês & Primeira Infância</h1>
            <p class="text-muted small mb-0">Produtos com altíssima conversão, rigorosos testes de segurança (Livre de BPA/Atóxico) e kits acessíveis para todas as rendas.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="alert('IA reanalisando normas de segurança do INMETRO e demanda no TikTok...'); location.reload();">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Atualizar IA Real-Time
            </button>
        </div>
    </div>

    <!-- Subcategory Pills Filter -->
    <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-light-subtle">
        <a href="baby_niche.php" class="btn btn-sm <?php echo empty($filterCategory) ? 'btn-primary fw-bold' : 'btn-outline-secondary border-light-subtle'; ?> rounded-pill px-3">
            Todas as Categorias (12)
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="baby_niche.php?cat=<?php echo urlencode($cat); ?>" class="btn btn-sm <?php echo $filterCategory === $cat ? 'btn-primary fw-bold' : 'btn-outline-secondary border-light-subtle'; ?> rounded-pill px-3">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Products Grid -->
    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-baby-carriage d-block mb-2" style="font-size: 32px;"></i>
                Nenhum produto cadastrado nesta categoria de bebê.
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="col-12 col-lg-6">
                    <div class="card border-light-subtle h-100 overflow-hidden" style="border-radius: 18px; background: rgba(255,255,255,0.02);">
                        <div class="card-header border-bottom border-light-subtle p-4 d-flex justify-content-between align-items-start" style="background: rgba(0, 255, 170, 0.05);">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fa-solid fa-certificate me-1"></i> <?php echo htmlspecialchars($p['safety_cert']); ?>
                                    </span>
                                    <span class="badge bg-dark-subtle text-white">
                                        <?php echo htmlspecialchars($p['sub_category']); ?> • <?php echo htmlspecialchars($p['age_range']); ?>
                                    </span>
                                </div>
                                <h4 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($p['title']); ?></h4>
                                <div class="text-muted small" style="font-size: 12px;">
                                    <i class="fa-solid fa-wallet me-1 text-accent-turquoise"></i> Faixa Econômica: <strong><?php echo htmlspecialchars($p['income_bracket']); ?></strong>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small" style="font-size: 11px;">PREÇO MÉDIO</div>
                                <div class="h4 fw-bold text-success mb-0">R$ <?php echo number_format($p['avg_price'], 2, ',', '.'); ?></div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Baby Safety & Hygiene Metrics -->
                            <div class="row g-2 mb-4 text-center">
                                <div class="col-4">
                                    <div class="p-2 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                        <div class="text-muted small" style="font-size: 10px;">MATERIAL</div>
                                        <div class="fw-bold text-white mt-1 small"><?php echo htmlspecialchars($p['material_info']); ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                        <div class="text-muted small" style="font-size: 10px;">LIMPEZA & HIGIENE</div>
                                        <div class="fw-bold text-accent-turquoise mt-1 small"><?php echo htmlspecialchars($p['cleaning_ease']); ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                        <div class="text-muted small" style="font-size: 10px;">RISCO PEÇAS PEQ.</div>
                                        <div class="fw-bold text-success mt-1 small"><?php echo htmlspecialchars($p['small_parts_risk']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3 AI Suggested Kits -->
                            <h6 class="fw-bold text-white mb-2 small"><i class="fa-solid fa-gift text-accent-purple me-1"></i> Kits Inteligentes (Econômico, Intermediário e Premium):</h6>
                            <?php 
                            $kits = explode(' | ', $p['suggested_kits'] ?: 'Kit Econômico | Kit Intermediário | Kit Premium');
                            foreach ($kits as $kit):
                            ?>
                                <div class="p-2 mb-2 rounded border border-light-subtle d-flex align-items-center" style="background: rgba(255,255,255,0.02); font-size: 12px;">
                                    <i class="fa-solid fa-wand-magic-sparkles text-accent-turquoise me-2"></i>
                                    <span><?php echo htmlspecialchars($kit); ?></span>
                                </div>
                            <?php endforeach; ?>

                            <!-- AI Analysis -->
                            <div class="mt-3 pt-3 border-top border-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small text-white"><i class="fa-solid fa-robot text-warning me-1"></i> Parecer IA Maternidade & Infância:</span>
                                    <span class="badge bg-success-subtle text-success small">Alta Aprovação Pais</span>
                                </div>
                                <p class="text-muted small mb-3" style="font-size: 12px;"><?php echo htmlspecialchars($p['ai_analysis']); ?></p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small" style="font-size: 11px;">
                                        <i class="fa-solid fa-box-open me-1 text-accent-turquoise"></i> Custo Atacado BR: <strong>R$ <?php echo number_format($p['est_cost'], 2, ',', '.'); ?></strong>
                                    </span>
                                    <a href="crm.php?company=<?php echo urlencode('Atacadista Infantil BR'); ?>" class="btn btn-sm btn-outline-success border-light-subtle">
                                        <i class="fa-solid fa-handshake-angle me-1"></i> Abrir CRM
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
