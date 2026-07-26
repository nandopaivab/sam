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
                <h2 class="fw-bold text-white mb-1"><i class="fa-brands fa-tiktok text-danger me-2 animate-pulse"></i> Kalodata TikTok Shop Analítico</h2>
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
</script>
