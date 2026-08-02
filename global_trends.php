<?php
/**
 * TrendHunter Brasil - SAM - Global & China Trends Analyzer
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

// Mock International Trending Gadgets
$globalGadgets = [
    [
        'id' => 201,
        'title' => 'Mini Projetor Inteligente HY300 4K Android',
        'origin' => 'AliExpress (China)',
        'origin_key' => 'china',
        'cost_usd' => 38.50,
        'suggested_brl' => 349.90,
        'trend_score' => 98,
        'monthly_growth' => '+320%',
        'virality_factor' => 'Explosivo (TikTok Global)',
        'est_import_tax' => 17.50, // USD (ICMS + Import)
        'category' => 'Eletrônicos & Imagem',
        'hot_link' => 'https://best.aliexpress.com/'
    ],
    [
        'id' => 202,
        'title' => 'Mini Selador Térmico de Sacolas e Embalagens',
        'origin' => 'AliExpress (China)',
        'origin_key' => 'china',
        'cost_usd' => 1.80,
        'suggested_brl' => 29.90,
        'trend_score' => 92,
        'monthly_growth' => '+85%',
        'virality_factor' => 'Muito Alto (Reels/Shorts)',
        'est_import_tax' => 0.40,
        'category' => 'Utilidades Domésticas',
        'hot_link' => 'https://best.aliexpress.com/'
    ],
    [
        'id' => 203,
        'title' => 'Escova de Limpeza Elétrica Giratória 5 em 1',
        'origin' => 'AliExpress (China)',
        'origin_key' => 'china',
        'cost_usd' => 11.20,
        'suggested_brl' => 119.90,
        'trend_score' => 96,
        'monthly_growth' => '+240%',
        'virality_factor' => 'Explosivo (TikTok BR/US)',
        'est_import_tax' => 2.30,
        'category' => 'Casa & Limpeza',
        'hot_link' => 'https://best.aliexpress.com/'
    ],
    [
        'id' => 204,
        'title' => 'Umidificador de Vulcão Flame Difusor de Aromas',
        'origin' => 'Temu (China / Global)',
        'origin_key' => 'temu',
        'cost_usd' => 7.80,
        'suggested_brl' => 89.90,
        'trend_score' => 95,
        'monthly_growth' => '+190%',
        'virality_factor' => 'Explosivo (Estética Quarto)',
        'est_import_tax' => 1.60,
        'category' => 'Decoração & Bem-Estar',
        'hot_link' => 'https://www.temu.com/'
    ],
    [
        'id' => 205,
        'title' => 'Impressora Térmica Portátil Bluetooth M02',
        'origin' => 'AliExpress (China)',
        'origin_key' => 'china',
        'cost_usd' => 18.50,
        'suggested_brl' => 189.90,
        'trend_score' => 94,
        'monthly_growth' => '+145%',
        'virality_factor' => 'Alto (Estudantes / Papelaria)',
        'est_import_tax' => 3.90,
        'category' => 'Papelaria & Escritório',
        'hot_link' => 'https://best.aliexpress.com/'
    ],
    [
        'id' => 206,
        'title' => 'Suporte de Celular Carregador por Indução Inteligente',
        'origin' => 'Temu (China / Global)',
        'origin_key' => 'temu',
        'cost_usd' => 8.90,
        'suggested_brl' => 99.90,
        'trend_score' => 93,
        'monthly_growth' => '+115%',
        'virality_factor' => 'Muito Alto (Car Gadgets)',
        'est_import_tax' => 1.80,
        'category' => 'Acessórios Automotivos',
        'hot_link' => 'https://www.temu.com/'
    ],
    [
        'id' => 207,
        'title' => 'Aspirador de Pó de Mesa Mini USB Recarregável',
        'origin' => 'Temu (China / Global)',
        'origin_key' => 'temu',
        'cost_usd' => 4.20,
        'suggested_brl' => 59.90,
        'trend_score' => 89,
        'monthly_growth' => '+75%',
        'virality_factor' => 'Alto (ASMR Limpeza Mesa)',
        'est_import_tax' => 0.85,
        'category' => 'Escritório & Casa',
        'hot_link' => 'https://www.temu.com/'
    ],
    [
        'id' => 208,
        'title' => 'Carregador Magnético Ultra-Fino Powerbank Qi',
        'origin' => 'Temu (China / Global)',
        'origin_key' => 'temu',
        'cost_usd' => 13.90,
        'suggested_brl' => 139.90,
        'trend_score' => 91,
        'monthly_growth' => '+130%',
        'virality_factor' => 'Muito Alto (iPhone Gadgets)',
        'est_import_tax' => 2.90,
        'category' => 'Celulares & Acessórios',
        'hot_link' => 'https://www.temu.com/'
    ],
    [
        'id' => 209,
        'title' => 'Microfone de Lapela Sem Fio Dual Lightning/Type-C',
        'origin' => 'Amazon US (Estados Unidos)',
        'origin_key' => 'usa',
        'cost_usd' => 14.50,
        'suggested_brl' => 159.90,
        'trend_score' => 94,
        'monthly_growth' => '+165%',
        'virality_factor' => 'Alto (Criação de Conteúdo)',
        'est_import_tax' => 3.10,
        'category' => 'Áudio & Vídeo',
        'hot_link' => 'https://www.amazon.com/'
    ],
    [
        'id' => 210,
        'title' => 'Espelho de Maquiagem LED Dobrável Recarregável USB',
        'origin' => 'Amazon US (Estados Unidos)',
        'origin_key' => 'usa',
        'cost_usd' => 12.80,
        'suggested_brl' => 129.90,
        'trend_score' => 90,
        'monthly_growth' => '+95%',
        'virality_factor' => 'Médio-Alto (Beleza & Make)',
        'est_import_tax' => 2.70,
        'category' => 'Beleza & Cuidados',
        'hot_link' => 'https://www.amazon.com/'
    ],
    [
        'id' => 211,
        'title' => 'Massageador de Pescoço EMS de Condução Térmica',
        'origin' => 'Amazon US (Estados Unidos)',
        'origin_key' => 'usa',
        'cost_usd' => 16.50,
        'suggested_brl' => 179.90,
        'trend_score' => 92,
        'monthly_growth' => '+110%',
        'virality_factor' => 'Alto (Saúde & Bem-Estar)',
        'est_import_tax' => 3.50,
        'category' => 'Saúde & Bem-Estar',
        'hot_link' => 'https://www.amazon.com/'
    ],
    [
        'id' => 212,
        'title' => 'Lixeira Inteligente com Sensor Antiodor 14L',
        'origin' => 'Amazon US (Estados Unidos)',
        'origin_key' => 'usa',
        'cost_usd' => 19.90,
        'suggested_brl' => 199.90,
        'trend_score' => 93,
        'monthly_growth' => '+125%',
        'virality_factor' => 'Alto (Smart Home Gadgets)',
        'est_import_tax' => 4.20,
        'category' => 'Utilidades Domésticas',
        'hot_link' => 'https://www.amazon.com/'
    ]
];

require __DIR__ . '/templates/header.php';
?>

<!-- Global Trends Custom CSS -->
<style>
    .market-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .badge-china {
        background-color: rgba(235, 87, 87, 0.15);
        color: #eb5757;
        border-color: rgba(235, 87, 87, 0.25);
    }
    .badge-temu {
        background-color: rgba(242, 153, 74, 0.15);
        color: #f2994a;
        border-color: rgba(242, 153, 74, 0.25);
    }
    .badge-usa {
        background-color: rgba(47, 128, 237, 0.15);
        color: #2f80ed;
        border-color: rgba(47, 128, 237, 0.25);
    }
    .score-badge {
        font-size: 14px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--accent-turquoise), var(--accent-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<div class="main-content-wrapper">
    <div class="container-fluid px-4">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold text-white mb-1"><i class="fa-solid fa-earth-americas text-info me-2"></i>Tendências Globais & Gadgets da China</h1>
                <p class="text-muted mb-0">Radar em tempo real de produtos virais em mercados internacionais para importação imediata (AliExpress, Temu e Amazon US).</p>
            </div>
            
            <div class="d-flex gap-2">
                <button onclick="triggerGlobalSync(this)" class="btn btn-outline-info">
                    <i class="fa-solid fa-arrows-rotate me-1 icon-spin"></i> Atualizar Radar Global
                </button>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="mb-4">
            <div class="btn-group border border-light-subtle rounded p-1 bg-dark bg-opacity-20" role="group">
                <button type="button" class="btn btn-sm btn-dark px-3 active filter-market-btn" onclick="filterMarkets('all', this)">Todos os Mercados</button>
                <button type="button" class="btn btn-sm btn-dark px-3 filter-market-btn" onclick="filterMarkets('china', this)">AliExpress (China)</button>
                <button type="button" class="btn btn-sm btn-dark px-3 filter-market-btn" onclick="filterMarkets('temu', this)">Temu (Global)</button>
                <button type="button" class="btn btn-sm btn-dark px-3 filter-market-btn" onclick="filterMarkets('usa', this)">Amazon US (EUA)</button>
            </div>
        </div>

        <!-- Radar Load Spinner -->
        <div id="radar-loading" class="d-none text-center py-5">
            <div class="spinner-border text-info mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="text-white fw-bold">Rastreando Lojas e Redes Sociais Globais...</h5>
            <p class="text-muted small">Mapeando virais no TikTok US/Asia, cotações no AliExpress e taxas de crescimento da Temu.</p>
        </div>

        <!-- Content Grid -->
        <div class="row g-4" id="gadgets-grid">
            <?php foreach ($globalGadgets as $g): 
                $badgeClass = 'badge-china';
                if ($g['origin_key'] === 'temu') $badgeClass = 'badge-temu';
                if ($g['origin_key'] === 'usa') $badgeClass = 'badge-usa';
                
                // Estimates
                $usdToBrl = 5.20; // Simulated stable conversion
                $costBrl = $g['cost_usd'] * $usdToBrl;
                $importTaxBrl = $g['est_import_tax'] * $usdToBrl;
                $totalCostBrl = $costBrl + $importTaxBrl;
                
                $estimatedProfit = $g['suggested_brl'] - $totalCostBrl;
                $estimatedRoi = round(($estimatedProfit / max(1.0, $totalCostBrl)) * 100, 1);
                
                $cleanTitle = str_replace("'", "\\'", $g['title']);
                ?>
                <div class="col-12 col-md-6 col-lg-4 gadget-card-wrapper" data-market="<?php echo $g['origin_key']; ?>">
                    <div class="card-premium h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                        
                        <!-- Top details -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="market-badge <?php echo $badgeClass; ?>"><i class="fa-solid fa-plane-departure me-1"></i><?php echo $g['origin']; ?></span>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-fire text-danger animate-pulse"></i>
                                    <span class="score-badge"><?php echo $g['trend_score']; ?>/100</span>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($g['title']); ?></h5>
                            <span class="badge bg-dark border text-muted mb-3"><?php echo htmlspecialchars($g['category']); ?></span>
                            
                            <!-- Key data points -->
                            <div class="row g-2 p-3 rounded border border-light-subtle bg-dark bg-opacity-30 mb-3" style="font-size: 12px;">
                                <div class="col-6">
                                    <div class="text-muted mb-1">Custo FOB na China:</div>
                                    <strong class="text-white fs-6">USD <?php echo number_format($g['cost_usd'], 2, '.', ','); ?></strong>
                                    <small class="text-muted d-block">(~R$ <?php echo number_format($costBrl, 2, ',', '.'); ?>)</small>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted mb-1">Imp. Importação Est:</div>
                                    <strong class="text-white fs-6">USD <?php echo number_format($g['est_import_tax'], 2, '.', ','); ?></strong>
                                    <small class="text-muted d-block">(~R$ <?php echo number_format($importTaxBrl, 2, ',', '.'); ?>)</small>
                                </div>
                                <div class="col-12 border-top border-light-subtle pt-2 mt-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Custo Final Estimado:</span>
                                        <strong class="text-light">R$ <?php echo number_format($totalCostBrl, 2, ',', '.'); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted">Preço Sugerido BR:</span>
                                        <strong class="text-accent-turquoise">R$ <?php echo number_format($g['suggested_brl'], 2, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Signals -->
                            <div class="mb-3 p-2 rounded bg-opacity-10 bg-info border border-info border-opacity-10" style="font-size: 11px;">
                                <div class="d-flex justify-content-between text-white">
                                    <span><i class="fa-solid fa-chart-line me-1"></i> Crescimento Mensal: <strong class="text-success"><?php echo $g['monthly_growth']; ?></strong></span>
                                    <span><i class="fa-solid fa-clapperboard me-1"></i> <?php echo $g['virality_factor']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div>
                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <a href="index.php?query=<?php echo urlencode($g['title']); ?>" class="btn btn-sm btn-outline-light w-100 py-2" style="font-size: 11px;" title="Buscar Concorrência BR">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar no BR
                                    </a>
                                </div>
                                <div class="col-6">
                                    <button onclick="openCalculatorWithProduct('<?php echo $cleanTitle; ?>', <?php echo $g['suggested_brl']; ?>, <?php echo $totalCostBrl; ?>)" class="btn btn-sm btn-outline-info w-100 py-2" style="font-size: 11px;" title="Calcular ROI de Importação">
                                        <i class="fa-solid fa-calculator me-1"></i> Simular Lucro
                                    </button>
                                </div>
                                <div class="col-12 mt-2">
                                    <a href="<?php echo $g['hot_link']; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-info w-100 py-2" style="font-size: 11px; background: linear-gradient(135deg, var(--accent-turquoise), var(--accent-purple)); border: 0;">
                                        <i class="fa-solid fa-cart-shopping me-1"></i> Ver Fornecedor Internacional (Importar)
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<script>
function filterMarkets(market, btn) {
    // Active styling
    document.querySelectorAll('.filter-market-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    // Cards visibility
    const cards = document.querySelectorAll('.gadget-card-wrapper');
    if (market === 'all') {
        cards.forEach(c => c.classList.remove('d-none'));
    } else {
        cards.forEach(c => {
            if (c.getAttribute('data-market') === market) {
                c.classList.remove('d-none');
            } else {
                c.classList.add('d-none');
            }
        });
    }
}

function triggerGlobalSync(btn) {
    const icon = $(btn).find('.icon-spin');
    icon.addClass('fa-spin');
    $(btn).prop('disabled', true);
    
    // Smooth loader view simulation
    document.getElementById('gadgets-grid').classList.add('d-none');
    document.getElementById('radar-loading').classList.remove('d-none');
    
    setTimeout(function() {
        document.getElementById('radar-loading').classList.add('d-none');
        document.getElementById('gadgets-grid').classList.remove('d-none');
        icon.removeClass('fa-spin');
        $(btn).prop('disabled', false);
        
        alert('🎉 Radar de Importação atualizado! Encontrados 4 novos gadgets com alta tração nas últimas 24h na China.');
    }, 2500);
}
</script>

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
