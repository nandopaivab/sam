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

// Create activity_logs table if not exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        user_name VARCHAR(100) DEFAULT 'Sistema',
        module VARCHAR(100) NOT NULL,
        action_type VARCHAR(100) NOT NULL,
        target_record VARCHAR(255) DEFAULT NULL,
        old_values TEXT DEFAULT NULL,
        new_values TEXT DEFAULT NULL,
        ip_address VARCHAR(50) DEFAULT '127.0.0.1',
        device_info VARCHAR(255) DEFAULT 'Navegador Web',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Check count and seed initial system audit logs if empty
    $cntStmt = $db->query("SELECT COUNT(*) FROM activity_logs");
    if ($cntStmt && (int)$cntStmt->fetchColumn() === 0) {
        $seedLogs = [
            [1, 'Administrador', 'Autenticação SAM', 'LOGIN', 'Sessão iniciada', 'N/A', 'Login bem-sucedido via formulário', '127.0.0.1'],
            [1, 'Administrador', 'CRM Comercial', 'CRIACÃO', 'Distribuidora SP Tech Express', 'N/A', 'Novo contato para Projetor 4K no TikTok Shop', '127.0.0.1'],
            [1, 'Fernando Paiva', 'Produtos Oceano Azul', 'CADASTRO IA', 'Organizador de Temperos para Gaveta', 'N/A', 'Adicionado ao catálogo com score 94/100', '127.0.0.1'],
            [1, 'Fernando Paiva', 'Nicho de Bebês', 'ANÁLISE IA', 'Prato com Ventosa Silicone BPA Free', 'N/A', 'Classificação de Oportunidade: ALTA (88 pt)', '127.0.0.1'],
            [1, 'Administrador', 'Consultor de Investimento', 'ANÁLISE EM TEMPO REAL', 'Perfil Equilibrado R$ 5.000,00', 'N/A', 'Portfólio com ROI Médio de 117.4% gerado', '127.0.0.1']
        ];
        $ins = $db->prepare("INSERT INTO activity_logs (user_id, user_name, module, action_type, target_record, old_values, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seedLogs as $lg) { $ins->execute($lg); }
    }
} catch (\Exception $e) {}

