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

<div id="wrapper">
    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fa-solid fa-square-trend-up"></i>
                </div>
                <div class="sidebar-logo-text">
                    SAM <span style="font-size: 10px; color: var(--accent-turquoise); display: block; letter-spacing: 1px; font-weight: 800;">Análise de MarketPlace</span>
                </div>
            </a>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php" class="sidebar-link">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Pesquisador Principal</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'kalodata.php' ? 'active' : ''; ?>">
                <a href="kalodata.php" class="sidebar-link">
                    <i class="fa-brands fa-tiktok text-danger animate-pulse"></i>
                    <span>Kalodata TikTok Intel</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'affiliate_generator.php' ? 'active' : ''; ?>">
                <a href="affiliate_generator.php" class="sidebar-link">
                    <i class="fa-solid fa-wand-magic-sparkles text-accent-purple"></i>
                    <span>Gerador de Anúncios</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'saved_suppliers.php' ? 'active' : ''; ?>">
                <a href="saved_suppliers.php" class="sidebar-link">
                    <i class="fa-solid fa-truck-fast text-accent-turquoise"></i>
                    <span>Fornecedores Salvos</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'saved_products.php' ? 'active' : ''; ?>">
                <a href="saved_products.php" class="sidebar-link">
                    <i class="fa-regular fa-heart text-danger"></i>
                    <span>Produtos Salvos & IA</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'investment_advisor.php' ? 'active' : ''; ?>">
                <a href="investment_advisor.php" class="sidebar-link">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                    <span>Consultor de Investimento BR</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="index.php#alerts-section" class="sidebar-link">
                    <i class="fa-regular fa-bell"></i>
                    <span>Alertas Monitorados</span>
                </a>
            </li>
            
            <!-- Google Trends RSS Sidebar Widget (Directly under Alertas) -->
            <li class="mt-4 px-2">
                <div class="p-3 rounded border border-light-subtle" style="background-color: rgba(255, 255, 255, 0.01);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-fire text-danger me-2"></i>
                            <h6 class="mb-0 text-white fw-bold" style="font-size: 13px;">Google Trends BR</h6>
                        </div>
                        <button onclick="refreshTrends()" class="btn btn-link p-0 text-muted hover-accent-turquoise" title="Atualizar Tendências" style="font-size: 11px; text-decoration: none;"><i class="fa-solid fa-arrows-rotate"></i></button>
                    </div>
                    <ul id="google-trends-list" class="list-unstyled p-0 m-0" style="font-size: 11px;">
                        <!-- Dynamically populated -->
                        <div class="text-center py-2"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>
                    </ul>
                </div>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center text-truncate">
                    <div class="bg-purple-glow text-accent-purple rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: bold;">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="text-truncate">
                        <div class="fw-semibold text-white small text-truncate"><?php echo htmlspecialchars($user['name']); ?></div>
                        <small class="text-muted" style="font-size: 10px;"><?php echo htmlspecialchars($user['role']); ?></small>
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
                    <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">Análise e Inteligência de Mercado</h5>
                    <span class="text-muted small d-none d-md-block">Pesquise produtos em múltiplos marketplaces brasileiros em tempo real.</span>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Theme Toggle -->
                <button id="theme-toggle-btn" class="btn btn-outline-secondary border-light-subtle me-2 me-md-3" style="border-radius: 10px; width: 42px; height: 42px; padding: 0;">
                    <?php if ($darkMode): ?>
                        <i class="fa-solid fa-sun"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-moon"></i>
                    <?php endif; ?>
                </button>

                <div class="border-start border-light-subtle h-25 mx-1 mx-md-2 align-self-center d-none d-sm-block"></div>
                <span class="badge bg-success-glow text-success border border-success-subtle p-2 ms-1 ms-md-2 d-none d-sm-inline-block"><i class="fa-solid fa-shield-halved me-1"></i> Plesk Compátivel</span>
            </div>
        </header>
        
        <main class="content-body">
            <!-- Dynamic Alert banners will slide here if triggered alerts exist -->
            <div id="alert-notification-banners"></div>
