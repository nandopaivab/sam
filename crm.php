<?php
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

// Create CRM tables if not exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS crm_activities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        contact_type VARCHAR(50) NOT NULL,
        contact_date DATE NOT NULL,
        contact_time TIME NOT NULL,
        responsible_name VARCHAR(100) NOT NULL,
        objective VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        result_summary TEXT DEFAULT NULL,
        negotiation_status VARCHAR(50) NOT NULL,
        interest_level VARCHAR(50) DEFAULT 'Alto',
        priority VARCHAR(50) DEFAULT 'Média',
        next_action VARCHAR(255) DEFAULT NULL,
        followup_date DATE DEFAULT NULL,
        product_id INTEGER DEFAULT NULL,
        product_title VARCHAR(255) DEFAULT NULL,
        marketplace VARCHAR(100) DEFAULT NULL,
        attachment_url VARCHAR(500) DEFAULT NULL,
        created_by_name VARCHAR(100) DEFAULT 'Sistema / Usuário',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");
} catch (\Exception $e) {}

// Fetch filter parameters
$filterCompany = trim($_GET['company'] ?? '');
$filterMarketplace = trim($_GET['marketplace'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$filterResponsible = trim($_GET['responsible'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

// Build query for activities
$query = "SELECT * FROM crm_activities WHERE 1=1";
$params = [];

if ($filterCompany !== '') {
    $query .= " AND company_name LIKE ?";
    $params[] = '%' . $filterCompany . '%';
}
if ($filterMarketplace !== '') {
    $query .= " AND marketplace = ?";
    $params[] = $filterMarketplace;
}
if ($filterStatus !== '') {
    $query .= " AND negotiation_status = ?";
    $params[] = $filterStatus;
}
if ($filterResponsible !== '') {
    $query .= " AND responsible_name LIKE ?";
    $params[] = '%' . $filterResponsible . '%';
}
if ($startDate !== '') {
    $query .= " AND contact_date >= ?";
    $params[] = $startDate;
}
if ($endDate !== '') {
    $query .= " AND contact_date <= ?";
    $params[] = $endDate;
}
$query .= " ORDER BY contact_date DESC, contact_time DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate KPIs
$today = date('Y-m-d');
$weekAgo = date('Y-m-d', strtotime('-7 days'));
$monthAgo = date('Y-m-d', strtotime('-30 days'));

$todayContacts = 0;
$weekContacts = 0;
$monthContacts = 0;
$activeNegotiations = 0;
$pendingFollowups = 0;
$approvedOrPublished = 0;
$totalCount = count($activities);

foreach ($activities as $act) {
    if ($act['contact_date'] === $today) $todayContacts++;
    if ($act['contact_date'] >= $weekAgo) $weekContacts++;
    if ($act['contact_date'] >= $monthAgo) $monthContacts++;
    
    if (in_array($act['negotiation_status'], ['Descoberta', 'Contato com Fornecedor', 'Negociação', 'Amostra Solicitada'])) {
        $activeNegotiations++;
    }
    if ($act['followup_date'] && $act['followup_date'] >= $today && $act['negotiation_status'] !== 'Publicado') {
        $pendingFollowups++;
    }
    if (in_array($act['negotiation_status'], ['Aprovado', 'Publicado'])) {
        $approvedOrPublished++;
    }
}

$conversionRate = $totalCount > 0 ? round(($approvedOrPublished / $totalCount) * 100, 1) : 0;

// Group activities by status for Kanban Board
$kanbanColumns = [
    'Descoberta' => ['title' => '1. Descoberta & Oportunidade', 'color' => '#00d2ff', 'icon' => 'fa-lightbulb'],
    'Contato com Fornecedor' => ['title' => '2. Contato com Fornecedor', 'color' => '#745df7', 'icon' => 'fa-phone-volume'],
    'Negociação' => ['title' => '3. Cotação & Negociação', 'color' => '#ffb703', 'icon' => 'fa-handshake'],
    'Amostra Solicitada' => ['title' => '4. Amostras & Testes', 'color' => '#fb8500', 'icon' => 'fa-box-open'],
    'Aprovado' => ['title' => '5. Aprovado & Compra', 'color' => '#2ec4b6', 'icon' => 'fa-check-double'],
    'Publicado' => ['title' => '6. Publicado & Escala', 'color' => '#06d6a0', 'icon' => 'fa-rocket']
];

$kanbanData = [];
foreach (array_keys($kanbanColumns) as $status) {
    $kanbanData[$status] = [];
}
foreach ($activities as $act) {
    $st = $act['negotiation_status'];
    if (isset($kanbanData[$st])) {
        $kanbanData[$st][] = $act;
    } else {
        $kanbanData['Descoberta'][] = $act;
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-purple-glow text-accent-purple border border-purple-subtle px-3 py-1">
                    <i class="fa-solid fa-handshake-angle me-1"></i> CRM COMERCIAL SAM
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> Pipeline Ativo
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Gestão Comercial & Pipeline de Produtos</h1>
            <p class="text-muted small mb-0">Acompanhe todas as interações com fornecedores e o ciclo cronológico de cada produto viral do e-commerce.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="exportCrmExcel()">
                <i class="fa-solid fa-file-excel text-success me-2"></i> Exportar Excel
            </button>
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="window.print()">
                <i class="fa-solid fa-file-pdf text-danger me-2"></i> Relatório PDF
            </button>
            <button class="btn btn-primary px-4 fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#crmActivityModal" onclick="resetActivityForm()">
                <i class="fa-solid fa-plus me-2"></i> Nova Atividade / Contato
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom border-light-subtle pb-3" id="crmTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 rounded-pill fw-bold d-flex align-items-center" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab">
                <i class="fa-solid fa-chart-pie me-2"></i> Dashboard de Produtividade
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-pill fw-bold d-flex align-items-center" id="kanban-tab" data-bs-toggle="tab" data-bs-target="#kanban-pane" type="button" role="tab">
                <i class="fa-solid fa-kanban me-2"></i> Pipeline Kanban (<?php echo $totalCount; ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-pill fw-bold d-flex align-items-center" id="agenda-tab" data-bs-toggle="tab" data-bs-target="#agenda-pane" type="button" role="tab">
                <i class="fa-solid fa-calendar-check me-2"></i> Agenda & Histórico Auditável
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-pill fw-bold d-flex align-items-center" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks-pane" type="button" role="tab">
                <i class="fa-solid fa-list-check me-2"></i> Tarefas Comerciais (Agenda Diária)
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="crmTabsContent">
        
        <!-- 1. DASHBOARD TAB -->
        <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel">
            <!-- KPI Cards Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card h-100 border-light-subtle bg-dark-subtle p-3" style="border-radius: 14px; background: rgba(255,255,255,0.03);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">CONTATOS REALIZADOS (MÊS)</span>
                            <div class="p-2 rounded bg-purple-glow text-accent-purple"><i class="fa-solid fa-phone-volume"></i></div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold text-white mb-0"><?php echo $monthContacts; ?></h3>
                            <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-up"></i> Hoje: <?php echo $todayContacts; ?></span>
                        </div>
                        <div class="text-muted small mt-2" style="font-size: 11px;">Semana atual: <strong><?php echo $weekContacts; ?></strong> interações</div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card h-100 border-light-subtle bg-dark-subtle p-3" style="border-radius: 14px; background: rgba(255,255,255,0.03);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">NEGOCIAÇÕES EM ANDAMENTO</span>
                            <div class="p-2 rounded bg-warning-subtle text-warning"><i class="fa-solid fa-handshake"></i></div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold text-white mb-0"><?php echo $activeNegotiations; ?></h3>
                            <span class="badge bg-warning-subtle text-warning small">Ativas</span>
                        </div>
                        <div class="text-muted small mt-2" style="font-size: 11px;">Em Descoberta, Cotação e Amostra</div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card h-100 border-light-subtle bg-dark-subtle p-3" style="border-radius: 14px; background: rgba(255,255,255,0.03);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">FOLLOW-UPS PENDENTES</span>
                            <div class="p-2 rounded bg-danger-subtle text-danger"><i class="fa-solid fa-bell"></i></div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold text-white mb-0"><?php echo $pendingFollowups; ?></h3>
                            <span class="text-danger small fw-bold"><i class="fa-solid fa-clock"></i> Atenção</span>
                        </div>
                        <div class="text-muted small mt-2" style="font-size: 11px;">Retornos agendados e próximos passos</div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card h-100 border-light-subtle bg-dark-subtle p-3" style="border-radius: 14px; background: rgba(255,255,255,0.03);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">TAXA DE CONVERSÃO</span>
                            <div class="p-2 rounded bg-success-subtle text-success"><i class="fa-solid fa-chart-line"></i></div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold text-white mb-0"><?php echo $conversionRate; ?>%</h3>
                            <span class="badge bg-success-subtle text-success small"><?php echo $approvedOrPublished; ?> Aprovados</span>
                        </div>
                        <div class="text-muted small mt-2" style="font-size: 11px;">Média de aprovação no pipeline</div>
                    </div>
                </div>
            </div>

            <!-- Charts & Performance Analysis Row -->
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-light-subtle p-4" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-white mb-0"><i class="fa-solid fa-ranking-star text-accent-purple me-2"></i> Desempenho por Etapa do Pipeline</h5>
                            <span class="text-muted small">Atualizado em tempo real</span>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($kanbanColumns as $status => $col): 
                                $cnt = count($kanbanData[$status]);
                                $pct = $totalCount > 0 ? round(($cnt / $totalCount) * 100) : 0;
                            ?>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.02);">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold small" style="color: <?php echo $col['color']; ?>;">
                                                <i class="fa-solid <?php echo $col['icon']; ?> me-1"></i> <?php echo $col['title']; ?>
                                            </span>
                                            <span class="badge bg-dark-subtle text-white"><?php echo $cnt; ?> produtos</span>
                                        </div>
                                        <div class="progress" style="height: 8px; border-radius: 4px; background: rgba(255,255,255,0.08);">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $pct; ?>%; background-color: <?php echo $col['color']; ?>;"></div>
                                        </div>
                                        <div class="text-end text-muted mt-1" style="font-size: 10px;"><?php echo $pct; ?>% das negociações</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-light-subtle p-4 h-100" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
                        <h5 class="fw-bold text-white mb-3"><i class="fa-solid fa-store text-accent-turquoise me-2"></i> Origem por Marketplace</h5>
                        <?php
                        $mpCounts = [];
                        foreach ($activities as $act) {
                            $mp = !empty($act['marketplace']) ? $act['marketplace'] : 'Outro';
                            $mpCounts[$mp] = ($mpCounts[$mp] ?? 0) + 1;
                        }
                        ?>
                        <ul class="list-group list-group-flush border-0">
                            <?php foreach ($mpCounts as $mp => $count): 
                                $mpPct = $totalCount > 0 ? round(($count / $totalCount) * 100) : 0;
                            ?>
                                <li class="list-group-item bg-transparent text-white px-0 d-flex justify-content-between align-items-center border-bottom border-light-subtle">
                                    <div>
                                        <span class="fw-bold small"><?php echo htmlspecialchars($mp); ?></span>
                                        <div class="progress mt-1" style="width: 120px; height: 5px; border-radius: 3px;">
                                            <div class="progress-bar bg-accent-turquoise" style="width: <?php echo $mpPct; ?>%;"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold"><?php echo $count; ?> (<?php echo $mpPct; ?>%)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. KANBAN PIPELINE TAB -->
        <div class="tab-pane fade" id="kanban-pane" role="tabpanel">
            <div class="row g-3 flex-nowrap overflow-auto pb-4" style="min-height: 550px;">
                <?php foreach ($kanbanColumns as $status => $col): 
                    $items = $kanbanData[$status];
                ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3" style="min-width: 280px;">
                        <div class="card border-light-subtle h-100" style="border-radius: 14px; background: rgba(255,255,255,0.02);">
                            <div class="card-header border-bottom border-light-subtle p-3 d-flex justify-content-between align-items-center" style="border-top: 3px solid <?php echo $col['color']; ?>; border-radius: 12px 12px 0 0;">
                                <div class="fw-bold text-white small">
                                    <i class="fa-solid <?php echo $col['icon']; ?> me-1" style="color: <?php echo $col['color']; ?>;"></i>
                                    <?php echo $col['title']; ?>
                                </div>
                                <span class="badge bg-dark border border-light-subtle text-white"><?php echo count($items); ?></span>
                            </div>
                            
                            <div class="card-body p-2 overflow-auto" style="max-height: 520px;">
                                <?php if (empty($items)): ?>
                                    <div class="text-center text-muted p-4 small">
                                        <i class="fa-regular fa-folder-open d-block mb-1" style="font-size: 20px;"></i>
                                        Nenhuma atividade nesta etapa
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($items as $act): ?>
                                        <div class="card border-light-subtle mb-2 p-3 hover-lift shadow-sm" style="border-radius: 12px; background: rgba(255,255,255,0.04);">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-purple-glow text-accent-purple small" style="font-size: 10px;">
                                                    <?php echo htmlspecialchars($act['marketplace'] ?: 'Geral'); ?>
                                                </span>
                                                <div class="dropdown">
                                                    <button class="btn btn-link p-0 text-muted" type="button" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-light-subtle small">
                                                        <?php foreach (array_keys($kanbanColumns) as $targetSt): ?>
                                                            <?php if ($targetSt !== $status): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="#" onclick="updateActivityStatus(<?php echo $act['id']; ?>, '<?php echo $targetSt; ?>'); return false;">
                                                                        Mover para: <?php echo $targetSt; ?>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>

                                            <h6 class="fw-bold text-white mb-1" style="font-size: 13px;">
                                                <?php echo htmlspecialchars($act['product_title'] ?: $act['objective']); ?>
                                            </h6>
                                            <div class="text-muted small mb-2" style="font-size: 11px;">
                                                <i class="fa-solid fa-building me-1 text-accent-turquoise"></i> <strong><?php echo htmlspecialchars($act['company_name']); ?></strong>
                                            </div>

                                            <p class="text-muted small mb-2 text-truncate" style="font-size: 11px;">
                                                <?php echo htmlspecialchars($act['description']); ?>
                                            </p>

                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle mt-2" style="font-size: 11px;">
                                                <span class="text-muted">
                                                    <i class="fa-regular fa-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($act['contact_date'])); ?>
                                                </span>
                                                <span class="badge <?php echo ($act['interest_level'] === 'Alto') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted'; ?>" style="font-size: 10px;">
                                                    Interesse: <?php echo htmlspecialchars($act['interest_level'] ?: 'Alto'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. AGENDA & AUDIT HISTORY TAB -->
        <div class="tab-pane fade" id="agenda-pane" role="tabpanel">
            <!-- Filter Bar -->
            <form method="GET" class="card border-light-subtle p-3 mb-4" style="border-radius: 14px; background: rgba(255,255,255,0.02);">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Empresa / Fornecedor</label>
                        <input type="text" name="company" class="form-control form-control-sm bg-dark text-white border-light-subtle" placeholder="Buscar empresa..." value="<?php echo htmlspecialchars($filterCompany); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Marketplace</label>
                        <select name="marketplace" class="form-select form-select-sm bg-dark text-white border-light-subtle">
                            <option value="">Todos</option>
                            <option value="Mercado Livre" <?php echo $filterMarketplace === 'Mercado Livre' ? 'selected' : ''; ?>>Mercado Livre</option>
                            <option value="Shopee" <?php echo $filterMarketplace === 'Shopee' ? 'selected' : ''; ?>>Shopee</option>
                            <option value="TikTok Shop" <?php echo $filterMarketplace === 'TikTok Shop' ? 'selected' : ''; ?>>TikTok Shop</option>
                            <option value="Amazon" <?php echo $filterMarketplace === 'Amazon' ? 'selected' : ''; ?>>Amazon</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Etapa / Status</label>
                        <select name="status" class="form-select form-select-sm bg-dark text-white border-light-subtle">
                            <option value="">Todas as Etapas</option>
                            <?php foreach (array_keys($kanbanColumns) as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo $filterStatus === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Data Início</label>
                        <input type="date" name="start_date" class="form-control form-control-sm bg-dark text-white border-light-subtle" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Data Fim</label>
                        <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-light-subtle" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-12 col-md-1 d-grid">
                        <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="fa-solid fa-filter"></i></button>
                    </div>
                </div>
            </form>

            <!-- Table of Activities -->
            <div class="card border-light-subtle overflow-hidden" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
                <div class="table-responsive">
                    <table class="table table-hover table-dark align-middle mb-0" style="font-size: 13px;">
                        <thead class="border-bottom border-light-subtle text-muted small">
                            <tr>
                                <th class="py-3 px-3">Data & Hora</th>
                                <th class="py-3">Empresa / Fornecedor</th>
                                <th class="py-3">Produto & Marketplace</th>
                                <th class="py-3">Tipo & Objetivo</th>
                                <th class="py-3">Status da Negociação</th>
                                <th class="py-3">Próximo Follow-up</th>
                                <th class="py-3 text-end px-3">Ações Auditáveis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activities)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-regular fa-folder-open d-block mb-2" style="font-size: 28px;"></i>
                                        Nenhuma atividade encontrada com os filtros selecionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activities as $act): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="fw-bold text-white"><?php echo date('d/m/Y', strtotime($act['contact_date'])); ?></div>
                                            <div class="text-muted small" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i> <?php echo substr($act['contact_time'], 0, 5); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-accent-turquoise"><?php echo htmlspecialchars($act['company_name']); ?></div>
                                            <div class="text-muted small" style="font-size: 11px;">Resp: <?php echo htmlspecialchars($act['responsible_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white"><?php echo htmlspecialchars($act['product_title'] ?: 'Geral / Múltiplos'); ?></div>
                                            <span class="badge bg-dark-subtle text-muted small"><?php echo htmlspecialchars($act['marketplace'] ?: 'Brasil'); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white"><span class="badge bg-primary-subtle text-primary me-1"><?php echo htmlspecialchars($act['contact_type']); ?></span> <?php echo htmlspecialchars($act['objective']); ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;" title="<?php echo htmlspecialchars($act['description']); ?>">
                                                <?php echo htmlspecialchars($act['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeColor = 'bg-info-subtle text-info';
                                            if ($act['negotiation_status'] === 'Aprovado') $badgeColor = 'bg-success-subtle text-success';
                                            if ($act['negotiation_status'] === 'Publicado') $badgeColor = 'bg-success text-white';
                                            if ($act['negotiation_status'] === 'Amostra Solicitada') $badgeColor = 'bg-warning-subtle text-warning';
                                            ?>
                                            <span class="badge <?php echo $badgeColor; ?> px-2 py-1"><?php echo htmlspecialchars($act['negotiation_status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($act['followup_date'])): ?>
                                                <div class="fw-bold text-warning small"><i class="fa-regular fa-calendar-check me-1"></i> <?php echo date('d/m/Y', strtotime($act['followup_date'])); ?></div>
                                                <div class="text-muted small text-truncate" style="max-width: 150px; font-size: 11px;"><?php echo htmlspecialchars($act['next_action']); ?></div>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end px-3">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary border-light-subtle" onclick='editActivity(<?php echo json_encode($act); ?>)' title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger border-light-subtle" onclick="deleteActivity(<?php echo $act['id']; ?>)" title="Excluir">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. TASKS COMMERCIAL SCHEDULE TAB -->
        <div class="tab-pane fade" id="tasks-pane" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-light-subtle p-4" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-white mb-0"><i class="fa-solid fa-list-check text-accent-purple me-2"></i> Agenda Comercial Diária & Tarefas (Follow-ups)</h5>
                            <span class="badge bg-warning-subtle text-warning"><?php echo $pendingFollowups; ?> Follow-ups</span>
                        </div>
                        <div class="list-group list-group-flush border-0">
                            <?php 
                            $hasTasks = false;
                            foreach ($activities as $act):
                                if (!empty($act['followup_date'])):
                                    $hasTasks = true;
                                    $isOverdue = ($act['followup_date'] < $today);
                                    $isToday = ($act['followup_date'] === $today);
                            ?>
                                    <div class="list-group-item bg-transparent text-white px-3 py-3 border-bottom border-light-subtle d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input me-3" type="checkbox" onchange="markTaskComplete(this, <?php echo $act['id']; ?>)">
                                            <div>
                                                <div class="fw-bold d-flex align-items-center gap-2">
                                                    <?php echo htmlspecialchars($act['next_action'] ?: 'Follow-up comercial'); ?>
                                                    <?php if ($isOverdue): ?>
                                                        <span class="badge bg-danger small">Atrasado</span>
                                                    <?php elseif ($isToday): ?>
                                                        <span class="badge bg-success small">Hoje</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted small mt-1" style="font-size: 12px;">
                                                    <i class="fa-solid fa-building text-accent-turquoise me-1"></i> <strong><?php echo htmlspecialchars($act['company_name']); ?></strong> — <?php echo htmlspecialchars($act['product_title'] ?: 'Produto não especificado'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold small text-warning"><i class="fa-regular fa-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($act['followup_date'])); ?></div>
                                            <span class="text-muted small" style="font-size: 11px;">Resp: <?php echo htmlspecialchars($act['responsible_name']); ?></span>
                                        </div>
                                    </div>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$hasTasks):
                            ?>
                                <div class="text-center text-muted p-5 small">
                                    <i class="fa-solid fa-check-double text-success d-block mb-2" style="font-size: 28px;"></i>
                                    Parabéns! Todas as tarefas e follow-ups estão em dia.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-light-subtle p-4" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
                        <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-bullseye text-success me-2"></i> Meta de Conversão Semanal</h6>
                        <div class="text-center py-3">
                            <h2 class="fw-bold text-white mb-1"><?php echo $approvedOrPublished; ?> / 10</h2>
                            <p class="text-muted small">Produtos aprovados para venda este mês</p>
                            <div class="progress mt-3" style="height: 10px; border-radius: 5px; background: rgba(255,255,255,0.08);">
                                <div class="progress-bar bg-success" style="width: <?php echo min(100, ($approvedOrPublished / 10) * 100); ?>%;"></div>
                            </div>
                        </div>
                        <hr class="border-light-subtle my-3">
                        <h6 class="fw-bold text-white mb-2 small"><i class="fa-solid fa-lightbulb text-warning me-1"></i> Dicas de Gestão no CRM SAM:</h6>
                        <ul class="text-muted small ps-3 mb-0" style="font-size: 12px; line-height: 1.6;">
                            <li>Sempre anexe cotações e vídeos dos fornecedores na descrição.</li>
                            <li>Defina uma data de follow-up específica para cada negociação.</li>
                            <li>Use o Kanban arrastando ou selecionando a próxima etapa até a publicação.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL: Create / Edit CRM Activity -->
<div class="modal fade" id="crmActivityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-light-subtle shadow-lg" style="border-radius: 16px; background: #181920;">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-handshake-angle text-accent-purple me-2"></i> <span id="modalTitle">Nova Atividade Comercial</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="crmForm">
                    <input type="hidden" id="actId" name="id" value="">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">Empresa / Fornecedor *</label>
                            <input type="text" id="actCompany" name="company_name" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Mega Atacadista SP">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Responsável *</label>
                            <input type="text" id="actResponsible" name="responsible_name" class="form-control bg-dark text-white border-light-subtle" required value="<?php echo htmlspecialchars($user['name'] ?? 'Fernando Paiva'); ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Tipo de Contato *</label>
                            <select id="actType" name="contact_type" class="form-select bg-dark text-white border-light-subtle">
                                <option value="WhatsApp">WhatsApp</option>
                                <option value="Ligação">Ligação</option>
                                <option value="E-mail">E-mail</option>
                                <option value="Reunião">Reunião (Vídeo/Presencial)</option>
                                <option value="Visita Técnica">Visita Técnica</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Marketplace Relacionado</label>
                            <select id="actMarketplace" name="marketplace" class="form-select bg-dark text-white border-light-subtle">
                                <option value="Shopee">Shopee</option>
                                <option value="Mercado Livre">Mercado Livre</option>
                                <option value="TikTok Shop">TikTok Shop</option>
                                <option value="Amazon">Amazon</option>
                                <option value="Múltiplos">Múltiplos</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Status da Negociação *</label>
                            <select id="actStatus" name="negotiation_status" class="form-select bg-dark text-white border-light-subtle">
                                <?php foreach (array_keys($kanbanColumns) as $st): ?>
                                    <option value="<?php echo $st; ?>"><?php echo $st; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Produto Viral Relacionado</label>
                            <input type="text" id="actProduct" name="product_title" class="form-control bg-dark text-white border-light-subtle" placeholder="Ex: Projetor Inteligente 4K">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Data do Contato *</label>
                            <input type="date" id="actDate" name="contact_date" class="form-control bg-dark text-white border-light-subtle" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Horário *</label>
                            <input type="time" id="actTime" name="contact_time" class="form-control bg-dark text-white border-light-subtle" required value="<?php echo date('H:i'); ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Nível de Interesse</label>
                            <select id="actInterest" name="interest_level" class="form-select bg-dark text-white border-light-subtle">
                                <option value="Alto">Alto Interesse</option>
                                <option value="Médio">Médio</option>
                                <option value="Baixo">Baixo</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Prioridade</label>
                            <select id="actPriority" name="priority" class="form-select bg-dark text-white border-light-subtle">
                                <option value="Alta">Alta</option>
                                <option value="Média" selected>Média</option>
                                <option value="Baixa">Baixa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Objetivo do Contato *</label>
                        <input type="text" id="actObjective" name="objective" class="form-control bg-dark text-white border-light-subtle" required placeholder="Ex: Verificar disponibilidade de lote e tabela progressiva de desconto">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Descrição Completa da Conversa *</label>
                        <textarea id="actDescription" name="description" class="form-control bg-dark text-white border-light-subtle" rows="3" required placeholder="Detalhe os pontos discutidos, valores propostos e acordos firmados..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Resultado Obtido</label>
                        <textarea id="actResult" name="result_summary" class="form-control bg-dark text-white border-light-subtle" rows="2" placeholder="Ex: Fornecedor confirmou estoque em SP e ofereceu frete grátis para lote acima de 100 un."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">Próxima Ação Comercial</label>
                            <input type="text" id="actNextAction" name="next_action" class="form-control bg-dark text-white border-light-subtle" placeholder="Ex: Solicitar envio de amostra e contrato de fornecimento">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">Data de Follow-up</label>
                            <input type="date" id="actFollowup" name="followup_date" class="form-control bg-dark text-white border-light-subtle" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Anexo / Link de Proposta (Catálogo, Imagem, NF ou Planilha)</label>
                        <input type="text" id="actAttachment" name="attachment_url" class="form-control bg-dark text-white border-light-subtle" placeholder="URL ou referência de documento...">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-light-subtle p-3">
                <button type="button" class="btn btn-outline-secondary border-light-subtle" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveCrmActivity()">
                    <i class="fa-solid fa-check me-1"></i> Salvar Atividade Auditada
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetActivityForm() {
    document.getElementById('crmForm').reset();
    document.getElementById('actId').value = '';
    document.getElementById('modalTitle').innerText = 'Nova Atividade Comercial';
}

function editActivity(act) {
    document.getElementById('actId').value = act.id;
    document.getElementById('actCompany').value = act.company_name || '';
    document.getElementById('actResponsible').value = act.responsible_name || '';
    document.getElementById('actType').value = act.contact_type || 'WhatsApp';
    document.getElementById('actMarketplace').value = act.marketplace || 'Shopee';
    document.getElementById('actStatus').value = act.negotiation_status || 'Descoberta';
    document.getElementById('actProduct').value = act.product_title || '';
    document.getElementById('actDate').value = act.contact_date || '';
    document.getElementById('actTime').value = act.contact_time || '';
    document.getElementById('actInterest').value = act.interest_level || 'Alto';
    document.getElementById('actPriority').value = act.priority || 'Média';
    document.getElementById('actObjective').value = act.objective || '';
    document.getElementById('actDescription').value = act.description || '';
    document.getElementById('actResult').value = act.result_summary || '';
    document.getElementById('actNextAction').value = act.next_action || '';
    document.getElementById('actFollowup').value = act.followup_date || '';
    document.getElementById('actAttachment').value = act.attachment_url || '';

    document.getElementById('modalTitle').innerText = 'Editar Atividade (Auditoria Ativa)';
    var myModal = new bootstrap.Modal(document.getElementById('crmActivityModal'));
    myModal.show();
}

function saveCrmActivity() {
    const form = document.getElementById('crmForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'save_crm_activity');

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao salvar atividade: ' + (data.error || 'Erro desconhecido.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro de comunicação com o servidor.');
    });
}

function deleteActivity(id) {
    if (!confirm('Tem certeza que deseja excluir este registro? A ação será mantida no log auditável do sistema.')) return;

    const formData = new FormData();
    formData.append('action', 'delete_crm_activity');
    formData.append('id', id);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao excluir: ' + (data.error || 'Erro desconhecido.'));
        }
    });
}

function updateActivityStatus(id, newStatus) {
    const formData = new FormData();
    formData.append('action', 'update_crm_status');
    formData.append('id', id);
    formData.append('status', newStatus);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao atualizar status: ' + (data.error || 'Erro desconhecido'));
        }
    });
}

function markTaskComplete(chk, id) {
    if (chk.checked) {
        if (confirm('Marcar este follow-up como concluído?')) {
            updateActivityStatus(id, 'Publicado');
        } else {
            chk.checked = false;
        }
    }
}

function exportCrmExcel() {
    window.location.href = 'api.php?action=export_crm_excel';
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
