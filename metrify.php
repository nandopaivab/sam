<?php
/**
 * TrendHunter Brasil - Metrify E-commerce Metrics & Financial Dashboard
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

// Seed Metrify default metrics into memory/simulation
$monthlyData = [
    ['month' => 'Janeiro', 'revenue' => 95000, 'cost' => 32000, 'ads' => 11000, 'fee' => 15200, 'tax' => 5700, 'net' => 31100],
    ['month' => 'Fevereiro', 'revenue' => 112000, 'cost' => 38000, 'ads' => 12500, 'fee' => 17920, 'tax' => 6720, 'net' => 36860],
    ['month' => 'Março', 'revenue' => 134000, 'cost' => 45000, 'ads' => 14000, 'fee' => 21440, 'tax' => 8040, 'net' => 45520],
    ['month' => 'Abril', 'revenue' => 128000, 'cost' => 43000, 'ads' => 13800, 'fee' => 20480, 'tax' => 7680, 'net' => 43040],
    ['month' => 'Maio', 'revenue' => 145000, 'cost' => 49000, 'ads' => 16000, 'fee' => 23200, 'tax' => 8700, 'net' => 48100],
    ['month' => 'Junho', 'revenue' => 158000, 'cost' => 53000, 'ads' => 17500, 'fee' => 25280, 'tax' => 9480, 'net' => 52740]
];

$topSKUs = [
    ['sku' => 'PROJ-4K-WIFI', 'title' => 'Mini Projetor Smart 4K Wi-Fi', 'sales' => 340, 'price' => 289.90, 'cost' => 98.00, 'fee' => 46.38, 'shipping' => 12.00, 'ads' => 24.50, 'profit' => 109.02, 'margin' => 37.6],
    ['sku' => 'SUP-TAMPA-KCH', 'title' => 'Suporte Organizador de Tampas Inox', 'sales' => 510, 'price' => 45.00, 'cost' => 12.00, 'fee' => 5.40, 'shipping' => 5.00, 'ads' => 3.50, 'profit' => 19.10, 'margin' => 42.4],
    ['sku' => 'PRT-VENTOSA-SIL', 'title' => 'Prato com Ventosa Silicone BPA Free', 'sales' => 280, 'price' => 39.90, 'cost' => 10.00, 'fee' => 4.79, 'shipping' => 5.00, 'ads' => 4.20, 'profit' => 15.91, 'margin' => 39.9],
    ['sku' => 'GIR-MORD-SENS', 'title' => 'Mordedor Sensorial Girafa Silicone', 'sales' => 420, 'price' => 29.90, 'cost' => 7.50, 'fee' => 3.59, 'shipping' => 5.00, 'ads' => 2.80, 'profit' => 11.01, 'margin' => 36.8],
    ['sku' => 'ORG-TEMPERO-GAV', 'title' => 'Organizador de Temperos de Gaveta', 'sales' => 190, 'price' => 59.90, 'cost' => 15.00, 'fee' => 7.19, 'shipping' => 6.00, 'ads' => 5.80, 'profit' => 25.91, 'margin' => 43.3]
];

$adCampaigns = [
    ['platform' => 'Mercado Livre Product Ads', 'spend' => 6400.00, 'impressions' => 128000, 'clicks' => 4800, 'conversions' => 320, 'cpc' => 1.33, 'ctr' => 3.75, 'revenue' => 26880.00, 'roas' => 4.2, 'acos' => 23.8],
    ['platform' => 'Shopee Ads - Busca & Descoberta', 'spend' => 4800.00, 'impressions' => 185000, 'clicks' => 6200, 'conversions' => 410, 'cpc' => 0.77, 'ctr' => 3.35, 'revenue' => 19680.00, 'roas' => 4.1, 'acos' => 24.4],
    ['platform' => 'Facebook/Instagram Ads (TikTok Shop)', 'spend' => 4000.00, 'impressions' => 95000, 'clicks' => 3100, 'conversions' => 190, 'cpc' => 1.29, 'ctr' => 3.26, 'revenue' => 16400.00, 'roas' => 4.1, 'acos' => 24.3]
];

require __DIR__ . '/templates/header.php';
?>

<!-- Inline CSS to replicate Metrify metrics cockpit -->
<style>
    .metrify-tab-nav {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        gap: 6px;
    }
    .metrify-tab-nav .nav-link {
        color: var(--text-secondary);
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        padding: 12px 22px;
        transition: all 0.2s ease;
        font-size: 13px;
    }
    .metrify-tab-nav .nav-link:hover {
        color: var(--text-primary);
        background-color: rgba(255, 255, 255, 0.02);
    }
    .metrify-tab-nav .nav-link.active {
        color: #00d2ff !important;
        background-color: var(--card-bg) !important;
        border-color: rgba(255, 255, 255, 0.08) rgba(255, 255, 255, 0.08) transparent !important;
        border-top: 2px solid #00d2ff !important;
    }
    .text-metrify-cyan {
        color: #00d2ff;
    }
    .border-metrify-cyan {
        border-color: rgba(0, 210, 255, 0.2) !important;
    }
    .bg-metrify-glow {
        background-color: rgba(0, 210, 255, 0.05);
    }
    .progress-bar-cyan {
        background-color: #00d2ff;
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Block -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-metrify-glow text-metrify-cyan border border-metrify-cyan px-3 py-1">
                    <i class="fa-solid fa-chart-column me-1"></i> METRIFY HUB DE METRICAS
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="fa-solid fa-arrows-spin me-1"></i> Mercado Livre & Shopee Integrados
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Metrify Dashboard e Inteligência Unitária</h1>
            <p class="text-muted small mb-0">Cockpit financeiro avançado para rastreamento de DRE, ROAS de campanhas, CPA, comissão de marketplaces e lucro líquido unitário por SKU.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="alert('Metrify atualizando dados das contas de Mercado Livre e Shopee...'); location.reload();">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Forçar Sincronização API
            </button>
        </div>
    </div>

    <!-- Metrify Top KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card turquoise p-3">
                <div class="metric-title"><i class="fa-solid fa-sack-dollar text-metrify-cyan me-1"></i> Faturamento Total (Bruto)</div>
                <div class="metric-value text-white">R$ 158.000,00</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>+8.9% vs mês anterior</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card p-3" style="border-left: 4px solid #06d6a0;">
                <div class="metric-title" style="color: #55efc4;"><i class="fa-solid fa-receipt me-1"></i> Lucro Líquido Total</div>
                <div class="metric-value text-white">R$ 52.740,00</div>
                <div class="small text-success mt-1">Margem Líquida Real: <strong>33.3%</strong></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card purple p-3">
                <div class="metric-title"><i class="fa-solid fa-rectangle-ad text-accent-purple me-1"></i> Investimento Ads (Total)</div>
                <div class="metric-value text-white">R$ 17.500,00</div>
                <div class="small text-success mt-1">ROAS Geral do Tráfego: <strong>4.1x</strong></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-premium metric-card yellow p-3">
                <div class="metric-title"><i class="fa-solid fa-percent text-warning me-1"></i> Taxa de Reclamação / Devolução</div>
                <div class="metric-value text-white">1.25%</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i>Dentro do limite saudável</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs metrify-tab-nav mb-4" id="metrifyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-panel" type="button" role="tab"><i class="fa-solid fa-table-list me-1"></i> DRE & Cockpit Geral</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="skus-tab" data-bs-toggle="tab" data-bs-target="#skus-panel" type="button" role="tab"><i class="fa-solid fa-tags me-1"></i> Análise e Rentabilidade por SKU</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads-panel" type="button" role="tab"><i class="fa-solid fa-gauge-high me-1"></i> Tráfego Pago & Metas Ads</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sync-tab" data-bs-toggle="tab" data-bs-target="#sync-panel" type="button" role="tab"><i class="fa-solid fa-network-wired me-1"></i> Integrador de Contas</button>
        </li>
    </ul>

    <!-- Tab Panels Content -->
    <div class="tab-content" id="metrifyTabsContent">
        
        <!-- Tab 1: DRE & Cockpit Geral -->
        <div class="tab-pane fade show active" id="overview-panel" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                <!-- DRE Graph -->
                <div class="col-12 col-lg-8">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-chart-line text-metrify-cyan me-2"></i> Evolução de Faturamento vs Lucro Líquido</h5>
                            <span class="text-muted small">Últimos 6 meses</span>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="dreChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Simulado DRE Month Selector -->
                <div class="col-12 col-lg-4">
                    <div class="card-premium p-4 h-100">
                        <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-file-invoice-dollar text-warning me-2"></i> Demonstrativo DRE Simplificado</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Mês de Referência</label>
                            <select class="form-select bg-dark text-white border-light-subtle" id="dreMonthSelector" onchange="updateDREMetrics()">
                                <option value="5">Junho (Mês Atual)</option>
                                <option value="4">Maio</option>
                                <option value="3">Abril</option>
                                <option value="2">Março</option>
                                <option value="1">Fevereiro</option>
                                <option value="0">Janeiro</option>
                            </select>
                        </div>

                        <div class="d-flex flex-column gap-2 text-white" style="font-size: 13px;">
                            <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-1">
                                <span class="text-muted">Faturamento Bruto:</span>
                                <strong id="dre-val-revenue">R$ 158.000,00</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-1">
                                <span class="text-muted">(-) Custo do Produto (COGS):</span>
                                <span id="dre-val-cost" class="text-danger">-R$ 53.000,00</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-1">
                                <span class="text-muted">(-) Comissão Mkt & Envios:</span>
                                <span id="dre-val-fee" class="text-danger">-R$ 25.280,00</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-1">
                                <span class="text-muted">(-) Tráfego Pago (Ads):</span>
                                <span id="dre-val-ads" class="text-danger">-R$ 17.500,00</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom border-light-subtle border-opacity-10 py-1">
                                <span class="text-muted">(-) Imposto e Taxas:</span>
                                <span id="dre-val-tax" class="text-danger">-R$ 9.480,00</span>
                            </div>
                            <div class="d-flex justify-content-between bg-metrify-glow border border-metrify-cyan p-2 rounded mt-2">
                                <span class="text-metrify-cyan fw-bold">(=) Lucro Líquido Real:</span>
                                <strong id="dre-val-net" class="text-success">R$ 52.740,00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: SKU Profitability Analyzer -->
        <div class="tab-pane fade" id="skus-panel" role="tabpanel" aria-labelledby="skus-tab">
            <div class="card-premium p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-tags text-metrify-cyan me-2"></i> Performance Unitária por SKU</h5>
                    <small class="text-muted">Analise custos, taxas, publicidade e margens líquidas detalhadas por produto</small>
                </div>
                
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Produto</th>
                                <th>Preço Venda</th>
                                <th>Custo Prod</th>
                                <th>Tarifas Mkt</th>
                                <th>Ads / CPA</th>
                                <th>Lucro Unit.</th>
                                <th>Margem Líq.</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topSKUs as $sku): ?>
                                <tr>
                                    <td class="font-monospace text-metrify-cyan fw-bold"><?php echo htmlspecialchars($sku['sku']); ?></td>
                                    <td class="fw-semibold text-white"><?php echo htmlspecialchars($sku['title']); ?></td>
                                    <td>R$ <?php echo number_format($sku['price'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($sku['cost'], 2, ',', '.'); ?></td>
                                    <td class="text-danger">-R$ <?php echo number_format($sku['fee'] + $sku['shipping'], 2, ',', '.'); ?></td>
                                    <td class="text-danger">-R$ <?php echo number_format($sku['ads'], 2, ',', '.'); ?></td>
                                    <td class="text-success fw-bold">R$ <?php echo number_format($sku['profit'], 2, ',', '.'); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-white me-2"><?php echo $sku['margin']; ?>%</span>
                                            <div class="progress w-100" style="height: 4px; background-color: rgba(255,255,255,0.05);">
                                                <div class="progress-bar progress-bar-cyan" role="progressbar" style="width: <?php echo $sku['margin']; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="openCalculatorWithProduct('<?php echo htmlspecialchars($sku['title']); ?>', <?php echo $sku['price']; ?>, <?php echo $sku['cost']; ?>)">
                                            <i class="fa-solid fa-calculator me-1"></i> Simular
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 3: Ads & Paid Traffic Performance -->
        <div class="tab-pane fade" id="ads-panel" role="tabpanel" aria-labelledby="ads-tab">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-rectangle-ad text-metrify-cyan me-2"></i> Performance de Campanhas e CPA</h5>
                
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Canal de Tráfego</th>
                                <th>Investido (Ads)</th>
                                <th>Visualizações</th>
                                <th>Cliques (CTR)</th>
                                <th>Conversões</th>
                                <th>CPC Médio</th>
                                <th>Faturamento Gerado</th>
                                <th>ROAS Real</th>
                                <th>ACOS %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adCampaigns as $c): ?>
                                <tr>
                                    <td class="fw-bold text-white"><i class="fa-solid fa-bullseye text-accent-purple me-1"></i> <?php echo htmlspecialchars($c['platform']); ?></td>
                                    <td class="text-danger fw-bold">R$ <?php echo number_format($c['spend'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($c['impressions'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($c['clicks'], 0, ',', '.'); ?> <small class="text-muted">(<?php echo $c['ctr']; ?>%)</small></td>
                                    <td><?php echo $c['conversions']; ?> vendas</td>
                                    <td>R$ <?php echo number_format($c['cpc'], 2, ',', '.'); ?></td>
                                    <td class="text-success fw-bold">R$ <?php echo number_format($c['revenue'], 2, ',', '.'); ?></td>
                                    <td class="text-warning fw-bold"><?php echo $c['roas']; ?>x</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?php echo $c['acos']; ?>% ACOS</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 4: Account Integrator -->
        <div class="tab-pane fade" id="sync-panel" role="tabpanel" aria-labelledby="sync-tab">
            <div class="row g-4">
                <!-- Mercado Livre Integration -->
                <div class="col-12 col-md-6">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: bold; font-size: 20px;">
                                    ML
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-white">Mercado Livre API</h5>
                                    <span class="text-muted small">Sincronização de vendas, tarifas e Product Ads</span>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Conectado</span>
                        </div>
                        <div class="mb-3 text-muted small">
                            Sua conta está integrada de forma segura. A última sincronização de dados financeiros e anúncios foi concluída há <strong>18 minutos</strong>.
                        </div>
                        <button class="btn btn-outline-warning w-100" onclick="alert('ML API: Sincronização manual completada!');">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Forçar Sincronização ML
                        </button>
                    </div>
                </div>

                <!-- Shopee Integration -->
                <div class="col-12 col-md-6">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: bold; font-size: 20px;">
                                    SH
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-white">Shopee API Integrada</h5>
                                    <span class="text-muted small">Sincronização de pedidos, fretes e Shopee Ads</span>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Conectado</span>
                        </div>
                        <div class="mb-3 text-muted small">
                            Integração oficial ativa. Todas as taxas de comissão e cupons aplicados a nível de SKU estão sendo baixados em tempo real.
                        </div>
                        <button class="btn btn-outline-danger w-100" onclick="alert('Shopee API: Sincronização de vendas e cupons concluída!');">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Forçar Sincronização Shopee
                        </button>
                    </div>
                </div>

                <!-- Amazon Integration (Unconnected) -->
                <div class="col-12 col-md-6">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-warning border border-warning rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: bold; font-size: 16px;">
                                    AMZ
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-white">Amazon Seller Central</h5>
                                    <span class="text-muted small">Sincronização FBA e custos logísticos</span>
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-muted border border-secondary px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Desconectado</span>
                        </div>
                        <div class="mb-3 text-muted small">
                            A sincronização com a Amazon está inativa. Conecte sua conta Seller Central para rastrear tarifas de envio FBA e cupons promocionais.
                        </div>
                        <button class="btn btn-outline-light w-100 border-light-subtle" onclick="alert('Amazon API: Redirecionando para autenticação OAuth Seller Central...');">
                            <i class="fa-solid fa-link me-1"></i> Conectar Amazon API
                        </button>
                    </div>
                </div>

                <!-- Custom API webhook Integration -->
                <div class="col-12 col-md-6">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: bold; font-size: 20px;">
                                    <i class="fa-solid fa-gears" style="font-size: 18px;"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-white">Integração ERP / Bling / Tiny</h5>
                                    <span class="text-muted small">Sincronização de Notas Fiscais e Emissão</span>
                                </div>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Configurar</span>
                        </div>
                        <div class="mb-3 text-muted small">
                            Integre seu ERP Bling ou Tiny via Webhook e chave API para puxar custos reais dos produtos (COGS) baseados nas notas fiscais de entrada.
                        </div>
                        <button class="btn btn-outline-primary w-100 border-light-subtle" onclick="alert('ERP Configs: Chave API e Webhooks salvos com sucesso.');">
                            <i class="fa-solid fa-key me-1"></i> Configurar Chave ERP Bling
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart JS Initialization Script -->
<script>
let dreData = <?php echo json_encode($monthlyData); ?>;

function updateDREMetrics() {
    const idx = parseInt(document.getElementById('dreMonthSelector').value);
    const data = dreData[idx];

    document.getElementById('dre-val-revenue').innerText = 'R$ ' + data.revenue.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('dre-val-cost').innerText = '-R$ ' + data.cost.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('dre-val-fee').innerText = '-R$ ' + data.fee.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('dre-val-ads').innerText = '-R$ ' + data.ads.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('dre-val-tax').innerText = '-R$ ' + data.tax.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('dre-val-net').innerText = 'R$ ' + data.net.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

$(document).ready(function() {
    // Render Chart.js
    const ctx = document.getElementById('dreChart').getContext('2d');
    
    const labels = dreData.map(d => d.month);
    const revenues = dreData.map(d => d.revenue);
    const nets = dreData.map(d => d.net);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Faturamento Bruto (R$)',
                    data: revenues,
                    borderColor: '#00d2ff',
                    backgroundColor: 'rgba(0, 210, 255, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Lucro Líquido Real (R$)',
                    data: nets,
                    borderColor: '#06d6a0',
                    backgroundColor: 'rgba(6, 214, 160, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#a4b0be',
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#a4b0be',
                        font: {
                            family: 'Inter'
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#a4b0be',
                        font: {
                            family: 'Inter'
                        },
                        callback: function(value) {
                            return 'R$ ' + value / 1000 + 'k';
                        }
                    }
                }
            }
        }
    });

    // Run initial trigger
    updateDREMetrics();
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
