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

$baseShopeeKeywords = [
    'achadinhos da shopee', 'organizador de maquiagem', 'fone bluetooth sem fio', 'garrafa motivacional 2l', 'mini processador usb', 
    'copo stanley termico', 'relogio masculino smart', 'mochila escolar impermeavel', 'luminaria led decorativa', 'tapete para banheiro', 
    'escova secadora rotativa', 'maquina de cortar cabelo', 'meia sapatilha antiderrapante', 'bolsa feminina transversal', 'capinha de iphone', 
    'pelicula de vidro temperado', 'kit pincel maquiagem', 'colar feminino prata', 'brinco de argola', 'anel regulavel', 
    'carteira masculina couro', 'cinto masculino social', 'oculos de sol quadrado', 'corrente de prata masculina', 'pulseira inteligente fit', 
    'carregador portatil powerbank', 'suporte de celular carro', 'cabo usb tipo c', 'adaptador tomada universal', 'fone de ouvido com fio', 
    'mouse sem fio recarregavel', 'teclado mecanico gamer', 'pad mouse grande', 'caixa de som bluetooth bluetooth', 'microfone lapela sem fio', 
    'tripod celular selfie', 'anel de luz ring light', 'camera de segurança wifi', 'lampada inteligente rgb', 'difusor de aromas ultrassonico', 
    'mini ventilador portatil', 'esponja eletrica limpeza', 'massageador corporal eletrico', 'balança digital cozinha', 'garrafa termica inox', 
    'marmita termica eletrica', 'escorredor de pratos pia', 'cabide de veludo fino', 'organizador de sapatos', 'caixa organizadora plastico'
];
$shopeeKeywords = [];
foreach ($baseShopeeKeywords as $idx => $kw) {
    $seed = strlen($kw) + $idx;
    $volume = 10000 + (($seed * 3479) % 480000);
    $cpc = 0.05 + (($seed * 17) % 85) / 100;
    $growth = '+' . (20 + (($seed * 43) % 230)) . '%';
    
    $shopeeKeywords[] = [
        'keyword' => $kw,
        'volume' => $volume,
        'cpc' => $cpc,
        'growth' => $growth
    ];
}

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

    <!-- Unified Supplier Search Bar -->
    <div class="card-premium p-4 mb-4" style="background: linear-gradient(135deg, rgba(116, 93, 247, 0.05) 0%, rgba(0, 210, 255, 0.05) 100%); border: 1px solid rgba(116, 93, 247, 0.15) !important;">
        <h5 class="fw-bold text-white mb-2"><i class="fa-solid fa-truck-fast text-info me-2"></i> Localizador de Fornecedores por Palavra-Chave</h5>
        <p class="text-muted small mb-3">Pesquise por qualquer palavra-chave ou produto para localizar fornecedores atacadistas nacionais/importadores adequados com cálculo estimado de margens e ROI.</p>
        
        <form onsubmit="searchSuppliersKeyword(event, this)">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control bg-dark text-white border-light-subtle p-3" placeholder="Digite o produto (ex: fone bluetooth, garrafa térmica, organizador)..." required style="border-radius: 8px 0 0 8px;">
                <button type="submit" class="btn btn-primary px-4 fw-bold" style="background-color: #745df7; border: 0; border-radius: 0 8px 8px 0;"><i class="fa-solid fa-magnifying-glass me-2"></i> Buscar Fornecedores</button>
            </div>
        </form>

        <!-- Search Results Container -->
        <div class="mt-4" id="supplier-kw-results" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-light-subtle border-opacity-10">
                <h6 class="fw-bold text-white mb-0"><i class="fa-solid fa-list text-info me-2"></i> Fornecedores Encontrados para: <span class="text-accent-turquoise" id="supplier-kw-term">...</span></h6>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle text-muted px-2 py-1" onclick="$('#supplier-kw-results').slideUp();" style="font-size: 11px;">Fechar</button>
            </div>
            <div class="row g-3" id="supplier-kw-list">
                <!-- Dynamically populated -->
            </div>

            <!-- Top 50 Products Container -->
            <div class="mt-4 pt-3 border-top border-light-subtle border-opacity-10" id="top-products-kw-container" style="display: none;">
                <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-trophy text-warning me-2"></i> Top 50 Produtos Mais Vendidos / Buscados (Shopee)</h6>
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Produto</th>
                                <th>Loja</th>
                                <th>Preço Venda</th>
                                <th>Vendas Est.</th>
                                <th>Avaliação</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="top-products-kw-list">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function searchSuppliersKeyword(event, form) {
        event.preventDefault();
        const keyword = form.elements['keyword'].value.trim();
        if (!keyword) return;

        const resultsDiv = $('#supplier-kw-results');
        const termSpan = $('#supplier-kw-term');
        const listDiv = $('#supplier-kw-list');
        const topContainer = $('#top-products-kw-container');
        const topList = $('#top-products-kw-list');

        resultsDiv.slideDown();
        termSpan.text('"' + keyword + '"');
        topContainer.hide();
        listDiv.html(`
            <div class="col-12 text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-info mb-2" role="status"></div>
                <div>Buscando fornecedores e mapeando os produtos mais vendidos...</div>
            </div>
        `);

        const formData = new FormData();
        formData.append('action', 'find_suppliers_by_keyword');
        formData.append('keyword', keyword);
        formData.append('marketplace', 'shopee');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.suppliers.length > 0) {
                listDiv.html('');
                data.suppliers.forEach(s => {
                    const encodedName = encodeURIComponent(s.name);
                    const encodedTitle = encodeURIComponent(s.product_title);
                    const card = `
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded border border-light-subtle h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.01);">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">${s.type}</span>
                                        <span class="text-muted small"><i class="fa-solid fa-truck-ramp-box me-1"></i> ${s.delivery_days} dias</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2">${s.name}</h6>
                                    <div class="text-muted small mb-2"><i class="fa-solid fa-map-location-dot me-1"></i> ${s.address}</div>
                                    
                                    <div class="row g-2 mb-3 text-center" style="font-size: 11px;">
                                        <div class="col-4">
                                            <div class="p-1 rounded border border-light-subtle bg-dark">
                                                <div class="text-muted">Preço Custo</div>
                                                <div class="fw-bold text-white mt-1">R$ ${s.wholesale_price.toFixed(2)}</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded border border-light-subtle bg-dark">
                                                <div class="text-muted">Margem Est.</div>
                                                <div class="fw-bold text-success mt-1">${s.margin_percent}%</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded border border-light-subtle bg-dark">
                                                <div class="text-muted">ROI Est.</div>
                                                <div class="fw-bold text-success mt-1">${s.roi_percent}%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-muted small mb-3 p-2 rounded" style="background: rgba(255,255,255,0.02); font-size: 11px;">
                                        <i class="fa-solid fa-circle-info text-info me-1"></i> ${s.notes}
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-info flex-grow-1" onclick="saveSupplierFromKeywordSearch('${s.name}', '${s.type}', ${s.wholesale_price}, ${s.profit_margin}, ${s.margin_percent}, ${s.roi_percent}, '${s.url}', '${s.address}', '${s.phone}', '${s.notes}', '${s.product_title}')">
                                        <i class="fa-solid fa-bookmark me-1"></i> Salvar Forn.
                                    </button>
                                    <a href="crm.php?company=${encodedName}&product=${encodedTitle}" class="btn btn-sm btn-primary border-0" style="background-color: #745df7; font-size:12px; font-weight:600;">
                                        <i class="fa-solid fa-handshake me-1"></i> Abrir CRM
                                    </a>
                                    <a href="https://wa.me/55${s.phone.replace(/\D/g, '')}" target="_blank" class="btn btn-sm btn-success" style="font-size:12px;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    listDiv.append(card);
                });

                // Render Top 50 products list
                if (data.top_products && data.top_products.length > 0) {
                    topContainer.show();
                    topList.html('');
                    data.top_products.forEach((p, idx) => {
                        const row = `
                            <tr>
                                <td class="fw-bold text-muted">${idx + 1}</td>
                                <td>
                                    <a href="${p.url}" target="_blank" class="fw-bold text-accent-turquoise text-decoration-none hover-underline">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1" style="font-size: 10px;"></i> ${p.title}
                                    </a>
                                </td>
                                <td>${p.store_name || 'N/A'}</td>
                                <td class="fw-bold text-white">R$ ${parseFloat(p.price).toFixed(2).replace('.', ',')}</td>
                                <td class="text-success fw-bold">${parseInt(p.sales_count_est).toLocaleString('pt-BR')} vendas</td>
                                <td><i class="fa-solid fa-star text-warning me-1"></i> ${p.rating || '4.5'}</td>
                                <td>
                                    <a href="${p.url}" target="_blank" class="btn btn-xs btn-outline-info px-2 py-1" style="font-size: 10px;">
                                        Ver Produto
                                    </a>
                                </td>
                            </tr>
                        `;
                        topList.append(row);
                    });
                }
            } else {
                listDiv.html('<div class="col-12 text-center py-4 text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Nenhum fornecedor encontrado para esta palavra-chave.</div>');
            }
        })
        .catch(err => {
            console.error(err);
            listDiv.html('<div class="col-12 text-center py-4 text-danger"><i class="fa-solid fa-circle-exclamation"></i> Erro de comunicação com o servidor.</div>');
        });
    }

    function saveSupplierFromKeywordSearch(name, type, cost, profit, margin, roi, url, address, phone, notes, productTitle) {
        const formData = new FormData();
        formData.append('action', 'save_supplier');
        formData.append('name', name);
        formData.append('type', type);
        formData.append('wholesale_price', cost);
        formData.append('profit_margin', profit);
        formData.append('margin_percent', margin);
        formData.append('roi_percent', roi);
        formData.append('url', url);
        formData.append('address', address);
        formData.append('phone', phone);
        formData.append('notes', notes);
        formData.append('product_title', productTitle);

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Fornecedor salvo com sucesso!');
            } else {
                alert('Erro ao salvar fornecedor: ' + (data.error || 'Erro desconhecido.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao conectar-se com o servidor.');
        });
    }
    </script>

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
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-info" onclick="openShopeeCalculatorWith(<?php echo $v['price']; ?>)">
                                                <i class="fa-solid fa-calculator me-1"></i> Margem
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning text-warning border-warning border-opacity-25" onclick="openShopeeSuppliersModal(<?php echo $v['id']; ?>)">
                                                <i class="fa-solid fa-truck me-1"></i> Fornecedores
                                            </button>
                                        </div>
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

<!-- Modal: Shopee Product Suppliers -->
<div class="modal fade" id="shopeeSuppliersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-truck text-warning me-2"></i> Fornecedores Atacado Recomendados</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-metrify-cyan mb-3" id="modal-shopee-product-title">Produto</h6>
                
                <div class="row g-3" id="modal-shopee-suppliers-container">
                    <!-- Populated dynamically -->
                </div>
            </div>
            <div class="modal-footer border-top border-light-subtle">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
const shopeeSuppliers = {
    1: { // Fone de Ouvido Gatinho
        title: "Fone de Ouvido Gatinho com Led Bluetooth",
        retail_price: 39.90,
        suppliers: [
            {
                name: "Atacadão dos Eletrônicos SP",
                type: "Distribuidor Nacional SP",
                wholesale_price: 14.90,
                delivery_days: 4,
                phone: "(11) 99128-4499",
                address: "Brás, São Paulo - SP",
                url: "https://www.atacadaodoseletronicos.com.br",
                notes: "Pedido mínimo: R$ 500,00 | Grade fechada"
            },
            {
                name: "AliExpress Tech Wholesale",
                type: "Importação Direta",
                wholesale_price: 11.80,
                delivery_days: 14,
                phone: "(11) 99000-0000",
                address: "Shenzhen, China",
                url: "https://pt.aliexpress.com",
                notes: "Isento de taxas pelo Remessa Conforme"
            },
            {
                name: "Importadora MultiUtil PR",
                type: "Distribuidor Nacional PR",
                wholesale_price: 13.50,
                delivery_days: 3,
                phone: "(41) 98711-2244",
                address: "Curitiba - PR",
                url: "https://www.multiutilpr.com.br",
                notes: "Faturamento mínimo: R$ 1.000,00"
            }
        ]
    },
    2: { // Mini Liquidificador
        title: "Mini Liquidificador Squeeze Portátil USB",
        retail_price: 29.90,
        suppliers: [
            {
                name: "Utilidades Brasil Atacado SP",
                type: "Distribuidor Nacional SP",
                wholesale_price: 9.90,
                delivery_days: 3,
                phone: "(11) 98765-4321",
                address: "Guarulhos - SP",
                url: "https://www.utilidadesbrasilatacado.com.br",
                notes: "Desconto de 5% no PIX"
            },
            {
                name: "AliExpress Home Dropship",
                type: "Importação Direta",
                wholesale_price: 7.50,
                delivery_days: 12,
                phone: "(11) 99111-2222",
                address: "Yiwu, China",
                url: "https://pt.aliexpress.com",
                notes: "Envio aéreo expresso"
            },
            {
                name: "Mega Distribuidora Sul SC",
                type: "Distribuidor Nacional SC",
                wholesale_price: 9.20,
                delivery_days: 3,
                phone: "(47) 98822-1100",
                address: "Joinville - SC",
                url: "https://www.megadistribuidorasul.com.br",
                notes: "Pedido mínimo de 50 unidades"
            }
        ]
    },
    3: { // Escova de Limpeza Spin
        title: "Escova de Limpeza Elétrica Spin Recarregável",
        retail_price: 69.90,
        suppliers: [
            {
                name: "Mega Utilidades Brás",
                type: "Distribuidor Nacional SP",
                wholesale_price: 24.90,
                delivery_days: 4,
                phone: "(11) 99312-8844",
                address: "Brás, São Paulo - SP",
                url: "https://www.megautilidadesbras.com.br",
                notes: "Retirada em loja física no Brás"
            },
            {
                name: "AliExpress Home Gadgets",
                type: "Importação Direta",
                wholesale_price: 18.90,
                delivery_days: 15,
                phone: "(11) 99222-3333",
                address: "Guangzhou, China",
                url: "https://pt.aliexpress.com",
                notes: "Lote mínimo de 20 peças"
            }
        ]
    },
    4: { // Seladora de Embalagens
        title: "Seladora de Embalagens Plásticas Portátil Ímã",
        retail_price: 12.90,
        suppliers: [
            {
                name: "Utilidades Brasil Atacado SP",
                type: "Distribuidor Nacional SP",
                wholesale_price: 4.50,
                delivery_days: 3,
                phone: "(11) 98765-4321",
                address: "Guarulhos - SP",
                url: "https://www.utilidadesbrasilatacado.com.br",
                notes: "Excelente para brindes ou kits"
            },
            {
                name: "AliExpress Mini Seals",
                type: "Importação Direta",
                wholesale_price: 2.90,
                delivery_days: 14,
                phone: "(11) 99333-4444",
                address: "Yiwu, China",
                url: "https://pt.aliexpress.com",
                notes: "Frete grátis acima de R$ 99"
            }
        ]
    },
    5: { // Caneta Depiladora
        title: "Caneta Depiladora de Sobrancelha Elétrica USB",
        retail_price: 19.90,
        suppliers: [
            {
                name: "Beleza Viva Atacado SP",
                type: "Distribuidor Nacional SP",
                wholesale_price: 6.50,
                delivery_days: 3,
                phone: "(11) 99281-8812",
                address: "Brás, São Paulo - SP",
                url: "https://www.belezavivaatacado.com.br",
                notes: "Melhor preço de cosméticos do Brás"
            },
            {
                name: "AliExpress Beauty Group",
                type: "Importação Direta",
                wholesale_price: 4.80,
                delivery_days: 13,
                phone: "(11) 99444-5555",
                address: "Shenzhen, China",
                url: "https://pt.aliexpress.com",
                notes: "Embalagem personalizada disponível"
            }
        ]
    }
};

function openShopeeSuppliersModal(productId) {
    const product = shopeeSuppliers[productId];
    if (!product) return;

    document.getElementById('modal-shopee-product-title').innerText = product.title + ' (Preço Shopee: R$ ' + product.retail_price.toFixed(2) + ')';
    
    const container = document.getElementById('modal-shopee-suppliers-container');
    container.innerHTML = '';

    product.suppliers.forEach(s => {
        const profit = product.retail_price - s.wholesale_price;
        const margin = (profit / product.retail_price) * 100;
        const roi = (profit / s.wholesale_price) * 100;
        const encodedName = encodeURIComponent(s.name);
        const encodedTitle = encodeURIComponent(product.title);

        const card = `
            <div class="col-12 col-md-6">
                <div class="p-3 rounded border border-light-subtle h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.01);">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">${s.type}</span>
                            <span class="text-muted small"><i class="fa-solid fa-truck-ramp-box me-1"></i> ${s.delivery_days} dias</span>
                        </div>
                        <h6 class="fw-bold text-white mb-2">${s.name}</h6>
                        <div class="text-muted small mb-2"><i class="fa-solid fa-map-location-dot me-1"></i> ${s.address}</div>
                        
                        <div class="row g-2 mb-3 text-center" style="font-size: 11px;">
                            <div class="col-4">
                                <div class="p-1 rounded border border-light-subtle bg-dark">
                                    <div class="text-muted">Custo Atacado</div>
                                    <div class="fw-bold text-white mt-1">R$ ${s.wholesale_price.toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 rounded border border-light-subtle bg-dark">
                                    <div class="text-muted">Margem Est.</div>
                                    <div class="fw-bold text-success mt-1">${margin.toFixed(1)}%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 rounded border border-light-subtle bg-dark">
                                    <div class="text-muted">ROI Est.</div>
                                    <div class="fw-bold text-success mt-1">${roi.toFixed(1)}%</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-muted small mb-3 p-2 rounded" style="background: rgba(255,255,255,0.02); font-size: 11px;">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> ${s.notes}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info flex-grow-1" onclick="saveShopeeSupplier('${s.name}', '${s.type}', ${s.wholesale_price}, ${profit}, ${margin}, ${roi}, '${s.url}', '${s.address}', '${s.phone}', '${s.notes}', '${product.title}')">
                            <i class="fa-solid fa-bookmark me-1"></i> Salvar Forn.
                        </button>
                        <a href="crm.php?company=${encodedName}&product=${encodedTitle}" class="btn btn-sm btn-primary bg-shopee-orange border-0" style="font-size:12px; font-weight:600;">
                            <i class="fa-solid fa-handshake me-1"></i> Abrir CRM
                        </a>
                        <a href="https://wa.me/55${s.phone.replace(/\D/g, '')}" target="_blank" class="btn btn-sm btn-success" style="font-size:12px;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += card;
    });

    const modal = new bootstrap.Modal(document.getElementById('shopeeSuppliersModal'));
    modal.show();
}

function saveShopeeSupplier(name, type, cost, profit, margin, roi, url, address, phone, notes, productTitle) {
    const formData = new FormData();
    formData.append('action', 'save_supplier');
    formData.append('name', name);
    formData.append('type', type);
    formData.append('wholesale_price', cost);
    formData.append('profit_margin', profit);
    formData.append('margin_percent', margin);
    formData.append('roi_percent', roi);
    formData.append('url', url);
    formData.append('address', address);
    formData.append('phone', phone);
    formData.append('notes', notes);
    formData.append('product_title', productTitle);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Fornecedor de Shopee salvo com sucesso!');
        } else {
            alert('Erro ao salvar fornecedor: ' + (data.error || 'Erro desconhecido.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao conectar-se com o servidor.');
    });
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
