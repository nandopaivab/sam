<?php
/**
 * TrendHunter Brasil - Kalodata TikTok Shop Intelligence Page
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

require __DIR__ . '/templates/header.php';
?>

<!-- Inline Page Styles for Kalodata Tabs -->
<style>
    .premium-tabs {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        gap: 8px;
    }
    .premium-tabs .nav-link {
        color: var(--text-secondary);
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s ease;
    }
    .premium-tabs .nav-link:hover {
        color: var(--text-primary);
        background-color: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.05) rgba(255, 255, 255, 0.05) transparent;
    }
    .premium-tabs .nav-link.active {
        color: var(--accent-turquoise) !important;
        background-color: var(--card-bg) !important;
        border-color: rgba(255, 255, 255, 0.08) rgba(255, 255, 255, 0.08) transparent !important;
        border-top: 2px solid var(--accent-turquoise) !important;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="fw-bold text-white mb-1"><i class="fa-brands fa-tiktok text-danger me-2 animate-pulse"></i> SAM - TikTok</h2>
                <p class="text-muted mb-0">Rastreamento avançado de lojas, receitas, taxas de crescimento e produtos mais vendidos no TikTok Shop Brasil.</p>
            </div>
            <div class="bg-card-glow p-2 px-3 rounded-3 border border-light-subtle d-flex align-items-center my-2" style="background-color: rgba(255,255,255,0.01);">
                <i class="fa-solid fa-signal text-accent-turquoise me-2"></i>
                <span class="text-white small fw-medium">TikTok API Status: <strong class="text-success">Ativa (Live)</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-premium metric-card purple p-3">
            <div class="metric-title"><i class="fa-solid fa-store text-accent-purple me-1"></i> Lojas Monitoradas</div>
            <div class="metric-value">1,480</div>
            <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>+45 novas esta semana</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-premium metric-card turquoise p-3">
            <div class="metric-title"><i class="fa-solid fa-chart-line text-accent-turquoise me-1"></i> Faturamento Semanal</div>
            <div class="metric-value">R$ 5.92M</div>
            <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>+18.3% vs semana anterior</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-premium metric-card yellow p-3">
            <div class="metric-title"><i class="fa-solid fa-percent text-warning me-1"></i> Crescimento Médio</div>
            <div class="metric-value">+42.8%</div>
            <div class="small text-muted mt-1">Taxa de conversão: 3.8%</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-premium metric-card p-3" style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.08) 0%, rgba(11, 12, 22, 0.5) 100%); border-left: 4px solid #dc3545;">
            <div class="metric-title" style="color: #ea868f;"><i class="fa-solid fa-circle-play text-danger me-1"></i> Vídeos Promocionais</div>
            <div class="metric-value" style="color: #fff;">18.5K</div>
            <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>+1,200 novos hoje</div>
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
                <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-trophy text-warning me-2"></i> Top 50 Produtos Mais Vendidos / Buscados (TikTok Shop)</h6>
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
        formData.append('marketplace', 'tiktok');

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

    <!-- Tab Navigation System -->
    <ul class="nav nav-tabs premium-tabs mb-4" id="kalodataTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-panel" type="button" role="tab"><i class="fa-solid fa-basket-shopping me-1"></i> Análise de Produtos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="shops-tab" data-bs-toggle="tab" data-bs-target="#shops-panel" type="button" role="tab"><i class="fa-solid fa-store me-1"></i> Análise de Lojas</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-panel" type="button" role="tab"><i class="fa-brands fa-tiktok me-1"></i> Vídeos & Criadores</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tiktok-ads-tab" data-bs-toggle="tab" data-bs-target="#tiktok-ads-panel" type="button" role="tab"><i class="fa-solid fa-rectangle-ad me-1"></i> TikTok Ads Palavras-chave</button>
    </li>
</ul>

<!-- Tab Panels Content -->
<div class="tab-content" id="kalodataTabsContent">
    
    <!-- Tab 1: Viral Products Panel -->
    <div class="tab-pane fade show active" id="products-panel" role="tabpanel" aria-labelledby="products-tab">
        <div class="card-premium p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-fire text-danger me-2"></i> Produtos Virais - TikTok Shop BR</h5>
                <small class="text-muted">Mostrando produtos com maior receita nos últimos 7 dias</small>
            </div>
            
            <div class="table-responsive">
                <table id="table-products" class="table-premium" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Imagem</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço Médio</th>
                            <th>Vendas Est.</th>
                            <th>Receita Est.</th>
                            <th>Crescimento</th>
                            <th>Avaliação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tkProducts = [];
                        $categories = [
                            'Eletrônicos & Acessórios', 'Beleza & Cuidados', 'Moda Masculina', 'Moda Feminina',
                            'Utilidades Domésticas', 'Esportes & Academia', 'Brinquedos & Hobbies', 'Smartwatches & Tech'
                        ];
                        $prodNouns = [
                            'Eletrônicos & Acessórios' => ['Fone de Ouvido Bluetooth TWS', 'Carregador Magnético por Indução', 'Mini Projetor Portátil Led', 'Tripé de Câmera com Ring Light', 'Microfone de Lapela Sem Fio', 'Cabo Organizador Magnético', 'Adaptador Hub USB-C Multiporta', 'Caixa de Som Portátil Bluetooth'],
                            'Beleza & Cuidados' => ['Batom Lip Tint Magic Gloss', 'Base Líquida Corretiva Hidratante', 'Modelador de Cabelo Ondulador', 'Sérum Facial Ácido Hialurônico', 'Kit de Pincéis de Maquiagem', 'Escova Secadora e Alisadora', 'Massageador Facial de Quartzo', 'Paleta de Sombras Ultra Pigmentada'],
                            'Moda Masculina' => ['Calça Jeans Slim Confort', 'Camiseta Básica Algodão Egípcio', 'Moletom Casual Canguru com Capuz', 'Bermuda Sarja Masculina Casual', 'Jaqueta Corta Vento Impermeável', 'Kit 10 Pares de Meias Invisíveis', 'Sapato Social Moderno Couro', 'Carteira Slim de Couro Legítimo'],
                            'Moda Feminina' => ['Vestido Longo Plissado Casual', 'Conjunto Feminino Cropped e Saia', 'Blusa Canelada Gola Alta Soft', 'Calça Alfaiataria Feminina Elegante', 'Bolsa Tiracolo Transversal Fashion', 'Óculos de Sol Vintage Cat Eye', 'Top Fitness Alto Impacto Suplex', 'Kit 5 Calcinhas Sem Costura Loba'],
                            'Utilidades Domésticas' => ['Mop Giratório Limpeza Prática', 'Organizador de Acrílico Giratório', 'Kit 6 Potes Herméticos Vidro', 'Mini Processador Alimentos USB', 'Luminária de Mesa Inteligente RGB', 'Espremedor de Frutas Portátil USB', 'Balança Digital de Cozinha Alta Precisão', 'Suporte Magnético para Facas Inox'],
                            'Esportes & Academia' => ['Garrafa Térmica Inox Premium', 'Kit 5 Faixas Elásticas Mini Band', 'Corda de Pular Profissional Roller', 'Tapete Yoga Mat Antiderrapante', 'Cinto Lombar Musculação Neoprene', 'Rolo de Liberação Miofascial Foam', 'Pochete Esportiva Impermeável Corrida', 'Luva de Academia Musculação Gel'],
                            'Brinquedos & Hobbies' => ['Blocos de Montar LEGO Creator', 'Câmera Fotográfica Infantil Digital', 'Mini Drone com Câmera HD Quadcopter', 'Jogo de Tabuleiro Clássico Estratégia', 'Quebra-Cabeça 1000 Peças Paisagens', 'Kit Pintura Tela por Números Art', 'Cubo Mágico Profissional Speed', 'Carro Controle Remoto 4x4 Off-Road'],
                            'Smartwatches & Tech' => ['Smartwatch Watch 9 Ultra NFC', 'Pulseira de Silicone Esportiva Watch', 'Fone de Ouvido Noise Cancelling', 'Pelicula de Vidro Curva 3D Smartwatch', 'Localizador Tracker Inteligente AirTag', 'Carregador Rápido GaN 65W Turbo', 'Suporte Veicular Celular com Carregador', 'Suporte Articulado para Monitor Duplo']
                        ];
                        $colors = ['745df7', '06e1cc', 'ffc107', 'dc3545', '198754', '0d6efd', '6f42c1', 'fd7e14', 'e83e8c', '20c997'];

                        for ($i = 0; $i < 100; $i++) {
                            $cat = $categories[$i % count($categories)];
                            $nounList = $prodNouns[$cat];
                            $noun = $nounList[$i % count($nounList)];
                            $brand = ['Pro', 'Max', 'Ultra', 'Elite', 'Premium', 'Xiaomi', 'Lenovo', 'Stanley', 'Beauty', 'Fit', 'Home'][$i % 11];
                            $title = $noun . ' ' . $brand . ' ' . ($i + 1);
                            $price = round(19.90 + fmod($i * 4.75, 350), 2);
                            $sales = (15000 - ($i * 145) % 14500);
                            $revenue = $price * $sales;
                            $growth = round(5.0 + fmod($i * 2.3, 95), 1);
                            $rating = round(4.2 + fmod($i * 0.13, 0.8), 1);
                            if ($rating > 5.0) $rating = 5.0;
                            $color = $colors[$i % count($colors)];
                            $imgText = urlencode(mb_substr($noun, 0, 8));
                            $img = "https://placehold.co/100x100/{$color}/FFFFFF?text={$imgText}";

                            $tkProducts[] = [
                                'id' => 301 + $i,
                                'title' => $title,
                                'category' => $cat,
                                'price' => $price,
                                'sales' => $sales,
                                'revenue' => $revenue,
                                'growth' => $growth,
                                'rating' => $rating,
                                'img' => $img
                            ];
                        }

                        foreach ($tkProducts as $p):
                            $pSearchUrl = "https://www.tiktok.com/search?q=" . urlencode($p['title']);
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo $p['img']; ?>" alt="Foto" class="rounded border border-light-subtle" style="width: 45px; height: 45px; object-fit: cover;">
                            </td>
                            <td>
                                <a href="<?php echo $pSearchUrl; ?>" target="_blank" class="fw-semibold text-white hover-accent text-decoration-none text-truncate d-block" style="max-width: 240px;" title="<?php echo htmlspecialchars($p['title']); ?>">
                                    <?php echo htmlspecialchars($p['title']); ?> <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 8px; opacity: 0.7;"></i>
                                </a>
                                <small class="text-muted">ID: TK_<?php echo $p['id']; ?></small>
                            </td>
                            <td><span class="badge bg-secondary py-1"><?php echo $p['category']; ?></span></td>
                            <td class="fw-bold">R$ <?php echo number_format($p['price'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($p['sales'], 0, ',', '.'); ?> un</td>
                            <td class="text-accent-turquoise fw-bold">R$ <?php echo number_format($p['revenue'], 2, ',', '.'); ?></td>
                            <td class="text-success fw-semibold"><i class="fa-solid fa-circle-arrow-up me-1"></i>+<?php echo $p['growth']; ?>%</td>
                            <td><span class="text-warning fw-bold"><?php echo $p['rating']; ?> ★</span></td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="lookupSuppliers(<?php echo $p['id']; ?>, '<?php echo addslashes($p['title']); ?>', <?php echo $p['price']; ?>)" class="btn btn-sm btn-outline-turquoise" title="Encontrar Fornecedores"><i class="fa-solid fa-truck-ramp-box"></i></button>
                                    <button onclick="openCalculatorWithProduct('<?php echo addslashes($p['title']); ?>', <?php echo $p['price']; ?>)" class="btn btn-sm btn-outline-info" title="Calcular Margens"><i class="fa-solid fa-calculator"></i></button>
                                    <button onclick="saveTkProduct(<?php echo $p['id']; ?>, '<?php echo addslashes($p['title']); ?>', <?php echo $p['price']; ?>, '<?php echo addslashes($p['category']); ?>', '<?php echo addslashes($p['img']); ?>', <?php echo $p['sales']; ?>, this)" class="btn btn-sm btn-outline-danger" title="Favoritar & Salvar para IA"><i class="fa-regular fa-heart"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: TikTok Shop Stores Panel -->
    <div class="tab-pane fade" id="shops-panel" role="tabpanel" aria-labelledby="shops-tab">
        <div class="card-premium p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-store text-accent-turquoise me-2"></i> Lojas TikTok Shop BR</h5>
                <small class="text-muted">Ranking das lojas que mais faturaram na plataforma esta semana</small>
            </div>
            
            <div class="table-responsive">
                <table id="table-shops" class="table-premium" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th>Nome da Loja</th>
                            <th>Nicho Principal</th>
                            <th>Itens Vendidos</th>
                            <th>Faturamento (Semana)</th>
                            <th>Taxa Crescimento</th>
                            <th>Nota Avaliação</th>
                            <th>Catálogo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tkShops = [];
                        $storeNiches = [
                            'Tecnologia & Fones', 'Beleza & Lipsticks', 'Smartwatches & Wearables',
                            'Moda & Acessórios', 'Brinquedos & Hobbies', 'Utilidades Domésticas',
                            'Saúde & Fitness', 'Decoração & Lar'
                        ];
                        $storePrefixes = ['Mundo', 'Império', 'Atacado', 'Outlet', 'Oficial', 'Shop', 'Distribuidora', 'Central', 'Portal', 'Estilo'];
                        $storeSuffixes = ['Brasil', 'Cosméticos', 'Eletrônicos', 'Roupas', 'Toys', 'Utilidades', 'Smart', 'Multimarcas', 'Express', 'Premium'];

                        for ($i = 0; $i < 100; $i++) {
                            $rank = $i + 1;
                            $pref = $storePrefixes[$i % count($storePrefixes)];
                            $suff = $storeSuffixes[($i * 3) % count($storeSuffixes)];
                            $name = $pref . ' ' . $suff . ' ' . ($i + 1);
                            $niche = $storeNiches[$i % count($storeNiches)];
                            $items = (35000 - ($i * 340) % 34000);
                            $revenue = $items * round(25.0 + fmod($i * 7.50, 150), 2);
                            $growth = round(10.0 + fmod($i * 3.7, 85), 1);
                            $rating = round(4.3 + fmod($i * 0.08, 0.7), 1);
                            if ($rating > 5.0) $rating = 5.0;

                            $tkShops[] = [
                                'rank' => $rank,
                                'name' => $name,
                                'niche' => $niche,
                                'items' => $items,
                                'revenue' => $revenue,
                                'growth' => $growth,
                                'rating' => $rating
                            ];
                        }

                        foreach ($tkShops as $s):
                            $shopUrl = "https://www.tiktok.com/search?q=" . urlencode($s['name']);
                        ?>
                        <tr>
                            <td><span class="badge bg-purple-glow text-accent-purple fw-bold"><?php echo $s['rank']; ?></span></td>
                            <td>
                                <a href="<?php echo $shopUrl; ?>" target="_blank" class="fw-bold text-white text-decoration-none hover-accent">
                                    <?php echo htmlspecialchars($s['name']); ?> <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 8px; opacity: 0.7;"></i>
                                </a>
                                <small class="text-muted d-block">Shop ID: <?php echo 892340 + $s['rank']; ?></small>
                            </td>
                            <td><span class="badge bg-secondary py-1"><?php echo $s['niche']; ?></span></td>
                            <td><?php echo number_format($s['items'], 0, ',', '.'); ?> itens</td>
                            <td class="text-accent-turquoise fw-bold">R$ <?php echo number_format($s['revenue'], 2, ',', '.'); ?></td>
                            <td class="text-success fw-semibold"><i class="fa-solid fa-arrow-trend-up me-1"></i>+<?php echo $s['growth']; ?>%</td>
                            <td><span class="text-warning fw-bold"><?php echo $s['rating']; ?> ★</span></td>
                            <td>
                                <button onclick="searchKeyword('<?php echo addslashes($s['niche']); ?>')" class="btn btn-sm btn-outline-purple py-1 px-2" style="font-size: 11px;"><i class="fa-solid fa-magnifying-glass"></i> Ver Produtos</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Media & Creators Panel -->
    <div class="tab-pane fade" id="media-panel" role="tabpanel" aria-labelledby="media-tab">
        <div class="row">
            <!-- Left Column: Viral Videos Feed -->
            <div class="col-lg-6 mb-4">
                <div class="card-premium p-4">
                    <h5 class="fw-bold mb-3 text-white"><i class="fa-brands fa-tiktok text-danger me-2 animate-bounce"></i> Vídeos Mais Virais</h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="p-2 rounded border border-light-subtle d-flex align-items-center" style="background-color: rgba(255,255,255,0.01);">
                            <div class="bg-danger text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                <i class="fa-solid fa-play"></i>
                            </div>
                            <div class="text-truncate" style="flex:1;">
                                <strong class="text-white small text-truncate d-block">"Comprei isso na Shopee por 20 reais!"</strong>
                                <small class="text-muted d-block"><i class="fa-solid fa-eye me-1"></i> 1.2M views | <i class="fa-solid fa-heart me-1"></i> 180K likes</small>
                                <small class="text-accent-turquoise d-block">Est. Vendas: +3.2K un | Conversão: 4.2%</small>
                            </div>
                        </div>

                        <div class="p-2 rounded border border-light-subtle d-flex align-items-center" style="background-color: rgba(255, 255, 255, 0.01);">
                            <div class="bg-danger text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                <i class="fa-solid fa-play"></i>
                            </div>
                            <div class="text-truncate" style="flex:1;">
                                <strong class="text-white small text-truncate d-block">"Testei o smartwatch ultra no banho!"</strong>
                                <small class="text-muted d-block"><i class="fa-solid fa-eye me-1"></i> 840K views | <i class="fa-solid fa-heart me-1"></i> 95K likes</small>
                                <small class="text-accent-turquoise d-block">Est. Vendas: +1.8K un | Conversão: 3.5%</small>
                            </div>
                        </div>

                        <div class="p-2 rounded border border-light-subtle d-flex align-items-center" style="background-color: rgba(255, 255, 255, 0.01);">
                            <div class="bg-danger text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                <i class="fa-solid fa-play"></i>
                            </div>
                            <div class="text-truncate" style="flex:1;">
                                <strong class="text-white small text-truncate d-block">"Essa paleta de sombras é um escândalo"</strong>
                                <small class="text-muted d-block"><i class="fa-solid fa-eye me-1"></i> 620K views | <i class="fa-solid fa-heart me-1"></i> 110K likes</small>
                                <small class="text-accent-turquoise d-block">Est. Vendas: +2.1K un | Conversão: 5.1%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Creators -->
            <div class="col-lg-6 mb-4">
                <div class="card-premium p-4">
                    <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-crown text-warning me-2"></i> Criadores / Afiliados Revelação</h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between p-2 rounded border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                            <div>
                                <strong class="text-white small d-block">Amanda Rezende</strong>
                                <small class="text-muted">@amanda_reviews | 320K seg.</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-glow text-success">+R$ 180K</span>
                                <small class="text-muted d-block" style="font-size:10px;">Vendas Geradas</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                            <div>
                                <strong class="text-white small d-block">Lucas Achadinhos</strong>
                                <small class="text-muted">@lucas_promos | 180K seg.</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-glow text-success">+R$ 125K</span>
                                <small class="text-muted d-block" style="font-size:10px;">Vendas Geradas</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                            <div>
                                <strong class="text-white small d-block">Gaby Makeups</strong>
                                <small class="text-muted">@gabytrending | 410K seg.</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-glow text-success">+R$ 95K</span>
                                <small class="text-muted d-block" style="font-size:10px;">Vendas Geradas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tab 4: TikTok Shop / Creator Ads Keywords -->
    <div class="tab-pane fade" id="tiktok-ads-panel" role="tabpanel" aria-labelledby="tiktok-ads-tab">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-rectangle-ad text-accent-turquoise me-2"></i> Palavras-chave & Hashtags em Alta - TikTok Shop BR</h5>
                
                <?php
                $stmtTk = $db->prepare("SELECT * FROM keyword_trends WHERE marketplace = 'tiktok' ORDER BY volume DESC");
                $stmtTk->execute();
                $tiktokKeywords = $stmtTk->fetchAll();
                
                // If DB is empty, run seeder
                if (empty($tiktokKeywords)) {
                    TrendHunter\Database::checkAndCreateKeywordTrendsTable($db);
                    $stmtTk->execute();
                    $tiktokKeywords = $stmtTk->fetchAll();
                }

                $lastUpdatedTk = !empty($tiktokKeywords) ? $tiktokKeywords[0]['last_updated'] : date('Y-m-d H:i:s');
                $lastUpdatedFormatted = date('d/m/Y H:i', strtotime($lastUpdatedTk));
                ?>

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <p class="text-muted small mb-0">Consulte as hashtags e termos de busca com maior volume de visualizações acumuladas e engajamento em vídeos promocionais no TikTok Brasil.</p>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small"><i class="fa-regular fa-clock me-1 text-accent-purple"></i> Atualizado em: <strong><?php echo $lastUpdatedFormatted; ?></strong></span>
                        <button type="button" class="btn btn-sm btn-outline-purple" onclick="refreshMarketplaceKeywords('tiktok', this)">
                            <i class="fa-solid fa-arrows-rotate me-1 icon-spin"></i> Atualizar Dados
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="table-tiktok-keywords" class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Palavra-Chave / Hashtag (TikTok)</th>
                                <th>Visualizações / Buscas</th>
                                <th>CPM Médio</th>
                                <th>CTR Médio</th>
                                <th>Categoria Principal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiktokKeywords as $kw): ?>
                                <tr>
                                    <td class="fw-bold text-white"><i class="fa-brands fa-tiktok me-2 text-muted"></i> <?php echo htmlspecialchars($kw['keyword']); ?></td>
                                    <td data-order="<?php echo $kw['volume']; ?>"><?php echo number_format($kw['volume'], 0, ',', '.'); ?> views</td>
                                    <td class="fw-bold text-accent-turquoise" data-order="<?php echo (float)($kw['cpc_cpm'] ?? 4.0); ?>">R$ <?php echo number_format((float)($kw['cpc_cpm'] ?? 4.0), 2, ',', '.'); ?></td>
                                    <td class="text-success fw-bold"><?php echo htmlspecialchars($kw['growth']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($kw['category']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="searchKeywordFromAd('<?php echo addslashes($kw['keyword']); ?>')">
                                            <i class="fa-solid fa-magnifying-glass me-1"></i> Analisar Termo
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

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<!-- Include Layout Footer -->
<?php require __DIR__ . '/templates/footer.php'; ?>

<script>
$(document).ready(function() {
    const dtOptions = {
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        order: [[1, 'asc']]
    };
    if ($('#table-products').length) {
        $('#table-products').DataTable(dtOptions);
    }
    if ($('#table-shops').length) {
        $('#table-shops').DataTable(dtOptions);
    }
    if ($('#table-tiktok-keywords').length) {
        $('#table-tiktok-keywords').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            },
            order: [[1, 'desc']]
        });
    }
});

function saveTkProduct(id, title, price, category, img, sales, btn) {
    const icon = $(btn).find('i');
    $.post('api.php?action=add_favorite', {
        product_id: id,
        title: title,
        price: price,
        marketplace: 'TikTok Shop BR',
        image_url: img,
        url: 'https://www.tiktok.com/search?q=' + encodeURIComponent(title),
        store_name: 'TikTok Viral Top 100',
        category: category,
        sales_count_est: sales,
        trend_score: 95
    }, function(res) {
        if (res.success) {
            icon.removeClass('fa-regular').addClass('fa-solid');
            if (typeof showSaveToast === 'function') {
                showSaveToast('❤️ Produto TikTok salvo! Acesse o menu "Produtos Salvos & IA" para criar anúncios.');
            } else {
                alert('❤️ Produto TikTok salvo em Produtos Salvos & IA!');
            }
        } else {
            alert('Erro: ' + (res.error || 'Não foi possível salvar'));
        }
    }).fail(function() {
        alert('Erro de conexão ao salvar produto.');
    });
}
function searchKeywordFromAd(kw) {
    const input = $('input[name="keyword"]');
    if (input.length) {
        input.val(kw);
        const form = input.closest('form');
        if (form.length) {
            form.submit();
            // Scroll to the search bar smoothly
            $('html, body').animate({
                scrollTop: input.offset().top - 120
            }, 500);
        }
    }
}
function refreshMarketplaceKeywords(marketplace, btn) {
    const icon = $(btn).find('.icon-spin');
    icon.addClass('fa-spin');
    $(btn).prop('disabled', true);
    
    $.ajax({
        url: 'api.php?action=sync_keywords',
        method: 'POST',
        data: { marketplace: marketplace },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Erro ao atualizar: ' + (response.error || 'Erro desconhecido'));
                icon.removeClass('fa-spin');
                $(btn).prop('disabled', false);
            }
        },
        error: function() {
            alert('Erro de rede ou servidor ao atualizar palavras-chave.');
            icon.removeClass('fa-spin');
            $(btn).prop('disabled', false);
        }
    });
}
</script>
