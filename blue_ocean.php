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
                                    <i class="fa-solid fa-check-circle me-1"></i> <?php echo htmlspecialchars((string)($p['opportunity_badge'] ?? '')); ?> (Score: <?php echo $p['trend_score']; ?>/100)
                                </span>
                                <span class="badge bg-dark-subtle text-white">
                                    <?php echo htmlspecialchars((string)($p['category'] ?? '')); ?> • <?php echo htmlspecialchars((string)($p['niche'] ?? '')); ?>
                                </span>
                            </div>
                            <h4 class="fw-bold text-white mb-1"><?php echo htmlspecialchars((string)($p['title'] ?? '')); ?></h4>
                            <div class="text-muted small" style="font-size: 12px;">
                                <i class="fa-solid fa-users me-1 text-accent-turquoise"></i> Público-alvo: <strong><?php echo htmlspecialchars((string)($p['target_audience'] ?? '')); ?></strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small" style="font-size: 11px;">MARGEM PROJETADA</div>
                            <div class="h4 fw-bold text-success mb-0">+<?php echo number_format((float)($p['proj_margin'] ?? 0.0), 1, ',', '.'); ?>%</div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Problem Solved Alert -->
                        <div class="p-3 rounded mb-4 border border-info-subtle" style="background: rgba(0, 210, 255, 0.05);">
                            <div class="fw-bold small text-info mb-1"><i class="fa-solid fa-lightbulb me-1"></i> Problema Resolvido & Diferencial</div>
                            <p class="text-white small mb-0" style="font-size: 12px;"><?php echo htmlspecialchars((string)($p['problem_solved'] ?? '')); ?></p>
                        </div>

                        <!-- Financials & Market Stats -->
                        <div class="row g-3 mb-4 text-center">
                            <div class="col-4">
                                <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                    <div class="text-muted small" style="font-size: 11px;">PREÇO MÉDIO VAREJO</div>
                                    <div class="fw-bold text-white mt-1">R$ <?php echo number_format((float)($p['avg_price'] ?? 0.0), 2, ',', '.'); ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                    <div class="text-muted small" style="font-size: 11px;">CUSTO ATACADO BR</div>
                                    <div class="fw-bold text-accent-turquoise mt-1">R$ <?php echo number_format((float)($p['est_cost'] ?? 0.0), 2, ',', '.'); ?></div>
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
                        $kits = explode(' | ', (string)($p['suggested_kits'] ?: 'Kit Econômico | Kit Intermediário | Kit Premium'));
                        foreach ($kits as $kit):
                        ?>
                            <div class="p-2 mb-2 rounded border border-light-subtle d-flex align-items-center" style="background: rgba(255,255,255,0.02); font-size: 12px;">
                                <i class="fa-solid fa-gift text-accent-purple me-2"></i>
                                <span><?php echo htmlspecialchars((string)($kit ?? '')); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <!-- Investment Recommendation -->
                        <div class="mt-3 pt-3 border-top border-light-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-white"><i class="fa-solid fa-chart-line text-success me-1"></i> Recomendação IA (<?php echo date('d/m/Y'); ?>):</span>
                                <span class="badge bg-dark-subtle text-muted small"><?php echo htmlspecialchars((string)($p['seasonality'] ?? '')); ?></span>
                            </div>
                            <p class="text-muted small mb-3" style="font-size: 12px;"><?php echo htmlspecialchars((string)($p['investment_recommendation'] ?? '')); ?></p>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" style="font-size: 11px;">
                                    <i class="fa-solid fa-truck me-1 text-accent-turquoise"></i> Fornecedor: <strong><?php echo htmlspecialchars((string)($p['related_suppliers'] ?? 'Distribuidor BR')); ?></strong>
                                </span>
                                <a href="crm.php?company=<?php echo urlencode(substr((string)($p['related_suppliers'] ?? ''), 0, 15)); ?>" class="btn btn-sm btn-outline-primary border-light-subtle">
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-water text-primary me-2"></i> Cadastrar Produto Oceano Azul</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="blueOceanForm">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">Título do Produto *</label>
                            <input type="text" name="title" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Mini Processador de Alimentos Portátil">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Categoria *</label>
                            <input type="text" name="category" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Cozinha / Utilidades">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Nicho *</label>
                            <input type="text" name="niche" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Utilidades Domésticas">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">Público-alvo *</label>
                            <input type="text" name="target_audience" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Moradores de apartamentos compactos">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Score de Tendência (0-100)</label>
                            <input type="number" name="trend_score" class="form-control bg-dark text-white border-light-subtle" value="92">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Etiqueta de Oportunidade</label>
                            <select name="opportunity_badge" class="form-select bg-dark text-white border-light-subtle">
                                <option value="Alta Oportunidade">Alta Oportunidade</option>
                                <option value="Média Oportunidade">Média Oportunidade</option>
                                <option value="Baixa Oportunidade">Baixa Oportunidade</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">Preço Venda Médio (R$) *</label>
                            <input type="number" step="0.01" name="avg_price" class="form-control bg-dark text-white border-light-subtle" required placeholder="0.00">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">Custo Atacado BR (R$) *</label>
                            <input type="number" step="0.01" name="est_cost" class="form-control bg-dark text-white border-light-subtle" required placeholder="0.00">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">Margem Projetada (%)</label>
                            <input type="number" step="0.1" name="proj_margin" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: 150">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Concorrentes 1ª Pág</label>
                            <input type="number" name="approx_competitors" class="form-control bg-dark text-white border-light-subtle" value="5">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Sazonalidade</label>
                            <input type="text" name="seasonality" class="form-control bg-dark text-white border-light-subtle" value="Ano Todo">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Fornecedor Recomendado</label>
                            <input type="text" name="related_suppliers" class="form-control bg-dark text-white border-light-subtle" placeholder="Ex: Distribuidora China BR">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Problema Resolvido & Diferencial *</label>
                        <textarea name="problem_solved" class="form-control bg-dark text-white border-light-subtle" rows="2" required placeholder="Descreva a dor que o produto sana e como se destacar no anúncio..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Kits Sugeridos por IA (Use ' | ' para separar os kits)</label>
                        <input type="text" name="suggested_kits" class="form-control bg-dark text-white border-light-subtle" placeholder="Kit Básico | Kit Duplo + Brinde | Kit Premium 3 Unidades">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Recomendação IA de Investimento</label>
                        <textarea name="investment_recommendation" class="form-control bg-dark text-white border-light-subtle" rows="2" placeholder="Recomendações e estratégias de anúncio do robô..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-light-subtle p-3">
                <button type="button" class="btn btn-outline-secondary border-light-subtle" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveBlueOceanProduct()">
                    <i class="fa-solid fa-check me-1"></i> Cadastrar Produto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function saveBlueOceanProduct() {
    const form = document.getElementById('blueOceanForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'save_blue_ocean');

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao salvar: ' + (data.error || 'Erro desconhecido.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao se conectar com o servidor.');
    });
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
