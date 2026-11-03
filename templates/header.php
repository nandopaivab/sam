<?php
/**
 * TrendHunter Brasil - Header Layout Template
 */

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'TrendHunter\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use TrendHunter\Auth;

$user = Auth::getCurrentUser();
$darkMode = $user['dark_mode'] ?? true;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="<?php echo $darkMode ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SAM - Sistema de Análise de MarketPlace</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    <!-- jQuery (Load first in head for inline scripts compatibility) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        /* Specific page helpers */
        .bg-purple-glow {
            background-color: rgba(116, 93, 247, 0.08);
        }
        .text-accent-purple {
            color: #8772f9;
        }
        .border-purple-stroke {
            border: 1px solid rgba(116, 93, 247, 0.2) !important;
        }
        .bg-turquoise-glow {
            background-color: rgba(6, 225, 204, 0.08);
        }
        .text-accent-turquoise {
            color: #06e1cc;
        }
        .border-turquoise-stroke {
            border: 1px solid rgba(6, 225, 204, 0.2) !important;
        }
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
    </style>
</head>
<body>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$controlPages = ['erp.php', 'profit_calculator.php', 'investment_advisor.php', 'crm.php'];
$isControlMode = in_array($currentPage, $controlPages, true);

$pageMetaMap = [
    'index.php'               => ['pill' => 'SAM HYPE & RAIO-X', 'pill_class' => 'sidebar-badge-hype', 'title' => 'Central de Inteligência & Raio-X Multi-Marketplace', 'subtitle' => 'Mineração de produtos, estimativa de vendas, conversão e concorrência em tempo real.'],
    'metrify.php'             => ['pill' => 'SAM RAIO-X • MELI', 'pill_class' => 'sidebar-badge-pro', 'title' => 'Raio-X Mercado Livre & Market Share', 'subtitle' => 'Análise profunda de categorias, visitas, lojas oficiais e tendências regionais no ML.'],
    'shopee.php'              => ['pill' => 'SAM RAIO-X • SHP', 'pill_class' => 'sidebar-badge-pro', 'title' => 'Raio-X Shopee Brasil (SHP Analytics)', 'subtitle' => 'Rastreamento de mais vendidos, taxas Shopee, frete grátis e nichos de baixa concorrência.'],
    'kalodata.php'            => ['pill' => 'SAM RAIO-X • TIKTOK', 'pill_class' => 'sidebar-badge-hype', 'title' => 'Raio-X TikTok Shop & Criadores Virais', 'subtitle' => 'Métricas de vídeos, lives, receita por produto e tendências virais de e-commerce.'],
    'blue_ocean.php'          => ['pill' => 'SAM HYPE • IA', 'pill_class' => 'sidebar-badge-hype', 'title' => 'SAM Hype — Validador de Nichos Oceano Azul', 'subtitle' => 'Antecipe tendências com alta demanda e baixa concorrência validadas por IA.'],
    'global_trends.php'       => ['pill' => 'ALIBABA LENS & GLOBAL', 'pill_class' => 'sidebar-badge-hype', 'title' => 'Radar Global & Buscador de Fornecedores China/BR', 'subtitle' => 'Compare tendências internacionais e localize fabricantes para importação e arbitragem.'],
    'baby_niche.php'          => ['pill' => 'SAM HYPE • NICHO', 'pill_class' => 'sidebar-badge-hype', 'title' => 'Laboratório de Nicho: Bebês & Primeira Infância', 'subtitle' => 'Kits de alta recorrência, margem saudável e validação de demanda.'],
    'erp.php'                 => ['pill' => 'SAM CONTROL • GESTÃO', 'pill_class' => 'sidebar-badge-control', 'title' => 'SAM Control — Operação, Catálogo & Margem Real', 'subtitle' => 'Consolidação multi-marketplace, controle de estoque físico, pedidos e lucratividade líquida.'],
    'profit_calculator.php'   => ['pill' => 'SAM CONTROL • PRECIFICAÇÃO', 'pill_class' => 'sidebar-badge-control', 'title' => 'Simulador de Margem de Contribuição & Promoções', 'subtitle' => 'Calcule taxas reais, impostos, frete, ROAS/ACOS e trava de margem para campanhas.'],
    'investment_advisor.php'  => ['pill' => 'SAM CONTROL • CAPITAL', 'pill_class' => 'sidebar-badge-control', 'title' => 'Consultor de Estoque, Giro & ROI', 'subtitle' => 'Planejamento financeiro de compras e alocação inteligente de capital em mercadorias.'],
    'crm.php'                 => ['pill' => 'SAM CONTROL • B2B', 'pill_class' => 'sidebar-badge-control', 'title' => 'CRM Comercial, Fornecedores & Pipeline B2B', 'subtitle' => 'Gestão de negociações com distribuidores, cotações e follow-up comercial.'],
    'affiliate_generator.php' => ['pill' => 'SAM IA • STUDIO', 'pill_class' => 'sidebar-badge-pro', 'title' => 'Publicador IA: Títulos SEO, Copy & Anúncios', 'subtitle' => 'Crie títulos magnéticos, descrições persuasivas e scripts de conversão em segundos.'],
    'saved_products.php'      => ['pill' => 'CATÁLOGO MONITORADO', 'pill_class' => 'sidebar-badge-hype', 'title' => 'Produtos Salvos, Curva ABC & Inteligência IA', 'subtitle' => 'Acompanhe seus produtos favoritos, histórico de preços e recomendações estratégicas.'],
    'saved_suppliers.php'     => ['pill' => 'FORNECEDORES B2B', 'pill_class' => 'sidebar-badge-control', 'title' => 'Base de Fornecedores & Distribuidores Validados', 'subtitle' => 'Fontes de atacado nacionais e internacionais vinculadas à sua operação.'],
];
$activeMeta = $pageMetaMap[$currentPage] ?? [
    'pill' => 'SAM ECOSSISTEMA PRO',
    'pill_class' => 'sidebar-badge-control',
    'title' => 'Análise e Inteligência de Mercado',
    'subtitle' => 'Decisões baseadas em dados reais para vendedores de alta performance.'
];
?>
<div id="wrapper">
    <!-- Sidebar Navigation (Avantpro Architecture with SAM Brand) -->
    <aside id="sidebar">
        <div class="sidebar-header" style="padding: 16px 18px 12px 18px;">
            <a href="index.php" class="sidebar-logo d-flex align-items-center gap-2 text-decoration-none w-100">
                <img src="/assets/logo.png" alt="SAM Logo" style="width: 40px; height: 40px; border-radius: 10px; object-fit: contain; box-shadow: 0 0 16px rgba(99, 102, 241, 0.35);">
                <div class="sidebar-logo-text" style="line-height: 1.15;">
                    <div class="d-flex align-items-center gap-1">
                        <span style="font-weight: 800; font-size: 17px; color: #fff; letter-spacing: 0.4px;">SAM</span>
                        <span class="badge" style="background: var(--avant-primary-gradient); font-size: 9px; padding: 2px 6px; font-weight: 800;">PRO</span>
                    </div>
                    <span style="font-size: 10px; color: var(--accent-turquoise); display: block; letter-spacing: 0.4px; font-weight: 700;">Intelligence & Control</span>
                </div>
            </a>
        </div>

        <!-- Avantpro Style Mode Switcher (Hype/Intelligence vs Control) -->
        <div class="avant-workspace-switcher">
            <a href="index.php" class="avant-ws-btn <?php echo !$isControlMode ? 'active' : ''; ?>" title="Frente de Loja: Pesquisa, Raio-X e IA">
                <i class="fa-solid fa-bolt"></i> SAM Hype
            </a>
            <a href="erp.php" class="avant-ws-btn ws-control <?php echo $isControlMode ? 'active' : ''; ?>" title="Retaguarda: Gestão, Margem e Lucro">
                <i class="fa-solid fa-chart-Simple fa-chart-column"></i> SAM Control
            </a>
        </div>

        <ul class="sidebar-menu">
            <!-- PILAR 1: SAM HYPE & MINERAÇÃO -->
            <li class="sidebar-section-label">
                <span><i class="fa-solid fa-compass me-1 text-accent-turquoise"></i> Mineração & Hype</span>
                <span class="text-accent-turquoise" style="font-size: 9px;">IA</span>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php" class="sidebar-link">
                    <i class="fa-solid fa-magnifying-glass-chart text-accent-turquoise"></i>
                    <span>Scanner & Raio-X</span>
                    <span class="sidebar-badge sidebar-badge-hype">HYPE</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'blue_ocean.php' ? 'active' : ''; ?>">
                <a href="blue_ocean.php" class="sidebar-link">
                    <i class="fa-solid fa-water text-primary"></i>
                    <span>Validador Oceano Azul</span>
                    <span class="sidebar-badge sidebar-badge-pro">IA</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'global_trends.php' ? 'active' : ''; ?>">
                <a href="global_trends.php" class="sidebar-link">
                    <i class="fa-solid fa-earth-americas text-info"></i>
                    <span>Alibaba Lens & Global</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'baby_niche.php' ? 'active' : ''; ?>">
                <a href="baby_niche.php" class="sidebar-link">
                    <i class="fa-solid fa-baby text-accent-turquoise"></i>
                    <span>Nicho: Bebês & Kits</span>
                </a>
            </li>

            <!-- PILAR 2: RAIO-X POR MARKETPLACE -->
            <li class="sidebar-section-label">
                <span><i class="fa-solid fa-store me-1 text-warning"></i> Raio-X Marketplaces</span>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'metrify.php' ? 'active' : ''; ?>">
                <a href="metrify.php" class="sidebar-link">
                    <i class="fa-solid fa-handshake text-warning"></i>
                    <span>SAM • Mercado Livre</span>
                    <span class="sidebar-badge" style="background: rgba(245,158,11,0.15); color:#f59e0b;">MELI</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'shopee.php' ? 'active' : ''; ?>">
                <a href="shopee.php" class="sidebar-link">
                    <i class="fa-solid fa-bag-shopping" style="color: #ee4d2d;"></i>
                    <span>SAM • Shopee (SHP)</span>
                    <span class="sidebar-badge" style="background: rgba(238,77,45,0.15); color:#ee4d2d;">SHP</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'kalodata.php' ? 'active' : ''; ?>">
                <a href="kalodata.php" class="sidebar-link">
                    <i class="fa-brands fa-tiktok text-danger animate-pulse"></i>
                    <span>SAM • TikTok Shop</span>
                    <span class="sidebar-badge" style="background: rgba(239,68,68,0.15); color:#f87171;">LIVE</span>
                </a>
            </li>

            <!-- PILAR 3: SAM CONTROL (GESTÃO & FINANÇAS) -->
            <li class="sidebar-section-label">
                <span><i class="fa-solid fa-shield-halved me-1 text-accent-purple"></i> SAM Control (Gestão)</span>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'erp.php' ? 'active' : ''; ?>">
                <a href="erp.php" class="sidebar-link">
                    <i class="fa-solid fa-boxes-stacked text-success"></i>
                    <span>Control: Catálogo & DRE</span>
                    <span class="sidebar-badge sidebar-badge-control">CTRL</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'profit_calculator.php' ? 'active' : ''; ?>">
                <a href="profit_calculator.php" class="sidebar-link">
                    <i class="fa-solid fa-calculator text-accent-turquoise"></i>
                    <span>Margem & Promoções</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'investment_advisor.php' ? 'active' : ''; ?>">
                <a href="investment_advisor.php" class="sidebar-link">
                    <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                    <span>Simulador ROI & Giro</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'crm.php' ? 'active' : ''; ?>">
                <a href="crm.php" class="sidebar-link">
                    <i class="fa-solid fa-handshake-angle text-accent-purple"></i>
                    <span>CRM & Atacado B2B</span>
                </a>
            </li>

            <!-- PILAR 4: FERRAMENTAS SELLER & IA -->
            <li class="sidebar-section-label">
                <span><i class="fa-solid fa-wand-magic-sparkles me-1 text-info"></i> Ferramentas & IA</span>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'affiliate_generator.php' ? 'active' : ''; ?>">
                <a href="affiliate_generator.php" class="sidebar-link">
                    <i class="fa-solid fa-wand-magic-sparkles text-accent-purple"></i>
                    <span>Criador de Anúncios IA</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" onclick="openEanGeneratorModal(); return false;" class="sidebar-link">
                    <i class="fa-solid fa-barcode text-warning"></i>
                    <span>Gerador EAN-13 / GTIN</span>
                    <span class="sidebar-badge sidebar-badge-pro">NOVO</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'saved_products.php' ? 'active' : ''; ?>">
                <a href="saved_products.php" class="sidebar-link">
                    <i class="fa-regular fa-heart text-danger"></i>
                    <span>Produtos Monitorados</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'saved_suppliers.php' ? 'active' : ''; ?>">
                <a href="saved_suppliers.php" class="sidebar-link">
                    <i class="fa-solid fa-truck-fast text-accent-turquoise"></i>
                    <span>Fornecedores Salvos</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="index.php#alerts-section" class="sidebar-link">
                    <i class="fa-regular fa-bell text-warning"></i>
                    <span>Radar de Alertas</span>
                </a>
            </li>

            <!-- PILAR 5: CONFIGURAÇÕES & SISTEMA -->
            <li class="sidebar-section-label">
                <span><i class="fa-solid fa-sliders me-1 text-muted"></i> Infraestrutura</span>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'database_sync.php' ? 'active' : ''; ?>">
                <a href="database_sync.php" class="sidebar-link">
                    <i class="fa-solid fa-cloud-arrow-down text-accent-turquoise"></i>
                    <span>Sincronização & Backup</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php" class="sidebar-link">
                    <i class="fa-solid fa-gears text-info"></i>
                    <span>Integrações & APIs</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentPage === 'audit_logs.php' ? 'active' : ''; ?>">
                <a href="audit_logs.php" class="sidebar-link">
                    <i class="fa-solid fa-file-shield text-warning"></i>
                    <span>Log de Auditoria</span>
                </a>
            </li>
            
            <!-- Termômetro de Buscas em Alta do Dia (Avantpro Hype Radar) -->
            <li class="mt-3 px-2">
                <div class="p-3 rounded-3 border border-light-subtle" style="background: linear-gradient(145deg, rgba(99, 102, 241, 0.08), rgba(6, 225, 204, 0.04));">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-light-subtle border-opacity-25">
                        <div class="d-flex align-items-center gap-1">
                            <i class="fa-solid fa-fire text-warning" style="font-size: 12px;"></i>
                            <h6 class="mb-0 fw-bold" style="font-size: 11px; color: var(--text-primary); letter-spacing: 0.2px;">SAM Hype • Alta do Dia (<?php echo date('d/m'); ?>)</h6>
                        </div>
                        <button type="button" onclick="refreshTrends(true)" class="btn btn-link p-0 text-muted hover-accent-turquoise" title="Sincronizar Ranking Oficial da Alta do Dia" style="font-size: 11px; text-decoration: none;"><i class="fa-solid fa-arrows-rotate"></i></button>
                    </div>
                    <!-- 2 Abinhas de Fonte Oficial -->
                    <div class="d-flex gap-1 mb-2 p-1 rounded-2" style="background: rgba(15, 23, 42, 0.45); border: 1px solid rgba(148, 163, 184, 0.14);">
                        <button type="button" id="hype-tab-ecommerce" onclick="switchHypeTrendsTab('ecommerce')" class="btn btn-sm flex-fill py-1 px-1 fw-bold border-0 rounded-1" style="font-size: 10px; background: linear-gradient(135deg, rgba(6, 225, 204, 0.22), rgba(99, 102, 241, 0.22)); color: #06e1cc; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                            <i class="fa-solid fa-cart-shopping me-1" style="font-size: 9px;"></i>Varejo
                        </button>
                        <button type="button" id="hype-tab-google" onclick="switchHypeTrendsTab('google')" class="btn btn-sm flex-fill py-1 px-1 fw-semibold border-0 rounded-1" style="font-size: 10px; background: transparent; color: var(--text-secondary);">
                            <i class="fa-brands fa-google me-1" style="font-size: 9px;"></i>Google Top 10
                        </button>
                    </div>
                    <ul id="google-trends-list" class="list-unstyled p-0 m-0 d-flex flex-column gap-1" style="font-size: 11px;">
                        <div class="text-center py-2"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>
                    </ul>
                </div>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center text-truncate">
                    <div class="bg-purple-glow text-accent-purple rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-weight: bold;">
                        <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="text-truncate">
                        <div class="fw-semibold text-white small text-truncate"><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></div>
                        <small class="text-accent-turquoise" style="font-size: 10px; font-weight: 600;">Plano PRO • Multi-CNPJ</small>
                    </div>
                </div>
                <a href="login.php?logout=1" class="text-danger ms-2" title="Sair do Sistema"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </aside>
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

    <!-- Main Content Wrapper -->
    <div id="content-wrapper">
        <header id="topbar">
            <div class="d-flex align-items-center">
                <button id="mobile-sidebar-toggle" class="btn btn-outline-secondary border-light-subtle d-lg-none me-3" style="border-radius: 10px; width: 42px; height: 42px; padding: 0;" aria-label="Abrir menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="sidebar-badge <?php echo $activeMeta['pill_class']; ?>" style="margin-left: 0; font-size: 9px; padding: 2px 8px;">
                            <i class="fa-solid fa-circle-bolt me-1"></i><?php echo htmlspecialchars($activeMeta['pill']); ?>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle d-none d-xl-inline-flex align-items-center gap-1" style="font-size: 10px; font-weight: 600;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            ML • Shopee • TikTok • Amazon Conectados
                        </span>
                    </div>
                    <h5 class="mb-0 fw-bold" style="font-size: 1.05rem;"><?php echo htmlspecialchars($activeMeta['title']); ?></h5>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-1 gap-md-2 ms-auto">
                <!-- Quick Tool: Gerador EAN-13/GTIN (Avantpro Signature Tool) -->
                <button type="button" onclick="openEanGeneratorModal()" class="btn btn-outline-secondary border-light-subtle d-none d-md-inline-flex align-items-center gap-2 px-3" style="border-radius: 10px; height: 40px; font-size: 12px; font-weight: 600;" title="Gerar Código EAN-13 / GTIN Válido para Anúncios">
                    <i class="fa-solid fa-barcode text-warning"></i>
                    <span>Gerar EAN/GTIN</span>
                </button>

                <!-- Quick Tool: Simulador de Margem -->
                <a href="profit_calculator.php" class="btn btn-outline-secondary border-light-subtle d-none d-lg-inline-flex align-items-center gap-2 px-3" style="border-radius: 10px; height: 40px; font-size: 12px; font-weight: 600;" title="Calculadora de Margem de Contribuição e Taxas">
                    <i class="fa-solid fa-calculator text-accent-turquoise"></i>
                    <span>Margem Real</span>
                </a>

                <!-- Theme Toggle Button -->
                <button class="btn btn-outline-secondary border-light-subtle" onclick="toggleThemeMode()" id="theme-toggle-btn" style="border-radius: 10px; width: 40px; height: 40px; padding: 0;" title="Alternar Tema Claro/Escuro">
                    <?php if ($darkMode): ?>
                        <i class="fa-solid fa-sun text-warning"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-moon text-primary"></i>
                    <?php endif; ?>
                </button>
                <!-- Notification Bell Dropdown -->
                <div class="dropdown me-1">
                    <button class="btn btn-outline-secondary border-light-subtle position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; width: 40px; height: 40px; padding: 0;">
                        <i class="fa-solid fa-bell text-warning"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            3
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-light-subtle p-2" style="width: 320px; border-radius: 14px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom border-light-subtle d-flex justify-content-between align-items-center">
                            <span class="fw-bold small"><i class="fa-solid fa-bell text-warning me-1"></i> Central SAM Control & Hype</span>
                            <span class="badge bg-primary-subtle text-primary small">3 Novos</span>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="crm.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-purple-glow text-accent-purple me-2"><i class="fa-solid fa-handshake-angle"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">4 Follow-ups de Atacado</div>
                                        <div class="text-muted" style="font-size: 11px;">Acesse o pipeline B2B do SAM Control.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="blue_ocean.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-primary-subtle text-primary me-2"><i class="fa-solid fa-water"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">Alerta SAM Hype: Oceano Azul</div>
                                        <div class="text-muted" style="font-size: 11px;">Organizadores com margem > 140% e baixa concorrência.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="baby_niche.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-success-subtle text-success me-2"><i class="fa-solid fa-baby"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">Validador IA: Kits Bebês</div>
                                        <div class="text-muted" style="font-size: 11px;">IA identificou 3 novos kits de alta conversão.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="border-start border-light-subtle h-25 mx-1 align-self-center d-none d-sm-block"></div>

                <!-- Connected User Profile Panel Dropdown -->
                <div class="dropdown ms-1">
                    <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 12px;">
                        <div class="rounded-circle bg-purple-glow text-accent-purple d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: 700;">
                            <?php echo strtoupper(substr($user['name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="text-start d-none d-md-block me-2">
                            <div class="fw-bold lh-1 text-white" style="font-size: 13px;">
                                <?php echo htmlspecialchars($user['name'] ?? 'Administrador'); ?>
                            </div>
                            <div class="text-muted" style="font-size: 10px;">
                                <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'Seller PRO')); ?> • Multi-CNPJ
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-light-subtle p-2" style="width: 280px; border-radius: 14px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom border-light-subtle mb-2">
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($user['name'] ?? 'Administrador'); ?></div>
                            <div class="text-accent-turquoise small fw-semibold"><i class="fa-solid fa-shield-check me-1"></i> SAM PRO • Ecossistema Completo</div>
                            <div class="text-muted small mt-1" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i> Sessão ativa: <?php echo date('d/m/Y H:i'); ?></div>
                        </li>
                        <li>
                            <a class="dropdown-item rounded py-2 d-flex align-items-center" href="settings.php">
                                <i class="fa-solid fa-plug me-2 text-accent-turquoise"></i> Contas & Integrações API
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded py-2 d-flex align-items-center" href="erp.php">
                                <i class="fa-solid fa-chart-pie me-2 text-accent-purple"></i> Painel SAM Control (DRE)
                            </a>
                        </li>
                        <li><hr class="dropdown-divider border-light-subtle"></li>
                        <li>
                            <a class="dropdown-item rounded py-2 d-flex align-items-center text-danger fw-bold" href="logout.php">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Sair do Sistema
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Global Modal: Gerador de Código EAN-13 / GTIN Válido (Estilo Avantpro) -->
        <div class="modal fade" id="eanGeneratorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-light-subtle shadow-lg" style="background-color: var(--card-bg); border-radius: 16px;">
                    <div class="modal-header border-bottom border-light-subtle">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="fa-solid fa-barcode text-warning me-2"></i> Gerador de EAN-13 / GTIN Válido (Padrão Brasil 789)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" style="filter: invert(1);"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Gere códigos de barras EAN-13 com dígito verificador oficial GS1 (prefixo 789 Brasil) prontos para cadastro de anúncios no Mercado Livre, Shopee e Amazon.</p>
                        <div class="p-3 rounded-3 mb-3 text-center border border-light-subtle" style="background: rgba(0,0,0,0.35);">
                            <small class="text-uppercase text-accent-turquoise fw-bold d-block mb-1" style="font-size: 10px; letter-spacing: 1px;">Código EAN-13 Gerado</small>
                            <div id="generated-ean-Main" class="fw-bold text-white font-monospace" style="font-size: 28px; letter-spacing: 3px;">7898357410015</div>
                            <div class="small text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i> Dígito verificador matemático validado</div>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" onclick="generateValidEan13()" class="btn btn-primary flex-grow-1 fw-bold" style="background: var(--avant-primary-gradient); border: none; border-radius: 10px;">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Gerar Novo EAN-13
                            </button>
                            <button type="button" onclick="copyGeneratedEan()" class="btn btn-outline-info fw-bold px-3" style="border-radius: 10px;">
                                <i class="fa-regular fa-copy me-1"></i> Copiar
                            </button>
                        </div>
                        <div>
                            <label class="form-label small text-muted fw-semibold mb-1">Lote Rápido (5 Códigos Validados):</label>
                            <div id="generated-ean-batch" class="p-2 rounded border border-light-subtle font-monospace small text-light" style="background: rgba(0,0,0,0.2); font-size: 12px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function computeEan13CheckDigit(twelveDigits) {
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                const digit = parseInt(twelveDigits.charAt(i), 10);
                sum += (i % 2 === 0) ? digit : digit * 3;
            }
            const remainder = sum % 10;
            return remainder === 0 ? 0 : (10 - remainder);
        }
        function createSingleEan13() {
            let base = '789';
            for (let i = 0; i < 9; i++) {
                base += Math.floor(Math.random() * 10).toString();
            }
            return base + computeEan13CheckDigit(base);
        }
        function generateValidEan13() {
            const mainEan = createSingleEan13();
            const elMain = document.getElementById('generated-ean-Main');
            if (elMain) elMain.innerText = mainEan;
            const batchEl = document.getElementById('generated-ean-batch');
            if (batchEl) {
                let batchHtml = '';
                for (let i = 0; i < 5; i++) {
                    const code = createSingleEan13();
                    batchHtml += `<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light-subtle border-opacity-10">
                        <span>${code}</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-accent-turquoise text-decoration-none" onclick="navigator.clipboard.writeText('${code}'); this.innerText='Copiado!';">Copiar</button>
                    </div>`;
                }
                batchEl.innerHTML = batchHtml;
            }
        }
        function openEanGeneratorModal() {
            generateValidEan13();
            const modalEl = document.getElementById('eanGeneratorModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
        function copyGeneratedEan() {
            const elMain = document.getElementById('generated-ean-Main');
            if (elMain) {
                navigator.clipboard.writeText(elMain.innerText);
                alert('Código EAN-13 copiado: ' + elMain.innerText);
            }
        }
        </script>

        <!-- 30-Minute Inactivity Session Monitor -->
        <div class="modal fade" id="sessionExpireModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom border-light-subtle">
                        <h5 class="modal-title text-warning fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i> Aviso de Expiração de Sessão</h5>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Sua sessão inativa está prestes a expirar em <strong id="sessionCountdown" class="text-danger">2:00</strong> minutos.</p>
                        <p class="text-muted small mb-0">Para proteger seus dados e manter a auditoria do SAM, a sessão é encerrada após 30 minutos sem interação. Os dados não salvos em formulários abertos serão preservados no armazenamento local.</p>
                    </div>
                    <div class="modal-footer border-top border-light-subtle">
                        <a href="logout.php" class="btn btn-outline-secondary border-light-subtle">Sair Agora</a>
                        <button type="button" class="btn btn-primary" id="btnRenewSession"><i class="fa-solid fa-arrows-rotate me-1"></i> Continuar Conectado</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Theme Toggle Mode Function
        function toggleThemeMode() {
            const isDark = $('html').attr('data-bs-theme') === 'dark';
            const newMode = isDark ? 0 : 1;
            $.post('api.php?action=toggle_theme', { dark_mode: newMode }, function(res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert('Não foi possível alterar o tema.');
                }
            }).fail(function() {
                alert('Erro ao conectar com o servidor.');
            });
        }

        // Automatic Session Expiry Monitor (30 Minutes = 1800s; Warn at 1680s)
        (function() {
            let inactiveTime = 0;
            const WARN_TIME = 28 * 60; // 28 minutes
            const MAX_TIME = 30 * 60;  // 30 minutes
            let countdownInterval = null;

            function resetTimer() {
                if (inactiveTime < WARN_TIME) {
                    inactiveTime = 0;
                }
            }

            window.addEventListener('mousemove', resetTimer);
            window.addEventListener('keypress', resetTimer);
            window.addEventListener('click', resetTimer);

            setInterval(function() {
                inactiveTime++;
                if (inactiveTime === WARN_TIME) {
                    const modalEl = document.getElementById('sessionExpireModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                        let remaining = MAX_TIME - WARN_TIME;
                        const countEl = document.getElementById('sessionCountdown');
                        clearInterval(countdownInterval);
                        countdownInterval = setInterval(() => {
                            remaining--;
                            if (countEl) {
                                let mins = Math.floor(remaining / 60);
                                let secs = remaining % 60;
                                countEl.innerText = `${mins}:${secs < 10 ? '0' : ''}${secs}`;
                            }
                            if (remaining <= 0) {
                                clearInterval(countdownInterval);
                                window.location.href = 'login.php?expired=1';
                            }
                        }, 1000);
                    }
                } else if (inactiveTime >= MAX_TIME) {
                    window.location.href = 'login.php?expired=1';
                }
            }, 1000);

            const btnRenew = document.getElementById('btnRenewSession');
            if (btnRenew) {
                btnRenew.addEventListener('click', function() {
                    inactiveTime = 0;
                    clearInterval(countdownInterval);
                    const modalEl = document.getElementById('sessionExpireModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }
                });
            }
        })();
        </script>

        <main class="content-body">
            <!-- Dynamic Alert banners will slide here if triggered alerts exist -->
            <div id="alert-notification-banners"></div>
