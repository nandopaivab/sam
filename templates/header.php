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
                    <span>Consultor de Investimento</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'crm.php' ? 'active' : ''; ?>">
                <a href="crm.php" class="sidebar-link" style="border-left: 3px solid #745df7;">
                    <i class="fa-solid fa-handshake-angle text-accent-purple"></i>
                    <span>CRM Comercial & Pipeline</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'blue_ocean.php' ? 'active' : ''; ?>">
                <a href="blue_ocean.php" class="sidebar-link">
                    <i class="fa-solid fa-water text-primary"></i>
                    <span>Produtos Oceano Azul</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'baby_niche.php' ? 'active' : ''; ?>">
                <a href="baby_niche.php" class="sidebar-link">
                    <i class="fa-solid fa-baby text-accent-turquoise"></i>
                    <span>Bebês & Primeira Infância</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'profit_calculator.php' ? 'active' : ''; ?>">
                <a href="profit_calculator.php" class="sidebar-link">
                    <i class="fa-solid fa-calculator text-success"></i>
                    <span>Calculadora de Lucro</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'audit_logs.php' ? 'active' : ''; ?>">
                <a href="audit_logs.php" class="sidebar-link">
                    <i class="fa-solid fa-file-shield text-warning"></i>
                    <span>Log Geral de Auditoria</span>
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
            
                <!-- Notification Bell Dropdown -->
                <div class="dropdown me-2 me-md-3">
                    <button class="btn btn-outline-secondary border-light-subtle position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; width: 42px; height: 42px; padding: 0;">
                        <i class="fa-solid fa-bell text-warning"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            3
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-light-subtle p-2" style="width: 320px; border-radius: 14px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom border-light-subtle d-flex justify-content-between align-items-center">
                            <span class="fw-bold small"><i class="fa-solid fa-bell text-warning me-1"></i> Central de Notificações</span>
                            <span class="badge bg-primary-subtle text-primary small">3 Novos</span>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="crm.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-purple-glow text-accent-purple me-2"><i class="fa-solid fa-handshake-angle"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">4 Follow-ups Pendentes</div>
                                        <div class="text-muted" style="font-size: 11px;">Acesse a agenda de contatos do CRM.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="blue_ocean.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-primary-subtle text-primary me-2"><i class="fa-solid fa-water"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">Oportunidades Oceano Azul</div>
                                        <div class="text-muted" style="font-size: 11px;">Organizadores com margem > 140%.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2 rounded my-1" href="baby_niche.php">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded bg-success-subtle text-success me-2"><i class="fa-solid fa-baby"></i></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">Kits Bebês & Infância</div>
                                        <div class="text-muted" style="font-size: 11px;">IA gerou 3 novos kits acessíveis.</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="border-start border-light-subtle h-25 mx-1 mx-md-2 align-self-center d-none d-sm-block"></div>

                <!-- Connected User Profile Panel Dropdown -->
                <div class="dropdown ms-1 ms-md-2">
                    <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 12px;">
                        <div class="rounded-circle bg-purple-glow text-accent-purple d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: 700;">
                            <?php echo strtoupper(substr($user['name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="text-start d-none d-md-block me-2">
                            <div class="fw-bold lh-1 text-white" style="font-size: 13px;">
                                <?php echo htmlspecialchars($user['name'] ?? 'Administrador'); ?>
                            </div>
                            <div class="text-muted" style="font-size: 11px;">
                                <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'Administrador')); ?> • SAM BR
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-light-subtle p-2" style="width: 280px; border-radius: 14px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom border-light-subtle mb-2">
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($user['name'] ?? 'Administrador'); ?></div>
                            <div class="text-muted small"><i class="fa-solid fa-building me-1"></i> SAM - E-commerce BR</div>
                            <div class="text-muted small mt-1" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i> Último login: <?php echo date('d/m/Y H:i'); ?></div>
                        </li>
                        <li>
                            <a class="dropdown-item rounded py-2 d-flex align-items-center" href="#" onclick="alert('Perfil de Administrador Ativo: Permissão Total aos Módulos SAM.'); return false;">
                                <i class="fa-solid fa-user me-2 text-accent-purple"></i> Meu Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded py-2 d-flex align-items-center" href="#" onclick="alert('Função de Segurança: Utilize o painel administrativo para redefinição de senha.'); return false;">
                                <i class="fa-solid fa-key me-2 text-info"></i> Alterar Senha
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
