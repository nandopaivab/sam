<?php
/**
 * TrendHunter Brasil - Metrify Category Market Share Intelligence
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
$user = Auth::getCurrentUser();
$db = Database::getConnection();

// Mock Categories database mirroring the user's Metrify screenshot
$categoriesData = [
    [
        'id' => 1,
        'name' => 'Acessórios para Veículos',
        'share' => 47.3,
        'ads' => '102.93 mi',
        'subcategories_count' => 24,
        'avg_ads' => '4.29 mi',
        'color' => '#8e44ad',
        'subcategories' => [
            ['name' => 'Peças de Carros e Caminhonetes', 'share' => 38.2, 'ads' => '39.32 mi'],
            ['name' => 'Acessórios de Tuning', 'share' => 22.5, 'ads' => '23.15 mi'],
            ['name' => 'Pneus e Rodas', 'share' => 18.3, 'ads' => '18.83 mi'],
            ['name' => 'Som Automotivo', 'share' => 11.0, 'ads' => '11.32 mi'],
            ['name' => 'Ferramentas de Oficina', 'share' => 10.0, 'ads' => '10.29 mi']
        ]
    ],
    [
        'id' => 2,
        'name' => 'Casa, Móveis e Decoração',
        'share' => 12.3,
        'ads' => '26.81 mi',
        'subcategories_count' => 12,
        'avg_ads' => '2.23 mi',
        'color' => '#2c3e50',
        'subcategories' => [
            ['name' => 'Móveis para Casa', 'share' => 35.1, 'ads' => '9.41 mi'],
            ['name' => 'Decoração e Adornos', 'share' => 28.4, 'ads' => '7.61 mi'],
            ['name' => 'Iluminação Residencial', 'share' => 18.0, 'ads' => '4.82 mi'],
            ['name' => 'Organização de Espaços', 'share' => 10.5, 'ads' => '2.81 mi'],
            ['name' => 'Cama, Mesa e Banho', 'share' => 8.0, 'ads' => '2.14 mi']
        ]
    ],
    [
        'id' => 3,
        'name' => 'Livros, Revistas e Comics',
        'share' => 5.6,
        'ads' => '12.28 mi',
        'subcategories_count' => 5,
        'avg_ads' => '2.46 mi',
        'color' => '#2980b9',
        'subcategories' => [
            ['name' => 'Livros Acadêmicos', 'share' => 45.0, 'ads' => '5.52 mi'],
            ['name' => 'HQs e Mangás', 'share' => 25.0, 'ads' => '3.07 mi'],
            ['name' => 'Revistas Especializadas', 'share' => 15.0, 'ads' => '1.84 mi'],
            ['name' => 'Livros Infantis', 'share' => 10.0, 'ads' => '1.22 mi'],
            ['name' => 'Colecionáveis', 'share' => 5.0, 'ads' => '0.61 mi']
        ]
    ],
    [
        'id' => 4,
        'name' => 'Calçados, Roupas e Bolsas',
        'share' => 4.1,
        'ads' => '8.91 mi',
        'subcategories_count' => 22,
        'avg_ads' => '405.2 mil',
        'color' => '#3498db',
        'subcategories' => [
            ['name' => 'Tênis e Calçados Esportivos', 'share' => 30.2, 'ads' => '2.69 mi'],
            ['name' => 'Roupas Íntimas e Lingerie', 'share' => 25.5, 'ads' => '2.27 mi'],
            ['name' => 'Moda Casual e Jeans', 'share' => 20.3, 'ads' => '1.81 mi'],
            ['name' => 'Bolsas e Acessórios', 'share' => 14.0, 'ads' => '1.24 mi'],
            ['name' => 'Moda Praia', 'share' => 10.0, 'ads' => '0.89 mi']
        ]
    ],
    [
        'id' => 5,
        'name' => 'Beleza e Cuidado Pessoal',
        'share' => 2.7,
        'ads' => '5.86 mi',
        'subcategories_count' => 13,
        'avg_ads' => '450.9 mil',
        'color' => '#9b59b6',
        'subcategories' => [
            ['name' => 'Cuidados com a Pele (Skincare)', 'share' => 38.0, 'ads' => '2.22 mi'],
            ['name' => 'Maquiagem Facial', 'share' => 27.5, 'ads' => '1.61 mi'],
            ['name' => 'Aparelhos de Cabelo (Secadores)', 'share' => 16.5, 'ads' => '0.96 mi'],
            ['name' => 'Perfumes Importados', 'share' => 10.0, 'ads' => '0.58 mi'],
            ['name' => 'Cuidados com Unhas', 'share' => 8.0, 'ads' => '0.46 mi']
        ]
    ],
    [
        'id' => 6,
        'name' => 'Ferramentas',
        'share' => 2.5,
        'ads' => '5.42 mi',
        'subcategories_count' => 9,
        'avg_ads' => '601.7 mil',
        'color' => '#d35400',
        'subcategories' => [
            ['name' => 'Ferramentas Elétricas', 'share' => 42.1, 'ads' => '2.28 mi'],
            ['name' => 'Ferramentas Manuais', 'share' => 28.0, 'ads' => '1.51 mi'],
            ['name' => 'Organizadores de Ferramentas', 'share' => 15.4, 'ads' => '0.83 mi'],
            ['name' => 'Acessórios Industriais', 'share' => 14.5, 'ads' => '0.78 mi']
        ]
    ],
    [
        'id' => 7,
        'name' => 'Informática',
        'share' => 2.3,
        'ads' => '5.07 mi',
        'subcategories_count' => 19,
        'avg_ads' => '266.6 mil',
        'color' => '#1abc9c',
        'subcategories' => [
            ['name' => 'Acessórios de PC / Periféricos', 'share' => 35.2, 'ads' => '1.78 mi'],
            ['name' => 'Armazenamento (SSD, Pendrive)', 'share' => 28.0, 'ads' => '1.41 mi'],
            ['name' => 'Monitores e Telas', 'share' => 18.8, 'ads' => '0.95 mi'],
            ['name' => 'Componentes de PC (Placas)', 'share' => 18.0, 'ads' => '0.91 mi']
        ]
    ],
    [
        'id' => 8,
        'name' => 'Esportes e Fitness',
        'share' => 2.3,
        'ads' => '4.93 mi',
        'subcategories_count' => 40,
        'avg_ads' => '123.3 mil',
        'color' => '#27ae60',
        'subcategories' => [
            ['name' => 'Musculação e Treino Funcional', 'share' => 33.5, 'ads' => '1.65 mi'],
            ['name' => 'Suplementos Alimentares', 'share' => 26.5, 'ads' => '1.30 mi'],
            ['name' => 'Ciclismo e Bicicletas', 'share' => 22.0, 'ads' => '1.08 mi'],
            ['name' => 'Futebol e Quadra', 'share' => 18.0, 'ads' => '0.88 mi']
        ]
    ],
    [
        'id' => 9,
        'name' => 'Arte, Papelaria e Armarinho',
        'share' => 1.8,
        'ads' => '3.88 mi',
        'subcategories_count' => 4,
        'avg_ads' => '970.0 mil',
        'color' => '#e67e22',
        'subcategories' => [
            ['name' => 'Materiais Escolares', 'share' => 48.2, 'ads' => '1.87 mi'],
            ['name' => 'Armarinho e Costura', 'share' => 26.4, 'ads' => '1.02 mi'],
            ['name' => 'Desenho e Artesanato', 'share' => 15.4, 'ads' => '0.59 mi'],
            ['name' => 'Decoração de Eventos', 'share' => 10.0, 'ads' => '0.38 mi']
        ]
    ],
    [
        'id' => 10,
        'name' => 'Eletrônicos, Áudio e Vídeo',
        'share' => 1.8,
        'ads' => '3.88 mi',
        'subcategories_count' => 15,
        'avg_ads' => '258.6 mil',
        'color' => '#e74c3c',
        'subcategories' => [
            ['name' => 'Smart TVs e Telas', 'share' => 38.0, 'ads' => '1.47 mi'],
            ['name' => 'Caixas de Som Portáteis', 'share' => 27.5, 'ads' => '1.06 mi'],
            ['name' => 'Acessórios de Áudio (Cabos)', 'share' => 19.5, 'ads' => '0.75 mi'],
            ['name' => 'Drones e Câmeras', 'share' => 15.0, 'ads' => '0.58 mi']
        ]
    ]
];

require __DIR__ . '/templates/header.php';
?>

<!-- Metrify style modifications matching Dhiegorose/Metrify screenshot layout -->
<style>
    /* Card design replica */
    .metrify-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .metrify-card:hover {
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .card-stripe {
        position: absolute;
        top: 0;
        left: 20px;
        right: 20px;
        height: 3px;
        border-radius: 0 0 4px 4px;
    }
    .metrify-card-header {
        display: flex;
        align-items: center;
        margin-top: 5px;
        margin-bottom: 12px;
    }
    .bullet-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
        display: inline-block;
    }
    .metrify-card-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 0;
    }
    .metrify-stat-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    .metrify-stat-col {
        flex: 1;
    }
    .metrify-stat-label {
        font-size: 11px;
        color: var(--text-secondary);
        text-transform: capitalize;
        margin-bottom: 3px;
    }
    .metrify-stat-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
    }
    .metrify-pill-container {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .metrify-pill {
        flex: 1;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 5px 8px;
        text-align: center;
        font-size: 11px;
        color: var(--text-secondary);
    }
    .metrify-pill strong {
        color: var(--text-primary);
    }
    .metrify-action-btn {
        background-color: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        transition: all 0.2s ease;
    }
    .metrify-action-btn:hover {
        background-color: var(--text-primary);
        color: var(--card-bg);
    }
    .right-bar-line {
        position: absolute;
        top: 25px;
        bottom: 25px;
        right: 15px;
        width: 1px;
        background-color: var(--border-color);
    }
    .chevron-right-link {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        transition: color 0.2s ease;
    }
    .metrify-card:hover .chevron-right-link {
        color: var(--text-primary);
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Block -->
    <div class="row align-items-center mb-4">
        <div class="col-12 col-md-6">
            <h1 class="h3 fw-bold mb-1 text-white"><i class="fa-solid fa-chart-column text-accent-turquoise me-2"></i> Metrify Análise de Participação</h1>
            <p class="text-muted small mb-0">Visão geral do ecossistema de anúncios divididos por categorias e participação de mercado total.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex align-items-center bg-card-glow p-2 rounded-3 border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                <span class="text-muted small me-2"><i class="fa-solid fa-layer-group me-1"></i> Total Base:</span>
                <span class="text-white small fw-bold">217.779.598 anúncios</span>
                <span class="mx-2 text-muted">|</span>
                <span class="text-white small fw-bold">32 categorias</span>
            </div>
        </div>
    </div>

    <!-- Filters & Sorting Controls (Exact replica of the top buttons in user screenshot) -->
    <div class="row g-3 mb-4 align-items-center">
        <!-- Search bar -->
        <div class="col-12 col-md-4 col-lg-3">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="metrifySearchInput" onkeyup="filterMetrifyCategories()" class="form-control ps-5 bg-dark text-white border-light-subtle" style="border-radius: 10px;" placeholder="Buscar categoria...">
            </div>
        </div>
        
        <!-- Sorting tags -->
        <div class="col-12 col-md-8 col-lg-9 text-md-end">
            <span class="text-muted small me-2">Ordenar por:</span>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary px-3 text-white fw-bold active" id="btn-sort-share" onclick="sortCategories('share')" style="border-radius: 8px 0 0 8px; font-size: 12px; background-color: #5f27cd; border-color: #5f27cd;">
                    <i class="fa-solid fa-chart-pie me-1"></i> Participação
                </button>
                <button type="button" class="btn btn-outline-secondary border-light-subtle px-3 text-white" id="btn-sort-sub" onclick="sortCategories('sub')" style="border-radius: 0 8px 8px 0; font-size: 12px;">
                    <i class="fa-solid fa-sitemap me-1"></i> Subcategorias
                </button>
            </div>
        </div>
    </div>

    <!-- Metrify Grid list of Cards -->
    <div class="row g-3" id="metrifyCardsGrid">
        <?php foreach ($categoriesData as $c): ?>
            <div class="col-12 col-md-6 col-lg-4 metrify-card-col" data-name="<?php echo htmlspecialchars(strtolower($c['name'])); ?>" data-share="<?php echo $c['share']; ?>" data-sub="<?php echo $c['subcategories_count']; ?>">
                <div class="metrify-card h-100">
                    <div class="card-stripe" style="background-color: <?php echo $c['color']; ?>;"></div>
                    
                    <div>
                        <!-- Title header -->
                        <div class="metrify-card-header">
                            <span class="bullet-indicator" style="background-color: <?php echo $c['color']; ?>;"></span>
                            <h3 class="metrify-card-title"><?php echo htmlspecialchars($c['name']); ?></h3>
                        </div>

                        <!-- Market Share & Ads count -->
                        <div class="metrify-stat-row">
                            <div class="metrify-stat-col">
                                <div class="metrify-stat-label">Participação de mercado</div>
                                <div class="metrify-stat-val text-white"><?php echo $c['share']; ?> <span class="small" style="font-size: 11px; font-weight: normal; color: #a4b0be;">%</span></div>
                            </div>
                            <div class="metrify-stat-col ps-2">
                                <div class="metrify-stat-label">Anúncios</div>
                                <div class="metrify-stat-val text-white"><?php echo $c['ads']; ?></div>
                            </div>
                        </div>

                        <!-- Sub-stats pills -->
                        <div class="metrify-pill-container">
                            <div class="metrify-pill">
                                <i class="fa-solid fa-sitemap me-1 text-muted"></i> <strong><?php echo $c['subcategories_count']; ?></strong> subcat.
                            </div>
                            <div class="metrify-pill">
                                <i class="fa-solid fa-layer-group me-1 text-muted"></i> <strong><?php echo $c['avg_ads']; ?></strong> méd.
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Category Analysis action button -->
                    <div style="padding-right: 25px; position: relative;">
                        <button class="metrify-action-btn" onclick="openCategoryAnalysisModal(<?php echo $c['id']; ?>)">
                            <i class="fa-solid fa-chart-simple me-2"></i> Análise da Categoria
                        </button>
                        <div class="right-bar-line"></div>
                        <a href="#" class="chevron-right-link" onclick="openCategoryAnalysisModal(<?php echo $c['id']; ?>); return false;">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: Category Analysis Breakdown -->
<div class="modal fade" id="categoryAnalysisModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-chart-simple text-accent-turquoise me-2"></i> Análise de Subcategorias & Mercado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <span class="bullet-indicator" id="modal-bullet" style="width: 12px; height: 12px;"></span>
                    <h4 class="fw-bold text-white mb-0" id="modal-category-name">Categoria</h4>
                </div>

                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small">Participação Total</div>
                            <div class="h3 fw-bold text-white mt-1" id="modal-share-val">0.0%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small">Anúncios Totais</div>
                            <div class="h3 fw-bold text-metrify-cyan mt-1" id="modal-ads-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small">Subcategorias</div>
                            <div class="h3 fw-bold text-warning mt-1" id="modal-subs-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small">Média de Anúncios</div>
                            <div class="h3 fw-bold text-accent-purple mt-1" id="modal-avg-val">0</div>
                        </div>
                    </div>
                </div>

                <!-- Subcategories breakdown table -->
                <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-list-ul me-2"></i> Distribuição por Subcategoria (Mercado Livre BR):</h6>
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Subcategoria</th>
                                <th style="width: 150px;">Participação (%)</th>
                                <th style="width: 150px;">Anúncios Est.</th>
                            </tr>
                        </thead>
                        <tbody id="modal-subcategories-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-light-subtle">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
const categoriesData = <?php echo json_encode($categoriesData); ?>;

function openCategoryAnalysisModal(catId) {
    const cat = categoriesData.find(c => c.id === catId);
    if (!cat) return;

    // Fill details
    document.getElementById('modal-bullet').style.backgroundColor = cat.color;
    document.getElementById('modal-category-name').innerText = cat.name;
    document.getElementById('modal-share-val').innerText = cat.share + '%';
    document.getElementById('modal-ads-val').innerText = cat.ads;
    document.getElementById('modal-subs-val').innerText = cat.subcategories_count;
    document.getElementById('modal-avg-val').innerText = cat.avg_ads;

    // Table body
    const tbody = document.getElementById('modal-subcategories-table-body');
    tbody.innerHTML = '';

    cat.subcategories.forEach(sub => {
        const tr = `
            <tr>
                <td class="fw-semibold text-white">${sub.name}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold text-white me-2" style="min-width: 45px;">${sub.share}%</span>
                        <div class="progress w-100" style="height: 4px; background-color: rgba(255,255,255,0.05);">
                            <div class="progress-bar" role="progressbar" style="width: ${sub.share}%; background-color: ${cat.color};"></div>
                        </div>
                    </div>
                </td>
                <td class="fw-bold text-metrify-cyan">${sub.ads}</td>
            </tr>
        `;
        tbody.innerHTML += tr;
    });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('categoryAnalysisModal'));
    modal.show();
}

function filterMetrifyCategories() {
    const input = document.getElementById('metrifySearchInput');
    const filter = input.value.toLowerCase().trim();
    const cols = document.querySelectorAll('.metrify-card-col');

    cols.forEach(col => {
        const name = col.getAttribute('data-name');
        if (name.includes(filter)) {
            col.style.display = '';
        } else {
            col.style.display = 'none';
        }
    });
}

function sortCategories(type) {
    const grid = document.getElementById('metrifyCardsGrid');
    const cols = Array.from(grid.querySelectorAll('.metrify-card-col'));

    // Highlight active button
    if (type === 'share') {
        document.getElementById('btn-sort-share').className = 'btn btn-primary px-3 text-white fw-bold active';
        document.getElementById('btn-sort-sub').className = 'btn btn-outline-secondary border-light-subtle px-3 text-white';
        
        // Sort descending by share
        cols.sort((a, b) => {
            return parseFloat(b.getAttribute('data-share')) - parseFloat(a.getAttribute('data-share'));
        });
    } else {
        document.getElementById('btn-sort-share').className = 'btn btn-outline-secondary border-light-subtle px-3 text-white';
        document.getElementById('btn-sort-sub').className = 'btn btn-primary px-3 text-white fw-bold active';
        document.getElementById('btn-sort-sub').style.backgroundColor = '#5f27cd';
        document.getElementById('btn-sort-sub').style.borderColor = '#5f27cd';
        document.getElementById('btn-sort-share').style.backgroundColor = 'transparent';

        // Sort descending by subcategories count
        cols.sort((a, b) => {
            return parseInt(b.getAttribute('data-sub')) - parseInt(a.getAttribute('data-sub'));
        });
    }

    // Re-append items in new order
    cols.forEach(col => grid.appendChild(col));
}

// Set initial sorted view by share
$(document).ready(function() {
    sortCategories('share');
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
