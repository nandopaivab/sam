<?php
/**
 * TrendHunter Brasil - Saved Suppliers Directory
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

use TrendHunter\Database;

$db = Database::getConnection();

// Retrieve all saved suppliers for the logged-in user
$stmt = $db->prepare("SELECT * FROM saved_suppliers WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$savedSuppliers = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-white mb-1"><i class="fa-solid fa-truck-fast text-accent-turquoise me-2"></i> Seus Fornecedores Salvos</h2>
        <p class="text-muted">Lista com contatos, endereços, CNPJ, margens projetadas e informações úteis dos distribuidores de atacado salvos por você.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card-premium p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-address-book text-accent-purple me-2"></i> Diretório de Contatos B2B</h5>
                <span class="badge bg-purple-glow text-accent-purple"><?php echo count($savedSuppliers); ?> Fornecedores</span>
            </div>

            <?php if (empty($savedSuppliers)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-truck-ramp-box text-muted fs-1 mb-3"></i>
                    <h6 class="text-white">Nenhum fornecedor salvo</h6>
                    <p class="text-muted small">Para salvar, faça uma varredura de produtos, clique em **Buscar Fornecedores** (🚚) em qualquer produto e depois clique em **Salvar Forn.**.</p>
                    <a href="index.php" class="btn btn-sm btn-turquoise mt-2"><i class="fa-solid fa-magnifying-glass me-1"></i> Ir para Pesquisas</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-premium" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Fornecedor</th>
                                <th>Contato & Localização</th>
                                <th>Notas / CNPJ</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($savedSuppliers as $s): 
                                $searchQuery = 'Acessórios';
                                $nameLower = mb_strtolower($s['name']);
                                if (str_contains($nameLower, 'brinquedos') || str_contains($nameLower, 'nipo')) {
                                    $searchQuery = 'Brinquedos';
                                } elseif (str_contains($nameLower, 'malu') || str_contains($nameLower, 'roupas') || str_contains($nameLower, 'bras')) {
                                    $searchQuery = 'Moda';
                                } elseif (str_contains($nameLower, 'tronic') || str_contains($nameLower, 'xiaomi') || str_contains($nameLower, 'tech')) {
                                    $searchQuery = 'Smartwatch';
                                } elseif (str_contains($nameLower, 'maquiagem') || str_contains($nameLower, 'cosméticos')) {
                                    $searchQuery = 'Maquiagem';
                                } elseif (str_contains($nameLower, 'utilidades') || str_contains($nameLower, 'casa') || str_contains($nameLower, 'home')) {
                                    $searchQuery = 'Utilidades';
                                }
                            ?>
                                <tr class="supplier-card-row" id="supplier-row-<?php echo $s['id']; ?>">
                                    <td>
                                        <strong class="text-white"><?php echo htmlspecialchars($s['name']); ?></strong>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($s['type']); ?></small>
                                    </td>
                                    <td>
                                        <div style="font-size: 11px; line-height: 1.5;">
                                            <span class="text-white d-block"><i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo htmlspecialchars($s['address'] ?? 'Não informado'); ?></span>
                                            <span class="text-accent-turquoise d-block mt-1"><i class="fa-solid fa-phone text-success me-1"></i> <?php echo htmlspecialchars($s['phone'] ?? 'Não informado'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-warning" style="font-size:11px;"><?php echo htmlspecialchars($s['notes'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 align-items-center">
                                            <a href="index.php?query=<?php echo urlencode($searchQuery); ?>" class="btn btn-sm btn-outline-turquoise py-1 px-2 fw-semibold d-inline-flex align-items-center" style="font-size: 11px; border-radius: 6px;" title="Ver Catálogo / Produtos do Nicho">
                                                <i class="fa-solid fa-magnifying-glass me-1" style="font-size: 9px;"></i> Catálogo
                                            </a>
                                            <a href="<?php echo htmlspecialchars($s['url'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-outline-info py-1 px-2" title="Visitar Site do Fornecedor" style="font-size:11px; border-radius: 6px;"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                            <button onclick="deleteSavedSupplier(<?php echo $s['id']; ?>, this)" class="btn btn-sm btn-outline-danger py-1 px-2" title="Remover Fornecedor" style="font-size:11px; border-radius: 6px;"><i class="fa-regular fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.deleteSavedSupplier = function(id, btnElement) {
    if (!confirm('Deseja realmente remover este fornecedor salvo das suas listas?')) return;
    
    const row = $(btnElement).closest('.supplier-card-row');
    $(btnElement).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    
    $.post('api.php?action=delete_saved_supplier', { id: id }, function(response) {
        if (response.success) {
            row.fadeOut(300, function() {
                $(this).remove();
                if ($('.supplier-card-row').length === 0) {
                    location.reload(); // Reload to show empty state
                }
            });
        } else {
            $(btnElement).prop('disabled', false).html('<i class="fa-regular fa-trash-can"></i>');
            alert(response.error || 'Erro ao remover fornecedor.');
        }
    }).fail(function() {
        $(btnElement).prop('disabled', false).html('<i class="fa-regular fa-trash-can"></i>');
        alert('Erro de conexão com o servidor.');
    });
};
</script>

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<!-- Include Layout Footer -->
<?php require __DIR__ . '/templates/footer.php'; ?>
