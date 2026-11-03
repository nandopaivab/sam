<?php
/**
 * TrendHunter Brasil - Consultor IA de Investimentos & Estoque BR
 * Orientação automatizada de compras com fornecedores nacionais baseada em orçamento
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

require_once __DIR__ . '/templates/header.php';

use TrendHunter\Auth;

$currentUser = Auth::getCurrentUser();
?>

<!-- Custom CSS for Investment Advisor -->
<style>
.invest-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(99, 102, 241, 0.1) 100%);
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 16px;
    padding: 32px;
    position: relative;
    overflow: hidden;
}
.invest-hero::after {
    content: "";
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
    z-index: 0;
    pointer-events: none;
}
.quick-btn {
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.25s ease;
}
.quick-btn:hover, .quick-btn.active {
    background: rgba(16, 185, 129, 0.25);
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}
.kpi-card-invest {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 22px;
    transition: transform 0.25s ease, border-color 0.25s ease;
}
.kpi-card-invest:hover {
    border-color: rgba(16, 185, 129, 0.4);
    transform: translateY(-3px);
}
.tag-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tag-core { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
.tag-viral { background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
.tag-cash { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.tag-innovate { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
</style>

<div class="container-fluid py-4">
    <!-- Hero Header -->
    <div class="invest-hero mb-4">
        <div class="row align-items-center position-relative" style="z-index: 1;">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-bold me-2">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> INTELIGÊNCIA ARTIFICIAL BR
                    </span>
                    <span class="text-muted small">Atualizado com Fornecedores Nacionais e Alta Saída</span>
                </div>
                <h2 class="fw-bolder text-white mb-2">Consultor IA de Compras & Investimentos BR</h2>
                <p class="text-light text-opacity-75 mb-0" style="font-size: 15px; max-width: 680px;">
                    Defina quanto você deseja investir em estoque. Nossa IA analisa os produtos com <strong>maior volume de vendas e lucratividade</strong> e cria uma estratégia de alocação de portfólio com <strong>fornecedores verificados no Brasil</strong>.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-light-subtle d-inline-block text-start">
                    <div class="small text-muted mb-1"><i class="fa-solid fa-shield-halved text-success me-1"></i> Estoque Nacional (BR)</div>
                    <div class="fw-bold text-white small mb-0">Zero taxa de importação & Entrega 2 a 5 dias</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Config Form -->
    <div class="card-premium mb-4">
        <div class="card-body p-4">
            <form id="investmentForm" onsubmit="generateInvestmentPlan(event)">
                <div class="row g-4 align-items-center">
                    <!-- Budget Input -->
                    <div class="col-lg-5">
                        <label class="form-label text-white fw-bold mb-2">
                            <i class="fa-solid fa-wallet text-success me-2"></i>Limite de Gastos / Orçamento (R$)
                        </label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text bg-dark border-end-0 text-success fw-bold">R$</span>
                            <input type="number" class="form-control bg-dark border-start-0 text-white fw-bold" id="budgetInput" name="budget" value="5000" min="100" step="50" required style="font-size: 20px;">
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="quick-btn" onclick="setQuickBudget(1000)">R$ 1.000</button>
                            <button type="button" class="quick-btn" onclick="setQuickBudget(3000)">R$ 3.000</button>
                            <button type="button" class="quick-btn active" onclick="setQuickBudget(5000)">R$ 5.000</button>
                            <button type="button" class="quick-btn" onclick="setQuickBudget(10000)">R$ 10.000</button>
                            <button type="button" class="quick-btn" onclick="setQuickBudget(25000)">R$ 25.000</button>
                        </div>
                    </div>

                    <!-- Risk Profile -->
                    <div class="col-lg-4">
                        <label class="form-label text-white fw-bold mb-2">
                            <i class="fa-solid fa-chart-line text-accent-purple me-2"></i>Perfil de Margem & Risco
                        </label>
                        <select class="form-select form-select-lg bg-dark text-white border-light-subtle" id="profileSelect" name="profile">
                            <option value="conservador">🛡️ Conservador - Giro Constante & Baixo Risco</option>
                            <option value="equilibrado" selected>⚖️ Equilibrado - Mix Vencedor (Mais Recomendado)</option>
                            <option value="agressivo">🚀 Agressivo - Virais Explosivos TikTok Shop</option>
                        </select>
                        <div class="small text-muted mt-2">
                            <i class="fa-solid fa-circle-info text-accent-turquoise me-1"></i> Define o equilíbrio entre produtos de catálogo firme e apostas virais.
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="col-lg-3 text-lg-end mt-4 mt-lg-auto">
                        <button type="submit" class="btn btn-lg btn-success w-100 fw-bold py-3 shadow-lg" id="btnGenerate" style="border-radius: 12px;">
                            <i class="fa-solid fa-brain me-2"></i>Gerar Plano com IA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading State -->
    <div id="planLoading" class="text-center py-5 d-none">
        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Calculando...</span>
        </div>
        <h5 class="text-white mt-3 fw-bold">Consultando Fornecedores Brasileiros e Algoritmos de Tendência...</h5>
        <p class="text-muted small">Alocando seu orçamento para máxima lucratividade com frete nacional rápido.</p>
    </div>

    <!-- Plan Results Section -->
    <div id="planResults" class="d-none">
        <!-- 4 KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="kpi-card-invest">
                    <div class="small text-muted fw-semibold mb-1">CAPITAL ALOCADO</div>
                    <h3 class="fw-bolder text-white mb-0" id="resInvested">R$ 0,00</h3>
                    <div class="small text-success mt-2"><i class="fa-solid fa-check-circle me-1"></i>100% em Estoque Naciona BR</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="kpi-card-invest">
                    <div class="small text-muted fw-semibold mb-1">FATURAMENTO PROJETADO</div>
                    <h3 class="fw-bolder text-accent-turquoise mb-0" id="resRevenue">R$ 0,00</h3>
                    <div class="small text-muted mt-2">Venda varejo sugerida</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="kpi-card-invest">
                    <div class="small text-muted fw-semibold mb-1">LUCRO LÍQUIDO ESTIMADO</div>
                    <h3 class="fw-bolder text-success mb-0" id="resProfit">R$ 0,00</h3>
                    <div class="small text-success mt-2"><i class="fa-solid fa-arrow-trend-up me-1"></i>Após custo do produto</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="kpi-card-invest">
                    <div class="small text-muted fw-semibold mb-1">ROI MÉDIO & GIRO</div>
                    <h3 class="fw-bolder text-warning mb-0" id="resRoi">0%</h3>
                    <div class="small text-muted mt-2" id="resTurnover">Giro: 15 a 25 dias</div>
                </div>
            </div>
        </div>

        <!-- Portfolio Table Card -->
        <div class="card-premium mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-boxes-stacked text-success me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-white">Plano de Compra Recomendado — Fornecedores Brasil</h5>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> Imprimir Plano PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportPlanCsv()">
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar Tabela
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" id="portfolioTable">
                        <thead class="bg-dark text-muted small text-uppercase">
                            <tr>
                                <th class="py-3 ps-4">Produto & Estratégia</th>
                                <th class="py-3 text-center">Preço Atacado</th>
                                <th class="py-3 text-center">Quantidade</th>
                                <th class="py-3 text-center">Total Investido</th>
                                <th class="py-3 text-center">Preço Venda</th>
                                <th class="py-3 text-center">Lucro Estimado</th>
                                <th class="py-3 text-center">ROI</th>
                                <th class="py-3 pe-4">Fornecedor BR & Prazo</th>
                            </tr>
                        </thead>
                        <tbody id="portfolioBody" class="border-top-0">
                            <!-- Populated dynamically by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Strategy Marketing & Sales Tips Card -->
        <div class="card-premium">
            <div class="card-header">
                <h5 class="mb-0 fw-bold text-white">
                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>Diretrizes Estratégicas de Vendas com IA
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3" id="strategyContainer">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPlanData = null;

function setQuickBudget(amount) {
    document.getElementById('budgetInput').value = amount;
    document.querySelectorAll('.quick-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function formatBRL(val) {
    return 'R$ ' + parseFloat(val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getTagBadgeClass(tag) {
    if (tag.includes('Carro-Chefe')) return 'tag-core';
    if (tag.includes('Tendência') || tag.includes('Viral')) return 'tag-viral';
    if (tag.includes('Caixa') || tag.includes('Baixo')) return 'tag-cash';
    return 'tag-innovate';
}

function generateInvestmentPlan(e) {
    if (e) e.preventDefault();
    
    const budget = document.getElementById('budgetInput').value;
    const profile = document.getElementById('profileSelect').value;
    const btn = document.getElementById('btnGenerate');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Calculando...';

    document.getElementById('planResults').classList.add('d-none');
    document.getElementById('planLoading').classList.remove('d-none');

    $.ajax({
        url: 'api.php',
        method: 'POST',
        data: {
            action: 'generate_investment_plan',
            budget: budget,
            profile: profile
        },
        dataType: 'json',
        success: function(resp) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-brain me-2"></i>Gerar Plano com IA';
            document.getElementById('planLoading').classList.add('d-none');

            if (resp && resp.success && resp.plan) {
                currentPlanData = resp.plan;
                renderInvestmentPlan(resp.plan);
            } else {
                alert('Erro ao gerar plano de investimento: ' + (resp.error || 'Erro desconhecido'));
            }
        },
        error: function(xhr) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-brain me-2"></i>Gerar Plano com IA';
            document.getElementById('planLoading').classList.add('d-none');
            alert('Falha na comunicação com a API de investimentos.');
        }
    });
}

function renderInvestmentPlan(plan) {
    // 1. KPI cards
    document.getElementById('resInvested').textContent = formatBRL(plan.total_invested);
    document.getElementById('resRevenue').textContent = formatBRL(plan.total_revenue);
    document.getElementById('resProfit').textContent = formatBRL(plan.total_profit);
    document.getElementById('resRoi').textContent = plan.average_roi + '%';
    document.getElementById('resTurnover').textContent = 'Giro Estimado: ' + plan.turnover_days;

    // 2. Table rows
    const tbody = document.getElementById('portfolioBody');
    tbody.innerHTML = '';

    plan.portfolio.forEach(item => {
        const tagClass = getTagBadgeClass(item.tag);
        const tr = document.createElement('tr');
        const cleanTitle = item.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const itemId = item.id || 0;

        tr.innerHTML = `
            <td class="ps-4 py-3">
                <a href="index.php?query=${encodeURIComponent(item.title)}" class="text-white hover-accent fw-bold mb-1 d-block text-decoration-none" title="Buscar no painel principal">
                    ${item.title} <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 8px; opacity: 0.7;"></i>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <span class="tag-badge ${tagClass}">${item.tag}</span>
                    <span class="badge bg-dark border text-muted">${item.allocation_percent}% do Orçamento</span>
                </div>
            </td>
            <td class="text-center fw-semibold text-light">${formatBRL(item.unit_cost)}</td>
            <td class="text-center">
                <span class="badge bg-success bg-opacity-25 text-success fs-6 px-3 py-2">${item.quantity} und</span>
            </td>
            <td class="text-center fw-bold text-white">${formatBRL(item.total_invested)}</td>
            <td class="text-center fw-semibold text-accent-turquoise">${formatBRL(item.suggested_price)}</td>
            <td class="text-center fw-bold text-success">${formatBRL(item.estimated_profit)}</td>
            <td class="text-center">
                <span class="badge bg-warning text-dark fw-bold">+${item.roi}%</span>
            </td>
            <td class="pe-4">
                <div class="fw-bold text-white small"><i class="fa-solid fa-building text-success me-1"></i>${item.supplier_name}</div>
                <div class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i>${item.supplier_location}</div>
                <div class="small text-accent-turquoise" style="font-size: 11px;"><i class="fa-solid fa-truck me-1"></i>${item.shipping_time}</div>
                <div class="mt-2 d-flex gap-1">
                    <button onclick="lookupSuppliers(${itemId}, '${cleanTitle}', ${item.suggested_price})" class="btn btn-xs btn-outline-turquoise text-nowrap" style="font-size: 10px; padding: 2px 6px;" title="Ver Fornecedores"><i class="fa-solid fa-truck-ramp-box me-1"></i>Fornecedores</button>
                    <button onclick="openCalculatorWithProduct('${cleanTitle}', ${item.suggested_price}); event.stopPropagation();" class="btn btn-xs btn-outline-info text-nowrap" style="font-size: 10px; padding: 2px 6px;" title="Calcular Margem"><i class="fa-solid fa-calculator me-1"></i>Calculadora</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // 3. Strategy Tips
    const stratContainer = document.getElementById('strategyContainer');
    stratContainer.innerHTML = '';

    plan.strategy_tips.forEach((tip, idx) => {
        const col = document.createElement('div');
        col.className = 'col-md-6';
        col.innerHTML = `
            <div class="p-3 rounded-3 border border-light-subtle bg-dark bg-opacity-50 h-100 d-flex align-items-start">
                <div class="bg-success bg-opacity-25 text-success rounded-circle p-2 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <div class="fw-bold text-white small mb-1">Estratégia de Alocação #${idx + 1}</div>
                    <div class="text-muted small">${tip}</div>
                </div>
            </div>
        `;
        stratContainer.appendChild(col);
    });

    document.getElementById('planResults').classList.remove('d-none');
}

function exportPlanCsv() {
    if (!currentPlanData || !currentPlanData.portfolio) return;
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Produto,Categoria,Custo Unitario BR,Qtd Recomendada,Total Investido,Preco Venda Sugerido,Lucro Estimado,ROI (%),Fornecedor BR,Localizacao Fornecedor,Prazo Envio\r\n";

    currentPlanData.portfolio.forEach(item => {
        let row = [
            `"${item.title}"`,
            `"${item.category}"`,
            item.unit_cost,
            item.quantity,
            item.total_invested,
            item.suggested_price,
            item.estimated_profit,
            item.roi,
            `"${item.supplier_name}"`,
            `"${item.supplier_location}"`,
            `"${item.shipping_time}"`
        ];
        csvContent += row.join(",") + "\r\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "plano_compras_fornecedores_br.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Auto generate on page load with default R$ 5.000
$(document).ready(function() {
    generateInvestmentPlan();
});
</script>

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
