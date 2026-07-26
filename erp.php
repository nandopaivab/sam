<?php
/**
 * TrendHunter Brasil - ERP Controle de Estoque & Vendas
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

$db = \TrendHunter\Database::getConnection();
$userId = 1; // Default session user

// 1. Fetch ERP Products
$stmtProds = $db->prepare("SELECT * FROM erp_products WHERE user_id = ? ORDER BY title ASC");
$stmtProds->execute([$userId]);
$products = $stmtProds->fetchAll();

// 2. Fetch ERP Sales
$stmtSales = $db->prepare("
    SELECT s.*, p.title as product_title, p.sku as product_sku, p.cost_price 
    FROM erp_sales s
    JOIN erp_products p ON s.product_id = p.id
    WHERE s.user_id = ? 
    ORDER BY s.sale_date DESC
");
$stmtSales->execute([$userId]);
$sales = $stmtSales->fetchAll();

// 3. Calculate Stats
$totalProducts = count($products);
$totalStockUnits = 0;
$totalStockCostValue = 0.0;
$totalStockSaleValue = 0.0;
$lowStockAlerts = 0;

foreach ($products as $p) {
    $qty = (int)$p['stock_quantity'];
    $totalStockUnits += $qty;
    $totalStockCostValue += ($p['cost_price'] * $qty);
    $totalStockSaleValue += ($p['selling_price'] * $qty);
    if ($qty <= (int)$p['min_stock']) {
        $lowStockAlerts++;
    }
}

$totalSalesCount = count($sales);
$totalRevenue = 0.0;
$totalNetProfit = 0.0;

foreach ($sales as $s) {
    $totalRevenue += (float)$s['total_amount'];
    $profitUnit = (float)$s['sale_price'] - (float)$s['cost_price'];
    $totalNetProfit += ($profitUnit * (int)$s['quantity']);
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="fw-bold text-white mb-1"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> ERP - Controle de Estoque & Vendas</h2>
                <p class="text-muted mb-0">Gerencie seu catálogo de produtos próprios, controle o estoque físico e registre vendas especificando os canais de venda.</p>
            </div>
            <div class="my-2">
                <button class="btn btn-primary bg-gradient border-0 px-4 fw-bold" style="background-color: #745df7;" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProductModal()">
                    <i class="fa-solid fa-plus me-2"></i> Novo Produto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ERP Dashboard Stats Grid -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card-premium metric-card purple p-3">
            <div class="metric-title"><i class="fa-solid fa-box text-accent-purple me-1"></i> Itens Cadastrados</div>
            <div class="metric-value"><?php echo $totalProducts; ?></div>
            <div class="small text-muted mt-1">Total unidades físicas: <?php echo $totalStockUnits; ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card-premium metric-card turquoise p-3">
            <div class="metric-title"><i class="fa-solid fa-dollar-sign text-accent-turquoise me-1"></i> Valor de Estoque (Custo)</div>
            <div class="metric-value">R$ <?php echo number_format($totalStockCostValue, 2, ',', '.'); ?></div>
            <div class="small text-accent-turquoise mt-1">Valor Venda: R$ <?php echo number_format($totalStockSaleValue, 2, ',', '.'); ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card-premium metric-card yellow p-3">
            <div class="metric-title"><i class="fa-solid fa-cart-shopping text-warning me-1"></i> Faturamento Geral</div>
            <div class="metric-value">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></div>
            <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>Lucro Líquido: R$ <?php echo number_format($totalNetProfit, 2, ',', '.'); ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card-premium metric-card p-3 <?php echo $lowStockAlerts > 0 ? 'border-danger' : ''; ?>" style="background: <?php echo $lowStockAlerts > 0 ? 'linear-gradient(135deg, rgba(220, 53, 69, 0.08) 0%, rgba(11, 12, 22, 0.5) 100%)' : 'rgba(255,255,255,0.01)'; ?>;">
            <div class="metric-title text-danger"><i class="fa-solid fa-circle-exclamation text-danger me-1"></i> Alertas de Estoque Mínimo</div>
            <div class="metric-value <?php echo $lowStockAlerts > 0 ? 'text-danger' : 'text-white'; ?>"><?php echo $lowStockAlerts; ?></div>
            <div class="small text-muted mt-1"><?php echo $lowStockAlerts > 0 ? 'Reponha estes produtos o quanto antes.' : 'Todos os estoques normalizados.'; ?></div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs premium-tabs mb-4" id="erpTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock-panel" type="button" role="tab"><i class="fa-solid fa-boxes-stacked me-1"></i> Estoque & Catálogo</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="emit-tab" data-bs-toggle="tab" data-bs-target="#emit-panel" type="button" role="tab"><i class="fa-solid fa-cart-plus me-1"></i> Registrar Venda</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-panel" type="button" role="tab"><i class="fa-solid fa-receipt me-1"></i> Histórico de Vendas</button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content text-white" id="erpTabsContent">
    
    <!-- Tab 1: Stock & Products Catalog -->
    <div class="tab-pane fade show active" id="stock-panel" role="tabpanel" aria-labelledby="stock-tab">
        <div class="card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-accent-purple me-2"></i> Lista de Produtos Disponíveis</h5>
            
            <div class="table-responsive">
                <table class="table-premium" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Título do Produto</th>
                            <th>Preço Custo</th>
                            <th>Preço Venda</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Status</th>
                            <th style="width: 150px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fs-2 mb-3"></i>
                                    <h5>Nenhum produto cadastrado no ERP</h5>
                                    <p class="small text-muted mb-0">Use o botão "Novo Produto" no topo para iniciar seu cadastro.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): 
                                $isLow = $p['stock_quantity'] <= $p['min_stock'];
                                ?>
                                <tr>
                                    <td class="fw-bold text-metrify-cyan"><?php echo htmlspecialchars((string)$p['sku']); ?></td>
                                    <td><?php echo htmlspecialchars((string)$p['title']); ?></td>
                                    <td>R$ <?php echo number_format((float)$p['cost_price'], 2, ',', '.'); ?></td>
                                    <td class="fw-bold text-white">R$ <?php echo number_format((float)$p['selling_price'], 2, ',', '.'); ?></td>
                                    <td class="fw-bold <?php echo $isLow ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo $p['stock_quantity']; ?> unidades
                                    </td>
                                    <td class="text-muted"><?php echo $p['min_stock']; ?> un.</td>
                                    <td>
                                        <?php if ($isLow): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Estoque Baixo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-info me-1" onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-xs btn-outline-danger" onclick="deleteProduct(<?php echo $p['id']; ?>, '<?php echo addslashes((string)$p['title']); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Emit Sale Form -->
    <div class="tab-pane fade" id="emit-panel" role="tabpanel" aria-labelledby="emit-tab">
        <div class="row">
            <div class="col-12 col-lg-6 mx-auto">
                <div class="card-premium p-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-cart-plus text-warning me-2"></i> Emitir Registro de Venda</h5>
                    <p class="text-muted small mb-4">Selecione o produto cadastrado, a quantidade vendida e o marketplace/canal para atualizar o estoque físico em tempo real.</p>
                    
                    <?php if (empty($products)): ?>
                        <div class="text-center py-4 text-warning">
                            <i class="fa-solid fa-triangle-exclamation fs-3 mb-2"></i>
                            <p class="mb-0">Cadastre pelo menos um produto no ERP antes de emitir uma venda.</p>
                        </div>
                    <?php else: ?>
                        <form id="emitSaleForm" onsubmit="emitSale(event)">
                            <div class="mb-3">
                                <label for="sale-product-id" class="form-label text-muted small fw-bold">Produto Vendido</label>
                                <select id="sale-product-id" class="form-select bg-dark text-white border-light-subtle" required onchange="updateSaleProductDetails(this)">
                                    <option value="" disabled selected>Selecione o produto...</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['selling_price']; ?>" data-stock="<?php echo $p['stock_quantity']; ?>">
                                            <?php echo htmlspecialchars((string)$p['title']); ?> (SKU: <?php echo htmlspecialchars((string)$p['sku']); ?>) - Estoque: <?php echo $p['stock_quantity']; ?> un.
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="sale-quantity" class="form-label text-muted small fw-bold">Quantidade Vendida</label>
                                    <input type="number" id="sale-quantity" class="form-control bg-dark text-white border-light-subtle" value="1" min="1" required oninput="calculateSaleTotal()">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sale-price" class="form-label text-muted small fw-bold">Preço de Venda Unitário (R$)</label>
                                    <input type="number" id="sale-price" step="0.01" class="form-control bg-dark text-white border-light-subtle" required oninput="calculateSaleTotal()">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold d-block">Canal / Plataforma de Venda</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="radio" class="btn-check" name="platform" id="plat-ml" value="mercadolivre" checked>
                                    <label class="btn btn-outline-secondary border-light-subtle text-white flex-grow-1" for="plat-ml">
                                        <i class="fa-solid fa-chart-pie text-success me-1"></i> Mercado Livre
                                    </label>

                                    <input type="radio" class="btn-check" name="platform" id="plat-shopee" value="shopee">
                                    <label class="btn btn-outline-secondary border-light-subtle text-white flex-grow-1" for="plat-shopee">
                                        <i class="fa-solid fa-bag-shopping text-danger me-1"></i> Shopee
                                    </label>

                                    <input type="radio" class="btn-check" name="platform" id="plat-tiktok" value="tiktok">
                                    <label class="btn btn-outline-secondary border-light-subtle text-white flex-grow-1" for="plat-tiktok">
                                        <i class="fa-brands fa-tiktok text-info me-1"></i> TikTok Shop
                                    </label>

                                    <input type="radio" class="btn-check" name="platform" id="plat-outros" value="outros">
                                    <label class="btn btn-outline-secondary border-light-subtle text-white flex-grow-1" for="plat-outros">
                                        <i class="fa-solid fa-globe text-warning me-1"></i> Loja / Outros
                                    </label>
                                </div>
                            </div>

                            <div class="p-3 mb-4 rounded border border-light-subtle bg-dark bg-opacity-50 text-center">
                                <div class="text-muted small">Total da Venda</div>
                                <h3 class="fw-bold text-accent-turquoise mb-0" id="sale-total-display">R$ 0,00</h3>
                            </div>

                            <button type="submit" class="btn btn-success bg-gradient w-100 py-3 fw-bold border-0">
                                <i class="fa-solid fa-check me-2"></i> Emitir Registro e Atualizar Estoque
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Sales History Log -->
    <div class="tab-pane fade" id="history-panel" role="tabpanel" aria-labelledby="history-tab">
        <div class="card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-receipt text-accent-turquoise me-2"></i> Histórico Geral de Vendas Emitidas</h5>
            
            <div class="table-responsive">
                <table class="table-premium" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Data/Hora</th>
                            <th>Produto</th>
                            <th>Plataforma</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Total Venda</th>
                            <th>Lucro Líquido</th>
                            <th style="width: 100px;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-receipt fs-2 mb-3"></i>
                                    <h5>Nenhuma venda registrada até o momento</h5>
                                    <p class="small text-muted mb-0">Seus registros de vendas aparecerão aqui após serem emitidos.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $s): 
                                $profitUnit = (float)$s['sale_price'] - (float)$s['cost_price'];
                                $totalProfit = $profitUnit * (int)$s['quantity'];
                                
                                // Platform badges details
                                $platformsInfo = [
                                    'mercadolivre' => ['label' => 'Mercado Livre', 'class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'fa-chart-pie text-success'],
                                    'shopee' => ['label' => 'Shopee', 'class' => 'bg-danger-subtle text-danger border-danger-subtle', 'icon' => 'fa-bag-shopping text-danger'],
                                    'tiktok' => ['label' => 'TikTok Shop', 'class' => 'bg-info-subtle text-info border-info-subtle', 'icon' => 'fa-brands fa-tiktok text-info'],
                                    'outros' => ['label' => 'Loja / Outros', 'class' => 'bg-warning-subtle text-warning border-warning-subtle', 'icon' => 'fa-globe text-warning']
                                ];
                                $plat = $platformsInfo[$s['platform']] ?? $platformsInfo['outros'];
                                ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?php echo $s['id']; ?></td>
                                    <td style="font-size: 11px;"><?php echo date('d/m/Y H:i', strtotime($s['sale_date'])); ?></td>
                                    <td>
                                        <div class="fw-bold text-white"><?php echo htmlspecialchars((string)$s['product_title']); ?></div>
                                        <small class="text-muted">SKU: <?php echo htmlspecialchars((string)$s['product_sku']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $plat['class']; ?> border px-2 py-1 small">
                                            <i class="fa-solid <?php echo $plat['icon']; ?> me-1"></i> <?php echo $plat['label']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-white"><?php echo $s['quantity']; ?> un.</td>
                                    <td>R$ <?php echo number_format((float)$s['sale_price'], 2, ',', '.'); ?></td>
                                    <td class="fw-bold text-accent-turquoise">R$ <?php echo number_format((float)$s['total_amount'], 2, ',', '.'); ?></td>
                                    <td class="fw-bold text-success">R$ <?php echo number_format($totalProfit, 2, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-danger" onclick="cancelSale(<?php echo $s['id']; ?>, '<?php echo addslashes((string)$s['product_title']); ?>')">
                                            <i class="fa-solid fa-xmark me-1"></i> Estornar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add/Edit Product -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-light-subtle" style="background-color: #12131a; border-radius: 14px;">
            <div class="modal-header border-bottom border-light-subtle border-opacity-10">
                <h5 class="modal-title fw-bold text-white" id="productModalLabel">Cadastrar Produto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm" onsubmit="saveProduct(event)">
                <input type="hidden" id="prod-id" value="0">
                <div class="modal-body text-white">
                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label for="prod-sku" class="form-label text-muted small fw-bold">SKU / Código</label>
                            <input type="text" id="prod-sku" class="form-control bg-dark text-white border-light-subtle" required placeholder="EX: FONE-XYZ">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="prod-title" class="form-label text-muted small fw-bold">Título do Produto</label>
                            <input type="text" id="prod-title" class="form-control bg-dark text-white border-light-subtle" required placeholder="EX: Fone Bluetooth Pro Gamer">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="prod-cost" class="form-label text-muted small fw-bold">Preço de Custo (R$)</label>
                            <input type="number" id="prod-cost" step="0.01" class="form-control bg-dark text-white border-light-subtle" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prod-selling" class="form-label text-muted small fw-bold">Preço de Venda (R$)</label>
                            <input type="number" id="prod-selling" step="0.01" class="form-control bg-dark text-white border-light-subtle" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="prod-stock" class="form-label text-muted small fw-bold">Estoque Inicial (Unidades)</label>
                            <input type="number" id="prod-stock" class="form-control bg-dark text-white border-light-subtle" required placeholder="0" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prod-min" class="form-label text-muted small fw-bold">Mínimo de Alerta</label>
                            <input type="number" id="prod-min" class="form-control bg-dark text-white border-light-subtle" value="5" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-light-subtle border-opacity-10">
                    <button type="button" class="btn btn-outline-secondary border-light-subtle text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary bg-gradient border-0 px-4 fw-bold" style="background-color: #745df7;">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddProductModal() {
    $('#productModalLabel').text('Cadastrar Produto');
    $('#prod-id').val(0);
    $('#prod-sku').val('');
    $('#prod-title').val('');
    $('#prod-cost').val('');
    $('#prod-selling').val('');
    $('#prod-stock').val('');
    $('#prod-min').val(5);
}

function openEditProductModal(product) {
    $('#productModalLabel').text('Editar Produto');
    $('#prod-id').val(product.id);
    $('#prod-sku').val(product.sku);
    $('#prod-title').val(product.title);
    $('#prod-cost').val(product.cost_price);
    $('#prod-selling').val(product.selling_price);
    $('#prod-stock').val(product.stock_quantity);
    $('#prod-min').val(product.min_stock);

    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

function saveProduct(event) {
    event.preventDefault();
    const id = $('#prod-id').val();
    const sku = $('#prod-sku').val().trim();
    const title = $('#prod-title').val().trim();
    const cost = $('#prod-cost').val();
    const selling = $('#prod-selling').val();
    const stock = $('#prod-stock').val();
    const min = $('#prod-min').val();

    const formData = new FormData();
    formData.append('action', 'save_erp_product');
    formData.append('id', id);
    formData.append('sku', sku);
    formData.append('title', title);
    formData.append('cost_price', cost);
    formData.append('selling_price', selling);
    formData.append('stock_quantity', stock);
    formData.append('min_stock', min);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Produto salvo com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar produto: ' + (data.error || 'Erro desconhecido.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao conectar-se com o servidor.');
    });
}

function deleteProduct(id, title) {
    if (!confirm('Deseja realmente excluir o produto "' + title + '"? Todas as vendas associadas a ele também serão removidas.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_erp_product');
    formData.append('id', id);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Produto excluído com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao excluir produto.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao conectar-se com o servidor.');
    });
}

function updateSaleProductDetails(select) {
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    $('#sale-price').val(price);
    calculateSaleTotal();
}

function calculateSaleTotal() {
    const qty = parseInt($('#sale-quantity').val()) || 1;
    const price = parseFloat($('#sale-price').val()) || 0.0;
    const total = qty * price;
    $('#sale-total-display').text('R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

function emitSale(event) {
    event.preventDefault();
    const productId = $('#sale-product-id').val();
    const qty = $('#sale-quantity').val();
    const price = $('#sale-price').val();
    const platform = $('input[name="platform"]:checked').val();

    if (!productId) {
        alert('Por favor, selecione um produto.');
        return;
    }

    const selectedOption = document.getElementById('sale-product-id').options[document.getElementById('sale-product-id').selectedIndex];
    const availableStock = parseInt(selectedOption.getAttribute('data-stock'));

    if (parseInt(qty) > availableStock) {
        alert('Estoque insuficiente para esta venda! Estoque disponível: ' + availableStock + ' unidades.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'emit_erp_sale');
    formData.append('product_id', productId);
    formData.append('quantity', qty);
    formData.append('sale_price', price);
    formData.append('platform', platform);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Venda emitida e estoque atualizado com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao emitir venda: ' + (data.error || 'Erro desconhecido.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao conectar-se com o servidor.');
    });
}

function cancelSale(id, title) {
    if (!confirm('Deseja realmente estornar a venda #' + id + ' do produto "' + title + '"? A quantidade vendida retornará ao estoque.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_erp_sale');
    formData.append('id', id);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Venda estornada com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao estornar venda.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao conectar-se com o servidor.');
    });
}
</script>

<?php require __DIR__ . '/templates/footer.php'; ?>
