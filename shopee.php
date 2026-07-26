<?php
/**
 * TrendHunter Brasil - SAM - Shopee Intelligence & Cost Calculator
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

// Mock Shopee products databases
$shopeeVirals = [
    ['id' => 1, 'title' => 'Fone de Ouvido Gatinho com Led Bluetooth', 'price' => 39.90, 'sales' => 12400, 'revenue' => 494760.00, 'growth' => '+240%', 'video_views' => '1.8M', 'category' => 'Eletrônicos & Áudio', 'score' => 96],
    ['id' => 2, 'title' => 'Mini Liquidificador Squeeze Portátil USB', 'price' => 29.90, 'sales' => 18500, 'revenue' => 553150.00, 'growth' => '+180%', 'video_views' => '2.4M', 'category' => 'Utilidades Domésticas', 'score' => 94],
    ['id' => 3, 'title' => 'Escova de Limpeza Elétrica Spin Recarregável', 'price' => 69.90, 'sales' => 8900, 'revenue' => 622100.00, 'growth' => '+310%', 'video_views' => '1.2M', 'category' => 'Utilidades Domésticas', 'score' => 92],
    ['id' => 4, 'title' => 'Seladora de Embalagens Plásticas Portátil Ímã', 'price' => 12.90, 'sales' => 22000, 'revenue' => 283800.00, 'growth' => '+90%', 'video_views' => '3.1M', 'category' => 'Cozinha & Organização', 'score' => 89],
    ['id' => 5, 'title' => 'Caneta Depiladora de Sobrancelha Elétrica USB', 'price' => 19.90, 'sales' => 14200, 'revenue' => 282580.00, 'growth' => '+150%', 'video_views' => '950K', 'category' => 'Beleza & Cuidados', 'score' => 87]
];

$flashDeals = [
    ['title' => 'Garrafa Térmica Motivacional Squeeze 2L', 'price' => 18.90, 'original_price' => 39.90, 'claimed' => 86, 'velocity' => 'Muito Alta', 'badge' => 'Oferta Quente'],
    ['title' => 'Organizador de Acrílico Giratório 360', 'price' => 34.90, 'original_price' => 69.90, 'claimed' => 74, 'velocity' => 'Alta', 'badge' => 'Últimas Unidades'],
    ['title' => 'Mini Processador e Triturador Alimentos USB', 'price' => 15.50, 'original_price' => 35.00, 'claimed' => 92, 'velocity' => 'Explosiva', 'badge' => 'Esgotando'],
    ['title' => 'Tripé de Celular Ring Light com Controle', 'price' => 24.90, 'original_price' => 59.90, 'claimed' => 48, 'velocity' => 'Média', 'badge' => 'Oferta do Dia']
];

$shopeeKeywords = [
    ['keyword' => 'achadinhos da shopee', 'volume' => 450000, 'cpc' => 0.12, 'growth' => '+180%'],
    ['keyword' => 'organizador de maquiagem', 'volume' => 120000, 'cpc' => 0.25, 'growth' => '+95%'],
    ['keyword' => 'fone bluetooth sem fio', 'volume' => 350000, 'cpc' => 0.32, 'growth' => '+110%'],
    ['keyword' => 'garrafa motivacional 2l', 'volume' => 180000, 'cpc' => 0.18, 'growth' => '+140%'],
    ['keyword' => 'mini processador usb', 'volume' => 140000, 'cpc' => 0.15, 'growth' => '+85%']
];

require __DIR__ . '/templates/header.php';
?>

<!-- Shopee Dashboard Custom Styles -->
<style>
    .shopee-orange {
        color: #ee4d2d !important;
    }
    .bg-shopee-orange {
        background-color: #ee4d2d !important;
    }
    .border-shopee-orange {
        border-color: rgba(238, 77, 45, 0.2) !important;
    }
    .bg-shopee-glow {
        background-color: rgba(238, 77, 45, 0.05) !important;
    }
    .shopee-tabs {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        gap: 6px;
    }
    .shopee-tabs .nav-link {
        color: var(--text-secondary);
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        padding: 12px 22px;
        transition: all 0.2s ease;
        font-size: 13px;
    }
    .shopee-tabs .nav-link:hover {
        color: var(--text-primary);
        background-color: rgba(255, 255, 255, 0.02);
    }
    .shopee-tabs .nav-link.active {
        color: #ee4d2d !important;
        background-color: var(--card-bg) !important;
        border-color: rgba(255, 255, 255, 0.08) rgba(255, 255, 255, 0.08) transparent !important;
        border-top: 2px solid #ee4d2d !important;
    }
    .progress-bar-shopee {
        background-color: #ee4d2d;
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-shopee-glow text-shopee-orange border border-shopee-orange px-3 py-1">
                    <i class="fa-solid fa-bag-shopping me-1"></i> SAM - SHOPEE INTEL
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="fa-solid fa-fire-flame-curved me-1"></i> Achadinhos & Ofertas Relâmpago
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">SAM - Shopee Inteligência de Nicho</h1>
            <p class="text-muted small mb-0">Rastreamento de ofertas relâmpago, produtos virais de alta conversão (Achadinhos), volume de buscas Shopee Ads e calculadora oficial de margem.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="alert('IA reanalisando ranking de achadinhos virais da Shopee Brasil...'); location.reload();">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Atualizar IA Real-Time
            </button>
        </div>
    </div>

    <!-- KPI Metrics Row (Themed Shopee) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card p-3" style="border-left: 4px solid #ee4d2d;">
                <div class="metric-title shopee-orange"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Achadinhos Rastreados</div>
                <div class="metric-value">12.840</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>+82 mapeados hoje</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card turquoise p-3">
                <div class="metric-title"><i class="fa-solid fa-wallet text-accent-turquoise me-1"></i> Faturamento Estimado (Top Lojas)</div>
                <div class="metric-value">R$ 4.89M</div>
                <div class="small text-muted mt-1">Média mensal do nicho</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card purple p-3">
                <div class="metric-title"><i class="fa-brands fa-tiktok text-accent-purple me-1"></i> Cliques em Redes Sociais</div>
                <div class="metric-value">3.2M</div>
                <div class="small text-success mt-1">Alta conversão orgânica</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card yellow p-3">
                <div class="metric-title"><i class="fa-solid fa-ticket text-warning me-1"></i> Cupons de Frete Ativos</div>
                <div class="metric-value">45 ativos</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-check me-1"></i>Monitorados via Shopee API</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs shopee-tabs mb-4" id="shopeeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="virals-tab" data-bs-toggle="tab" data-bs-target="#virals-panel" type="button" role="tab"><i class="fa-solid fa-fire me-1"></i> Achadinhos Virais</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="flash-tab" data-bs-toggle="tab" data-bs-target="#flash-panel" type="button" role="tab"><i class="fa-solid fa-bolt me-1"></i> Ofertas Relâmpago (Flash Deals)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shopee-calc-tab" data-bs-toggle="tab" data-bs-target="#shopee-calc-panel" type="button" role="tab"><i class="fa-solid fa-calculator me-1"></i> Calculadora de Custos Shopee</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shopee-ads-tab" data-bs-toggle="tab" data-bs-target="#shopee-ads-panel" type="button" role="tab"><i class="fa-solid fa-rectangle-ad me-1"></i> Shopee Ads Palavras-chave</button>
        </li>
    </ul>

    <!-- Tab Panels -->
    <div class="tab-content" id="shopeeTabsContent">
        
        <!-- Tab 1: Achadinhos Virais -->
        <div class="tab-pane fade show active" id="virals-panel" role="tabpanel" aria-labelledby="virals-tab">
            <div class="card-premium p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-chart-line text-metrify-cyan me-2"></i> Produtos Virais Organizados (TikTok & Insta para Shopee)</h5>
                    <small class="text-muted">Rendimento estimado nos últimos 30 dias</small>
                </div>
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço Shopee</th>
                                <th>Vendas Est.</th>
                                <th>Faturamento Est.</th>
                                <th>Visualizações Vídeo</th>
                                <th>Score IA</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shopeeVirals as $v): ?>
                                <tr>
                                    <td class="fw-bold text-white"><i class="fa-solid fa-bag-shopping shopee-orange me-2"></i> <?php echo htmlspecialchars($v['title']); ?></td>
                                    <td class="fw-semibold">R$ <?php echo number_format($v['price'], 2, ',', '.'); ?></td>
                                    <td class="text-success fw-semibold"><?php echo number_format($v['sales'], 0, ',', '.'); ?>/mês</td>
                                    <td class="fw-bold text-white">R$ <?php echo number_format($v['revenue'], 2, ',', '.'); ?></td>
                                    <td><span class="badge bg-purple-subtle text-accent-purple px-2 py-1"><i class="fa-brands fa-tiktok me-1"></i> <?php echo $v['video_views']; ?></span></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo $v['score']; ?> pts</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="openShopeeCalculatorWith(<?php echo $v['price']; ?>)">
                                            <i class="fa-solid fa-calculator me-1"></i> Margem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Ofertas Relâmpago -->
        <div class="tab-pane fade" id="flash-panel" role="tabpanel" aria-labelledby="flash-tab">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-bolt shopee-orange me-2"></i> Ofertas Relâmpago Shopee BR (Real-Time Flash Deals)</h5>
                <div class="row g-3">
                    <?php foreach ($flashDeals as $fd): ?>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 rounded border border-light-subtle h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.01);">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-danger text-white"><?php echo $fd['badge']; ?></span>
                                        <span class="text-success small fw-bold"><i class="fa-solid fa-gauge-high"></i> <?php echo $fd['velocity']; ?></span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($fd['title']); ?></h6>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <h5 class="fw-bold shopee-orange mb-0">R$ <?php echo number_format($fd['price'], 2, ',', '.'); ?></h5>
                                        <span class="text-muted text-decoration-line-through small">R$ <?php echo number_format($fd['original_price'], 2, ',', '.'); ?></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between text-muted small mb-1" style="font-size: 11px;">
                                        <span>Reservado:</span>
                                        <span><?php echo $fd['claimed']; ?>%</span>
                                    </div>
                                    <div class="progress mb-3" style="height: 6px; background-color: rgba(255,255,255,0.05);">
                                        <div class="progress-bar progress-bar-shopee" role="progressbar" style="width: <?php echo $fd['claimed']; ?>%"></div>
                                    </div>

                                    <button class="btn btn-sm btn-primary w-100 bg-shopee-orange border-0 fw-bold" onclick="window.location.href='index.php?query='+encodeURIComponent('<?php echo addslashes($fd['title']); ?>')">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Rastrear Concorrentes
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Calculadora de Custos Shopee -->
        <div class="tab-pane fade" id="shopee-calc-panel" role="tabpanel" aria-labelledby="shopee-calc-tab">
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="card-premium p-4">
                        <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-calculator shopee-orange me-2"></i> Parâmetros de Simulação</h5>
                        
                        <form id="shopeeCalcForm">
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Preço de Venda Sugerido (R$)</label>
                                <input type="number" step="0.01" id="shopee-calc-price" class="form-control bg-dark text-white border-light-subtle" value="39.90" oninput="calculateShopeeProfit()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Custo Unitário do Produto (R$)</label>
                                <input type="number" step="0.01" id="shopee-calc-cost" class="form-control bg-dark text-white border-light-subtle" value="12.00" oninput="calculateShopeeProfit()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Taxa de Comissão Shopee (%)</label>
                                <select id="shopee-calc-commission" class="form-select bg-dark text-white border-light-subtle" onchange="calculateShopeeProfit()">
                                    <option value="14">14% (Comissão Básica Sem Frete Grátis)</option>
                                    <option value="20" selected>20% (Comissão Com Frete Grátis Extra)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Taxa Fixa Shopee por Item (R$)</label>
                                <input type="number" step="0.01" id="shopee-calc-fixed-fee" class="form-control bg-dark text-white border-light-subtle" value="4.00" oninput="calculateShopeeProfit()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Custos Operacionais & Embalagem (R$)</label>
                                <input type="number" step="0.01" id="shopee-calc-ops" class="form-control bg-dark text-white border-light-subtle" value="2.50" oninput="calculateShopeeProfit()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Investimento de Ads p/ Venda (CPA R$)</label>
                                <input type="number" step="0.01" id="shopee-calc-ads" class="form-control bg-dark text-white border-light-subtle" value="3.00" oninput="calculateShopeeProfit()">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card-premium p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-chart-pie text-success me-2"></i> Resultado Financeiro Unitário</h5>
                            
                            <div class="d-flex flex-column gap-2 text-white" style="font-size: 13px;">
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">Faturamento Unitário (Venda):</span>
                                    <strong id="shopee-val-sales">R$ 39,90</strong>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">(-) Custo do Produto (COGS):</span>
                                    <span class="text-danger" id="shopee-val-cost">-R$ 12,00</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">(-) Comissão da Categoria Shopee:</span>
                                    <span class="text-danger" id="shopee-val-comm">-R$ 7,98</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">(-) Taxa Fixa por Item Vendido:</span>
                                    <span class="text-danger" id="shopee-val-fixed">-R$ 4,00</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">(-) Embalagem & Operação:</span>
                                    <span class="text-danger" id="shopee-val-ops">-R$ 2,50</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-2">
                                    <span class="text-muted">(-) Custo de Aquisição (Ads/CPA):</span>
                                    <span class="text-danger" id="shopee-val-ads">-R$ 3,00</span>
                                </div>

                                <div class="d-flex justify-content-between bg-shopee-glow border border-shopee-orange p-3 rounded mt-3">
                                    <span class="shopee-orange fw-bold fs-6">Lucro Líquido Unitário:</span>
                                    <strong class="text-success fs-5" id="shopee-val-profit">R$ 10,42</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-light-subtle text-center">
                            <span class="text-muted small">Margem de Lucro Real: </span>
                            <span class="h4 fw-bold text-success" id="shopee-val-margin">26.1%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Shopee Ads -->
        <div class="tab-pane fade" id="shopee-ads-panel" role="tabpanel" aria-labelledby="shopee-ads-tab">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-rectangle-ad shopee-orange me-2"></i> Buscas em Alta no Planejador Shopee Ads BR</h5>
                
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Palavra-chave Shopee Ads</th>
                                <th>Volume de Buscas Mensal</th>
                                <th>CPC Sugerido</th>
                                <th>Crescimento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shopeeKeywords as $kw): ?>
                                <tr>
                                    <td class="fw-bold text-white"><i class="fa-solid fa-magnifying-glass me-2 text-muted"></i> <?php echo htmlspecialchars($kw['keyword']); ?></td>
                                    <td><?php echo number_format($kw['volume'], 0, ',', '.'); ?> buscas</td>
                                    <td class="fw-bold text-warning">R$ <?php echo number_format($kw['cpc'], 2, ',', '.'); ?></td>
                                    <td class="text-success fw-bold"><i class="fa-solid fa-arrow-trend-up"></i> <?php echo $kw['growth']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary bg-shopee-orange border-0" onclick="window.location.href='index.php?query='+encodeURIComponent('<?php echo addslashes($kw['keyword']); ?>')">
                                            <i class="fa-solid fa-circle-play me-1"></i> Varrer Termo
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function openShopeeCalculatorWith(price) {
    // Fill prices
    document.getElementById('shopee-calc-price').value = price;
    document.getElementById('shopee-calc-cost').value = (price * 0.35).toFixed(2); // Auto-suggest cost at 35%
    
    // Switch to tab
    const calcTab = new bootstrap.Tab(document.getElementById('shopee-calc-tab'));
    calcTab.show();

    // Trigger calculation
    calculateShopeeProfit();
}

function calculateShopeeProfit() {
    const price = parseFloat(document.getElementById('shopee-calc-price').value) || 0;
    const cost = parseFloat(document.getElementById('shopee-calc-cost').value) || 0;
    const commPct = parseFloat(document.getElementById('shopee-calc-commission').value) || 0;
    const fixedFee = parseFloat(document.getElementById('shopee-calc-fixed-fee').value) || 0;
    const ops = parseFloat(document.getElementById('shopee-calc-ops').value) || 0;
    const ads = parseFloat(document.getElementById('shopee-calc-ads').value) || 0;

    // Calculation formulas
    const commVal = price * (commPct / 100);
    const profit = price - cost - commVal - fixedFee - ops - ads;
    const margin = price > 0 ? (profit / price) * 100 : 0;

    // UI Updates
    document.getElementById('shopee-val-sales').innerText = 'R$ ' + price.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-cost').innerText = '-R$ ' + cost.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-comm').innerText = '-R$ ' + commVal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-fixed').innerText = '-R$ ' + fixedFee.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-ops').innerText = '-R$ ' + ops.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-ads').innerText = '-R$ ' + ads.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

    document.getElementById('shopee-val-profit').innerText = 'R$ ' + profit.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('shopee-val-margin').innerText = margin.toFixed(1) + '%';

    // Change margin text color based on profitability
    const marginEl = document.getElementById('shopee-val-margin');
    if (margin >= 20) {
        marginEl.className = 'h4 fw-bold text-success';
    } else if (margin >= 10) {
        marginEl.className = 'h4 fw-bold text-warning';
    } else {
        marginEl.className = 'h4 fw-bold text-danger';
    }
}

// Run initial calculations
$(document).ready(function() {
    calculateShopeeProfit();
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
