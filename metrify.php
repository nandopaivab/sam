<?php
/**
 * TrendHunter Brasil - SAM - Mercado Livre Market Share & Regional Trends
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

// Detailed category database mirroring Metrify & screenshot analytics
$categoriesData = [
    [
        'id' => 1,
        'name' => 'Acessórios para Veículos',
        'share' => 47.3,
        'ads' => '102.93 mi',
        'subcategories_count' => 24,
        'avg_ads' => '4.29 mi',
        'color' => '#8e44ad',
        'results_count' => '142.500',
        'visits_count' => '4.21M',
        'avg_price' => '289,90',
        'market_leaders' => '12.440',
        'frequent_words' => [
            ['word' => 'Led', 'count' => 150],
            ['word' => 'Farol', 'count' => 120],
            ['word' => 'Carro', 'count' => 98],
            ['word' => 'Pneu', 'count' => 84],
            ['word' => 'Automotivo', 'count' => 76],
            ['word' => 'Suporte', 'count' => 62],
            ['word' => 'Capa', 'count' => 45]
        ],
        'hot_queries' => [
            'lampada led automotiva', 'farol de milha universal', 'pneu aro 14 firestone',
            'central multimidia android', 'suporte celular veicular', 'capa de banco couro'
        ],
        'official_stores' => [
            ['name' => 'Connect Parts', 'ads' => 340],
            ['name' => 'Mega Tuning', 'ads' => 190],
            ['name' => 'Pneus Prime', 'ads' => 140],
            ['name' => 'Auto Express', 'ads' => 85]
        ],
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
        'color' => '#16a085',
        'results_count' => '48.900',
        'visits_count' => '1.85M',
        'avg_price' => '154,50',
        'market_leaders' => '5.930',
        'frequent_words' => [
            ['word' => 'Mesa', 'count' => 95],
            ['word' => 'Organizador', 'count' => 88],
            ['word' => 'Cozinha', 'count' => 82],
            ['word' => 'Suporte', 'count' => 74],
            ['word' => 'Prateleira', 'count' => 61],
            ['word' => 'Decoração', 'count' => 54],
            ['word' => 'Kit', 'count' => 48]
        ],
        'hot_queries' => [
            'organizador de gavetas acrilico', 'mesa de cabeceira suspensa', 'suporte temperos parede',
            'prateleira madeira rustica', 'kit organizador cozinha', 'quadro decorativo sala'
        ],
        'official_stores' => [
            ['name' => 'Decora Fácil', 'ads' => 120],
            ['name' => 'Lojas Conforto', 'ads' => 95],
            ['name' => 'Casa & Arte', 'ads' => 82]
        ],
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
        'name' => 'Brinquedos e Hobbies',
        'share' => 1.7,
        'ads' => '3.63 mi',
        'subcategories_count' => 18,
        'avg_ads' => '201.6 mil',
        'color' => '#d35400',
        'results_count' => '2.972',
        'visits_count' => '609.142',
        'avg_price' => '169,59',
        'market_leaders' => '1.444',
        'frequent_words' => [
            ['word' => 'Bicicleta', 'count' => 95],
            ['word' => 'Infantil', 'count' => 82],
            ['word' => 'Equilíbrio', 'count' => 67],
            ['word' => 'Pedal', 'count' => 62],
            ['word' => 'Rodas', 'count' => 35],
            ['word' => 'Rosa', 'count' => 28],
            ['word' => 'Balance', 'count' => 28],
            ['word' => 'Azul', 'count' => 22],
            ['word' => 'Bike', 'count' => 20],
            ['word' => 'Nathor', 'count' => 13]
        ],
        'hot_queries' => [
            'bicicleta equilibrio', 'bicicleta buba', 'bicicleta nathor balance',
            'bicicleta sem pedal', 'bicicleta de equilibrio caloi', 'bicicleta infantil sem pedal'
        ],
        'official_stores' => [
            ['name' => 'MSP KIDS', 'ads' => 23],
            ['name' => 'MAIS BARATO BRINQUEDO', 'ads' => 13],
            ['name' => 'FLOAT TOYS', 'ads' => 12],
            ['name' => 'RMAISTRCH', 'ads' => 6]
        ],
        'subcategories' => [
            ['name' => 'Mini Veículos e Bicicletas', 'share' => 48.6, 'ads' => '1.76 mi'],
            ['name' => 'Jogos de Tabuleiro', 'share' => 22.4, 'ads' => '813 mil'],
            ['name' => 'Bonecas e Acessórios', 'share' => 15.0, 'ads' => '544 mil'],
            ['name' => 'Blocos de Montar', 'share' => 14.0, 'ads' => '508 mil']
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
        'results_count' => '32.400',
        'visits_count' => '1.12M',
        'avg_price' => '89,90',
        'market_leaders' => '4.210',
        'frequent_words' => [
            ['word' => 'Tênis', 'count' => 120],
            ['word' => 'Camiseta', 'count' => 105],
            ['word' => 'Feminino', 'count' => 94],
            ['word' => 'Masculino', 'count' => 88],
            ['word' => 'Kit', 'count' => 82],
            ['word' => 'Bolsa', 'count' => 67],
            ['word' => 'Preto', 'count' => 45]
        ],
        'hot_queries' => [
            'tenis corrida esportivo', 'kit camisetas algodao 30.1', 'bolsa transversal feminina',
            'calça jeans slim fit', 'meias invisiveis atacado', 'mochila impermeavel notebook'
        ],
        'official_stores' => [
            ['name' => 'Moda Express', 'ads' => 240],
            ['name' => 'Calçados Conforto', 'ads' => 180],
            ['name' => 'Outlet Store', 'ads' => 120]
        ],
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
        'results_count' => '18.900',
        'visits_count' => '940.000',
        'avg_price' => '64,90',
        'market_leaders' => '2.140',
        'frequent_words' => [
            ['word' => 'Sérum', 'count' => 84],
            ['word' => 'Creme', 'count' => 76],
            ['word' => 'Kit', 'count' => 70],
            ['word' => 'Cabelo', 'count' => 65],
            ['word' => 'Facial', 'count' => 58],
            ['word' => 'Matte', 'count' => 45]
        ],
        'hot_queries' => [
            'serum acido hialuronico', 'kit maquiagem profissional', 'protetor solar fps 60',
            'escova secadora rotativa', 'oleo capilar reconstrutor', 'base liquida alta cobertura'
        ],
        'official_stores' => [
            ['name' => 'Beleza Cosméticos', 'ads' => 150],
            ['name' => 'Skincare Store', 'ads' => 94]
        ],
        'subcategories' => [
            ['name' => 'Cuidados com a Pele (Skincare)', 'share' => 38.0, 'ads' => '2.22 mi'],
            ['name' => 'Maquiagem Facial', 'share' => 27.5, 'ads' => '1.61 mi'],
            ['name' => 'Aparelhos de Cabelo (Secadores)', 'share' => 16.5, 'ads' => '0.96 mi'],
            ['name' => 'Perfumes Importados', 'share' => 10.0, 'ads' => '0.58 mi'],
            ['name' => 'Cuidados com Unhas', 'share' => 8.0, 'ads' => '0.46 mi']
        ]
    ]
];

// Mock Google Trends Regional Data (States of Brazil e-commerce hot searches)
$regionalTrends = [
    'SP' => [
        'name' => 'São Paulo',
        'top_searches' => [
            ['keyword' => 'Copo Stanley 1.2L', 'volume' => 100, 'growth' => '+320%', 'badge' => 'Explosivo'],
            ['keyword' => 'Mini Projetor Smart Wifi', 'volume' => 92, 'growth' => '+180%', 'badge' => 'Alta Demanda'],
            ['keyword' => 'Fone Bluetooth TWS Redmi', 'volume' => 88, 'growth' => '+140%', 'badge' => 'Estável'],
            ['keyword' => 'Organizador Acrílico Giratório', 'volume' => 75, 'growth' => '+250%', 'badge' => 'Explosivo'],
            ['keyword' => 'Bicicleta de Equilíbrio Infantil', 'volume' => 64, 'growth' => '+95%', 'badge' => 'Crescente']
        ],
        'hot_category' => 'Eletrônicos & Utilidades'
    ],
    'RJ' => [
        'name' => 'Rio de Janeiro',
        'top_searches' => [
            ['keyword' => 'Garrafa Térmica Academia', 'volume' => 100, 'growth' => '+210%', 'badge' => 'Alta Demanda'],
            ['keyword' => 'Óculos de Sol Cat Eye Vintage', 'volume' => 89, 'growth' => '+150%', 'badge' => 'Crescente'],
            ['keyword' => 'Fone Bluetooth Gamer', 'volume' => 85, 'growth' => '+120%', 'badge' => 'Estável'],
            ['keyword' => 'Bolsa Tiracolo Feminina', 'volume' => 78, 'growth' => '+80%', 'badge' => 'Estável'],
            ['keyword' => 'Caixa de Som Portátil Prova D\'Água', 'volume' => 70, 'growth' => '+190%', 'badge' => 'Crescente']
        ],
        'hot_category' => 'Esportes & Moda'
    ],
    'MG' => [
        'name' => 'Minas Gerais',
        'top_searches' => [
            ['keyword' => 'Mop Giratório Balde Limpeza', 'volume' => 100, 'growth' => '+140%', 'badge' => 'Estável'],
            ['keyword' => 'Organizador de Temperos Cozinha', 'volume' => 94, 'growth' => '+280%', 'badge' => 'Explosivo'],
            ['keyword' => 'Mordedor Sensorial Girafa Silicone', 'volume' => 82, 'growth' => '+110%', 'badge' => 'Crescente'],
            ['keyword' => 'Prato Ventosa Bebê Alimentação', 'volume' => 79, 'growth' => '+130%', 'badge' => 'Crescente'],
            ['keyword' => 'Lâmpada Inteligente RGB Alexa', 'volume' => 72, 'growth' => '+90%', 'badge' => 'Estável']
        ],
        'hot_category' => 'Utilidades Domésticas & Bebês'
    ],
    'BA' => [
        'name' => 'Bahia',
        'top_searches' => [
            ['keyword' => 'Ring Light Tripé Iluminação', 'volume' => 100, 'growth' => '+120%', 'badge' => 'Estável'],
            ['keyword' => 'Microfone Lapela Sem Fio USB', 'volume' => 91, 'growth' => '+200%', 'badge' => 'Alta Demanda'],
            ['keyword' => 'Batom Lip Tint Magic Gloss', 'volume' => 84, 'growth' => '+170%', 'badge' => 'Crescente'],
            ['keyword' => 'Smartwatch Watch 9 Ultra NFC', 'volume' => 79, 'growth' => '+150%', 'badge' => 'Crescente'],
            ['keyword' => 'Mini Processador Alimentos USB', 'volume' => 68, 'growth' => '+95%', 'badge' => 'Estável']
        ],
        'hot_category' => 'Vestíveis & Beleza'
    ],
    'PR' => [
        'name' => 'Paraná',
        'top_searches' => [
            ['keyword' => 'Moletom Canguru com Capuz', 'volume' => 100, 'growth' => '+310%', 'badge' => 'Sazonal Explosivo'],
            ['keyword' => 'Jaqueta Corta Vento Impermeável', 'volume' => 93, 'growth' => '+240%', 'badge' => 'Sazonal Alta'],
            ['keyword' => 'Aquecedor Elétrico Portátil', 'volume' => 88, 'growth' => '+420%', 'badge' => 'Explosivo'],
            ['keyword' => 'Copo Térmico Cerveja Stanley', 'volume' => 82, 'growth' => '+130%', 'badge' => 'Estável'],
            ['keyword' => 'Suporte Articulado Monitor Duplo', 'volume' => 70, 'growth' => '+110%', 'badge' => 'Estável']
        ],
        'hot_category' => 'Moda Inverno & Tech'
    ],
    'RS' => [
        'name' => 'Rio Grande do Sul',
        'top_searches' => [
            ['keyword' => 'Calça Alfaiataria Masculina', 'volume' => 100, 'growth' => '+180%', 'badge' => 'Alta Demanda'],
            ['keyword' => 'Jaqueta Forrada Pelúcia Masculina', 'volume' => 95, 'growth' => '+390%', 'badge' => 'Sazonal Explosivo'],
            ['keyword' => 'Balança Digital Cozinha Precisão', 'volume' => 82, 'growth' => '+90%', 'badge' => 'Estável'],
            ['keyword' => 'Kit Potes Vidro Herméticos', 'volume' => 78, 'growth' => '+150%', 'badge' => 'Crescente'],
            ['keyword' => 'Luminária de Mesa Monitor Barra', 'volume' => 71, 'growth' => '+110%', 'badge' => 'Estável']
        ],
        'hot_category' => 'Moda Masculina & Cozinha'
    ]
];

require __DIR__ . '/templates/header.php';
?>

<!-- SAM - Mercado Livre style modifications -->
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

    /* Word clouds and list tags matching the screenshot upload */
    .word-cloud-tag {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 6px;
        margin-right: 6px;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .hot-query-tag {
        display: inline-block;
        padding: 6px 12px;
        background-color: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: var(--text-primary);
        border-radius: 8px;
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hot-query-tag:hover {
        background-color: #00d2ff;
        color: #0b0c16;
        border-color: #00d2ff;
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Block -->
    <div class="row align-items-center mb-4">
        <div class="col-12 col-md-6">
            <h1 class="h3 fw-bold mb-1 text-white"><i class="fa-solid fa-chart-column text-accent-turquoise me-2"></i> SAM - Mercado Livre</h1>
            <p class="text-muted small mb-0">Cockpit analítico de participação de mercado, buscas em alta regional do Google Trends e detalhamento de categorias.</p>
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
                <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-trophy text-warning me-2"></i> Top 50 Produtos Mais Vendidos / Buscados (Mercado Livre)</h6>
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
        formData.append('marketplace', 'mercadolivre');

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
    <ul class="nav nav-tabs metrify-tab-nav mb-4" id="samMlTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="market-tab" data-bs-toggle="tab" data-bs-target="#market-panel" type="button" role="tab"><i class="fa-solid fa-folder-tree me-1"></i> Categorias & Market Share</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="regional-tab" data-bs-toggle="tab" data-bs-target="#regional-panel" type="button" role="tab"><i class="fa-solid fa-earth-americas me-1"></i> Google Trends Regional (Estados)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ml-ads-tab" data-bs-toggle="tab" data-bs-target="#ml-ads-panel" type="button" role="tab"><i class="fa-solid fa-rectangle-ad me-1"></i> Mercado Livre Ads Palavras-chave</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="samMlTabsContent">
        
        <!-- Tab 1: Categories and Market Share Grid -->
        <div class="tab-pane fade show active" id="market-panel" role="tabpanel" aria-labelledby="market-tab">
            <!-- Filters & Sorting Controls -->
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

            <!-- Categories Grid -->
            <div class="row g-3" id="metrifyCardsGrid">
                <?php foreach ($categoriesData as $c): ?>
                    <div class="col-12 col-md-6 col-lg-4 metrify-card-col" data-name="<?php echo htmlspecialchars(strtolower($c['name'])); ?>" data-share="<?php echo $c['share']; ?>" data-sub="<?php echo $c['subcategories_count']; ?>">
                        <div class="metrify-card h-100">
                            <div class="card-stripe" style="background-color: <?php echo $c['color']; ?>;"></div>
                            
                            <div>
                                <div class="metrify-card-header">
                                    <span class="bullet-indicator" style="background-color: <?php echo $c['color']; ?>;"></span>
                                    <h3 class="metrify-card-title"><?php echo htmlspecialchars($c['name']); ?></h3>
                                </div>

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

                                <div class="metrify-pill-container">
                                    <div class="metrify-pill">
                                        <i class="fa-solid fa-sitemap me-1 text-muted"></i> <strong><?php echo $c['subcategories_count']; ?></strong> subcat.
                                    </div>
                                    <div class="metrify-pill">
                                        <i class="fa-solid fa-layer-group me-1 text-muted"></i> <strong><?php echo $c['avg_ads']; ?></strong> méd.
                                    </div>
                                </div>
                            </div>

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

        <!-- Tab 2: Google Trends Regional (States of Brazil Analyzer) -->
        <div class="tab-pane fade" id="regional-panel" role="tabpanel" aria-labelledby="regional-tab">
            <div class="row g-4">
                <!-- State Selector List -->
                <div class="col-12 col-lg-4">
                    <div class="card-premium p-4">
                        <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-location-dot text-accent-turquoise me-2"></i> Estados do Brasil</h5>
                        <p class="text-muted small">Selecione o estado brasileiro para filtrar as buscas e micro-tendências locais de e-commerce.</p>
                        
                        <div class="list-group d-flex flex-column gap-2" id="states-list-group">
                            <?php foreach ($regionalTrends as $uf => $data): ?>
                                <button type="button" class="btn btn-outline-secondary border-light-subtle text-start text-white p-3 d-flex justify-content-between align-items-center state-btn" id="btn-state-<?php echo $uf; ?>" onclick="selectTrendsState('<?php echo $uf; ?>')" style="border-radius: 10px; background-color: rgba(255,255,255,0.01);">
                                    <div>
                                        <span class="fw-bold text-metrify-cyan me-2"><?php echo $uf; ?></span>
                                        <span><?php echo htmlspecialchars($data['name']); ?></span>
                                    </div>
                                    <span class="badge bg-dark text-white border border-light-subtle small"><?php echo htmlspecialchars($data['hot_category']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- State Hot Searches Breakdown Panel -->
                <div class="col-12 col-lg-8">
                    <div class="card-premium p-4 h-100" id="trends-detail-panel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-light-subtle">
                            <div>
                                <h4 class="fw-bold text-white mb-0" id="state-title-label">Tendências SP</h4>
                                <p class="text-muted small mb-0">Produtos e palavras-chave que mais cresceram em buscas nas últimas 72 horas.</p>
                            </div>
                            <span class="badge bg-metrify-glow text-metrify-cyan border border-metrify-cyan px-3 py-2" id="state-hot-category-badge">Eletrônicos</span>
                        </div>

                        <!-- Progress indicators for searches -->
                        <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-fire text-danger me-2"></i> Produtos Mais Buscados na Região:</h6>
                        <div class="d-flex flex-column gap-3 mb-4" id="trends-progress-container">
                            <!-- Populated dynamically -->
                        </div>

                        <div class="alert alert-info rounded-3 border-0 bg-metrify-glow border-metrify-cyan text-white small p-3">
                            <i class="fa-solid fa-lightbulb me-2 text-metrify-cyan"></i>
                            <strong>Dica SAM:</strong> Os itens listados em **Explosivo** ou **Sazonal** possuem alta probabilidade de esgotamento e picos de demanda localizados. Considere anunciar com entrega expressa focada nestas regiões.
                        </div>
                    </div>

                    <!-- Placeholder before selection -->
                    <div class="card-premium p-5 text-center h-100 d-flex flex-column justify-content-center align-items-center" id="trends-placeholder-panel">
                        <i class="fa-solid fa-earth-americas text-muted fs-1 mb-3 animate-pulse"></i>
                        <h5 class="text-white">Selecione um Estado do Brasil</h5>
                        <p class="text-muted" style="max-width: 400px;">Clique em um dos estados na barra lateral para analisar dados geográficos e palavras-chave de maior interesse da população local via Google Trends.</p>
                    </div>
                </div>
            </div>
        <!-- Tab 3: Mercado Livre Product Ads Keywords -->
        <div class="tab-pane fade" id="ml-ads-panel" role="tabpanel" aria-labelledby="ml-ads-tab">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-rectangle-ad text-accent-turquoise me-2"></i> Palavras-chave Recomendadas - Mercado Livre Product Ads</h5>
                <p class="text-muted small mb-4">Consulte os termos com maior volume de buscas internas no Mercado Livre BR, concorrência estimada e custo por clique médio sugerido.</p>
                
                <?php
                $mlKeywords = [
                    ['keyword' => 'garrafa termica stanley', 'volume' => 380000, 'cpc' => 0.45, 'growth' => '+150%', 'competition' => 'Alta'],
                    ['keyword' => 'mini liquidificador portatil', 'volume' => 220000, 'cpc' => 0.28, 'growth' => '+120%', 'competition' => 'Média'],
                    ['keyword' => 'organizador de gavetas', 'volume' => 140000, 'cpc' => 0.18, 'growth' => '+90%', 'competition' => 'Baixa'],
                    ['keyword' => 'fone de ouvido bluetooth sem fio', 'volume' => 450000, 'cpc' => 0.52, 'growth' => '+110%', 'competition' => 'Alta'],
                    ['keyword' => 'ring light de mesa led', 'volume' => 190000, 'cpc' => 0.35, 'growth' => '+75%', 'competition' => 'Média']
                ];
                ?>
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Palavra-Chave (ML Ads)</th>
                                <th>Buscas Mensais</th>
                                <th>CPC Sugerido</th>
                                <th>Concorrência</th>
                                <th>Crescimento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mlKeywords as $kw): 
                                $compBadge = $kw['competition'] === 'Alta' ? 'bg-danger-subtle text-danger border-danger-subtle' : ($kw['competition'] === 'Média' ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-success-subtle text-success border-success-subtle');
                                ?>
                                <tr>
                                    <td class="fw-bold text-white"><i class="fa-solid fa-magnifying-glass me-2 text-muted"></i> <?php echo htmlspecialchars($kw['keyword']); ?></td>
                                    <td><?php echo number_format($kw['volume'], 0, ',', '.'); ?> buscas</td>
                                    <td class="fw-bold text-accent-turquoise">R$ <?php echo number_format($kw['cpc'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $compBadge; ?> border px-2 py-1 small"><?php echo $kw['competition']; ?></span>
                                    </td>
                                    <td class="text-success fw-bold"><i class="fa-solid fa-arrow-trend-up"></i> <?php echo $kw['growth']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="$('#search-query').val('<?php echo addslashes($kw['keyword']); ?>'); window.scrollTo({top: 0, behavior: 'smooth'}); setTimeout(() => {$('#search-form').submit();}, 300);">
                                            <i class="fa-solid fa-search me-1"></i> Analisar Termo
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

<!-- Modal: Category Analysis Breakdown (Matching the user's Metrify screenshot layout) -->
<div class="modal fade" id="categoryAnalysisModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-chart-simple text-accent-turquoise me-2"></i> SAM - Análise Avançada de Nicho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Metrify style indicators row from user screenshot -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">RESULTADOS</div>
                            <div class="h4 fw-bold text-white mt-1 mb-0" id="modal-results-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">LOJAS OFICIAIS</div>
                            <div class="h4 fw-bold text-metrify-cyan mt-1 mb-0" id="modal-stores-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">PREÇO MÉDIO</div>
                            <div class="h4 fw-bold text-success mt-1 mb-0" id="modal-avg-price-val">R$ 0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">LÍDERES DE MERCADO</div>
                            <div class="h4 fw-bold text-warning mt-1 mb-0" id="modal-leaders-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">VISITAS ACUMULADAS</div>
                            <div class="h4 fw-bold text-white mt-1 mb-0" id="modal-visits-val">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="p-3 rounded border border-light-subtle h-100" style="background: rgba(255,255,255,0.01);">
                            <div class="text-muted small" style="font-size: 10px;">SUBCATEGORIAS</div>
                            <div class="h4 fw-bold text-accent-purple mt-1 mb-0" id="modal-subs-val">0</div>
                        </div>
                    </div>
                </div>

                <!-- Word frequencies and hot queries matching the screenshot -->
                <div class="row g-4 mb-4">
                    <!-- Word frequencies -->
                    <div class="col-12 col-md-6">
                        <div class="p-4 rounded-3 border border-light-subtle h-100" style="background-color: rgba(255,255,255,0.01);">
                            <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-font text-metrify-cyan me-2"></i> Palavras mais frequentes nos títulos:</h6>
                            <div class="d-flex flex-wrap" id="modal-words-container">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                    <!-- Hot searches -->
                    <div class="col-12 col-md-6">
                        <div class="p-4 rounded-3 border border-light-subtle h-100" style="background-color: rgba(255,255,255,0.01);">
                            <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-magnifying-glass text-warning me-2"></i> Mais buscados no Mercado Livre:</h6>
                            <div class="d-flex flex-wrap" id="modal-hot-queries-container">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribution subcategories table -->
                <div class="row g-4">
                    <div class="col-12 col-lg-7">
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

                    <!-- Official stores block from screenshot -->
                    <div class="col-12 col-lg-5">
                        <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-store me-2 text-success"></i> Lojas oficiais em destaque:</h6>
                        <div class="list-group d-flex flex-column gap-2" id="modal-stores-container">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top border-light-subtle">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar Análise</button>
            </div>
        </div>
    </div>
</div>

<script>
const categoriesData = <?php echo json_encode($categoriesData); ?>;
const regionalTrends = <?php echo json_encode($regionalTrends); ?>;

function openCategoryAnalysisModal(catId) {
    const cat = categoriesData.find(c => c.id === catId);
    if (!cat) return;

    // Fill indicators
    document.getElementById('modal-results-val').innerText = cat.results_count;
    document.getElementById('modal-stores-val').innerText = cat.official_stores.length + ' lojas';
    document.getElementById('modal-avg-price-val').innerText = 'R$ ' + cat.avg_price;
    document.getElementById('modal-leaders-val').innerText = cat.market_leaders;
    document.getElementById('modal-visits-val').innerText = cat.visits_count;
    document.getElementById('modal-subs-val').innerText = cat.subcategories_count;

    // Frequent words tags
    const wordsContainer = document.getElementById('modal-words-container');
    wordsContainer.innerHTML = '';
    const wordColors = ['#fc5c65', '#fd9644', '#feb342', '#2bcbba', '#45aaf2', '#a55eea', '#778ca3'];
    cat.frequent_words.forEach((item, idx) => {
        const color = wordColors[idx % wordColors.length];
        const span = `
            <span class="word-cloud-tag" style="background-color: rgba(${hexToRgb(color)}, 0.08); color: ${color}; border-color: rgba(${hexToRgb(color)}, 0.2);">
                ${item.word} <strong class="ms-1" style="opacity: 0.6;">${item.count}</strong>
            </span>
        `;
        wordsContainer.innerHTML += span;
    });

    // Hot queries tags
    const hotContainer = document.getElementById('modal-hot-queries-container');
    hotContainer.innerHTML = '';
    cat.hot_queries.forEach(query => {
        const span = `<span class="hot-query-tag" onclick="closeModalAndSearch('${query}')">${query}</span>`;
        hotContainer.innerHTML += span;
    });

    // Subcategories table
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

    // Official stores list
    const storesContainer = document.getElementById('modal-stores-container');
    storesContainer.innerHTML = '';
    cat.official_stores.forEach(store => {
        const div = `
            <div class="p-3 rounded border border-light-subtle d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.01);">
                <div class="fw-semibold text-white"><i class="fa-solid fa-circle-check text-success me-1"></i> ${store.name}</div>
                <span class="badge bg-metrify-glow text-metrify-cyan border border-metrify-cyan">${store.ads} Anúncios</span>
            </div>
        `;
        storesContainer.innerHTML += div;
    });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('categoryAnalysisModal'));
    modal.show();
}

function closeModalAndSearch(query) {
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('categoryAnalysisModal')).hide();
    // Redirect to index with query
    window.location.href = 'index.php?q=' + encodeURIComponent(query);
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? 
        parseInt(result[1], 16) + ',' + parseInt(result[2], 16) + ',' + parseInt(result[3], 16) 
        : '255,255,255';
}

function selectTrendsState(uf) {
    // Toggle active classes in state buttons
    document.querySelectorAll('.state-btn').forEach(btn => {
        btn.classList.remove('border-info');
        btn.style.backgroundColor = 'rgba(255,255,255,0.01)';
    });
    const activeBtn = document.getElementById('btn-state-' + uf);
    activeBtn.classList.add('border-info');
    activeBtn.style.backgroundColor = 'rgba(0, 210, 255, 0.05)';

    const data = regionalTrends[uf];
    if (!data) return;

    // Fill state trends data
    document.getElementById('state-title-label').innerText = 'Tendências no Google Trends - ' + data.name;
    document.getElementById('state-hot-category-badge').innerText = data.hot_category;

    const container = document.getElementById('trends-progress-container');
    container.innerHTML = '';

    data.top_searches.forEach(item => {
        let badgeColor = 'bg-info-subtle text-info border-info-subtle';
        if (item.badge.includes('Explosivo')) {
            badgeColor = 'bg-danger-subtle text-danger border-danger-subtle';
        } else if (item.badge.includes('Sazonal')) {
            badgeColor = 'bg-warning-subtle text-warning border-warning-subtle';
        }

        const block = `
            <div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-white" style="cursor:pointer;" onclick="window.location.href='index.php?q=${encodeURIComponent(item.keyword)}'">
                        <i class="fa-solid fa-magnifying-glass text-muted me-1"></i> ${item.keyword}
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge ${badgeColor} border px-2 py-1 small">${item.badge}</span>
                        <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-trend-up"></i> ${item.growth}</span>
                    </div>
                </div>
                <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.05);">
                    <div class="progress-bar progress-bar-cyan" role="progressbar" style="width: ${item.volume}%"></div>
                </div>
            </div>
        `;
        container.innerHTML += block;
    });

    // Show details
    document.getElementById('trends-placeholder-panel').style.display = 'none';
    document.getElementById('trends-detail-panel').style.display = 'block';
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

    if (type === 'share') {
        document.getElementById('btn-sort-share').className = 'btn btn-primary px-3 text-white fw-bold active';
        document.getElementById('btn-sort-sub').className = 'btn btn-outline-secondary border-light-subtle px-3 text-white';
        
        cols.sort((a, b) => {
            return parseFloat(b.getAttribute('data-share')) - parseFloat(a.getAttribute('data-share'));
        });
    } else {
        document.getElementById('btn-sort-share').className = 'btn btn-outline-secondary border-light-subtle px-3 text-white';
        document.getElementById('btn-sort-sub').className = 'btn btn-primary px-3 text-white fw-bold active';
        document.getElementById('btn-sort-sub').style.backgroundColor = '#5f27cd';
        document.getElementById('btn-sort-sub').style.borderColor = '#5f27cd';
        document.getElementById('btn-sort-share').style.backgroundColor = 'transparent';

        cols.sort((a, b) => {
            return parseInt(b.getAttribute('data-sub')) - parseInt(a.getAttribute('data-sub'));
        });
    }

    cols.forEach(col => grid.appendChild(col));
}

// Auto-run initial sorting by share
$(document).ready(function() {
    sortCategories('share');
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