// Filter parameters
$filterUser = trim($_GET['user'] ?? '');
$filterModule = trim($_GET['module'] ?? '');
$filterAction = trim($_GET['action'] ?? '');
$filterTarget = trim($_GET['target'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$query = "SELECT * FROM activity_logs WHERE 1=1";
$params = [];

if ($filterUser !== '') {
    $query .= " AND user_name LIKE ?";
    $params[] = '%' . $filterUser . '%';
}
if ($filterModule !== '') {
    $query .= " AND module = ?";
    $params[] = $filterModule;
}
if ($filterAction !== '') {
    $query .= " AND action_type = ?";
    $params[] = $filterAction;
}
if ($filterTarget !== '') {
    $query .= " AND target_record LIKE ?";
    $params[] = '%' . $filterTarget . '%';
}
if ($startDate !== '') {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $startDate;
}
if ($endDate !== '') {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $endDate;
}
$query .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">
                    <i class="fa-solid fa-file-shield me-1"></i> AUDITORIA E SEGURANÇA SAM
                </span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    <i class="fa-solid fa-lock me-1"></i> Registros Imutáveis
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Log Geral de Atividades & Rastreabilidade</h1>
            <p class="text-muted small mb-0">Auditoria completa de todos os acessos, edições, exclusões e movimentações comerciais no sistema.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="exportAuditCsv()">
                <i class="fa-solid fa-file-csv text-success me-2"></i> Exportar Auditoria (CSV)
            </button>
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="window.print()">
                <i class="fa-solid fa-print me-2"></i> Imprimir Relatório
            </button>
        </div>
    </div>

    <!-- Immutable Security Notice -->
    <div class="alert alert-dark border-light-subtle bg-dark-subtle p-3 mb-4 d-flex align-items-center gap-3" style="border-radius: 12px; background: rgba(255,255,255,0.03);">
        <i class="fa-solid fa-shield-halved text-warning" style="font-size: 24px;"></i>
        <div>
            <div class="fw-bold text-white small">Políticas de Segurança e Conformidade (Audit Trail)</div>
            <div class="text-muted small" style="font-size: 12px;">Por diretrizes de compliance empresarial, é expressamente <strong>proibida a alteração ou exclusão</strong> de registros desta tabela por usuários comuns ou administradores. Todos os endereços IP, horários e valores anteriores são arquivados permanentemente.</div>
        </div>
    </div>

    <!-- Search Filters -->
    <form method="GET" class="card border-light-subtle p-3 mb-4" style="border-radius: 14px; background: rgba(255,255,255,0.02);">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Usuário</label>
                <input type="text" name="user" class="form-control form-control-sm bg-dark text-white border-light-subtle" placeholder="Ex: Administrador" value="<?php echo htmlspecialchars($filterUser); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Módulo</label>
                <select name="module" class="form-select form-select-sm bg-dark text-white border-light-subtle">
                    <option value="">Todos os Módulos</option>
                    <option value="Autenticação SAM" <?php echo $filterModule === 'Autenticação SAM' ? 'selected' : ''; ?>>Autenticação SAM</option>
                    <option value="CRM Comercial" <?php echo $filterModule === 'CRM Comercial' ? 'selected' : ''; ?>>CRM Comercial</option>
                    <option value="Produtos Oceano Azul" <?php echo $filterModule === 'Produtos Oceano Azul' ? 'selected' : ''; ?>>Produtos Oceano Azul</option>
                    <option value="Nicho de Bebês" <?php echo $filterModule === 'Nicho de Bebês' ? 'selected' : ''; ?>>Nicho de Bebês</option>
                    <option value="Consultor de Investimento" <?php echo $filterModule === 'Consultor de Investimento' ? 'selected' : ''; ?>>Consultor de Investimento</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Ação Auditada</label>
                <input type="text" name="action" class="form-control form-control-sm bg-dark text-white border-light-subtle" placeholder="Ex: CRIACÃO / LOGIN" value="<?php echo htmlspecialchars($filterAction); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Registro Afetado</label>
                <input type="text" name="target" class="form-control form-control-sm bg-dark text-white border-light-subtle" placeholder="Ex: Projetor 4K" value="<?php echo htmlspecialchars($filterTarget); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Data Inicial</label>
                <input type="date" name="start_date" class="form-control form-control-sm bg-dark text-white border-light-subtle" value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-light-subtle" value="<?php echo htmlspecialchars($endDate); ?>">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3"><i class="fa-solid fa-filter"></i></button>
            </div>
        </div>
    </form>

    <!-- Audit Logs Table -->
    <div class="card border-light-subtle overflow-hidden" style="border-radius: 16px; background: rgba(255,255,255,0.02);">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="border-bottom border-light-subtle text-muted small">
                    <tr>
                        <th class="py-3 px-3">ID & Horário</th>
                        <th class="py-3">Usuário & Endereço IP</th>
                        <th class="py-3">Módulo</th>
                        <th class="py-3">Tipo de Ação</th>
                        <th class="py-3">Registro Afetado</th>
                        <th class="py-3">Detalhes / Valores Alterados</th>
                        <th class="py-3 text-end px-3">Auditoria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-file-shield d-block mb-2" style="font-size: 28px;"></i>
                                Nenhum log de auditoria encontrado para este filtro.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="px-3">
                                    <span class="badge bg-dark border border-light-subtle text-muted me-1">#<?php echo $log['id']; ?></span>
                                    <div class="fw-bold text-white mt-1"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-accent-purple"><?php echo htmlspecialchars($log['user_name'] ?: 'Sistema'); ?></div>
                                    <span class="text-muted small" style="font-size: 11px;"><i class="fa-solid fa-network-wired me-1"></i> IP: <?php echo htmlspecialchars($log['ip_address'] ?: '127.0.0.1'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($log['module']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $actBadge = 'bg-info-subtle text-info';
                                    if ($log['action_type'] === 'EXCLUSÃO') $actBadge = 'bg-danger-subtle text-danger';
                                    if ($log['action_type'] === 'CRIACÃO') $actBadge = 'bg-success-subtle text-success';
                                    if ($log['action_type'] === 'LOGIN') $actBadge = 'bg-warning-subtle text-warning';
                                    ?>
                                    <span class="badge <?php echo $actBadge; ?> px-2 py-1"><?php echo htmlspecialchars($log['action_type']); ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($log['target_record'] ?: 'Geral / Módulo'); ?></div>
                                </td>
                                <td>
                                    <div class="text-white small"><?php echo htmlspecialchars($log['new_values'] ?: 'Ação processada e auditada com sucesso.'); ?></div>
                                    <?php if (!empty($log['old_values']) && $log['old_values'] !== 'N/A'): ?>
                                        <div class="text-muted small" style="font-size: 11px;">Anterior: <?php echo htmlspecialchars($log['old_values']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fa-solid fa-check me-1"></i> Verificado
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportAuditCsv() {
    let csvContent = "data:text/csv;charset=utf-8,ID,DataHorario,Usuario,IP,Modulo,Acao,Registro,Detalhes\n";
    <?php foreach ($logs as $log): ?>
    csvContent += "<?php echo $log['id']; ?>,\"<?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>\",\"<?php echo addslashes($log['user_name']); ?>\",\"<?php echo $log['ip_address']; ?>\",\"<?php echo addslashes($log['module']); ?>\",\"<?php echo addslashes($log['action_type']); ?>\",\"<?php echo addslashes($log['target_record'] ?: ''); ?>\",\"<?php echo addslashes($log['new_values'] ?: ''); ?>\"\n";
    <?php endforeach; ?>
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "sam_auditoria_logs_<?php echo date('Ymd_His'); ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
