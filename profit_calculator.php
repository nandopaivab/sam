<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

use TrendHunter\Auth;

Auth::requireLogin();
$user = Auth::user();

include __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    <i class="fa-solid fa-calculator me-1"></i> SIMULADOR COMERCIAL AVANÇADO
                </span>
                <span class="badge bg-purple-glow text-accent-purple border border-purple-subtle px-2 py-1">
                    <i class="fa-solid fa-layer-group me-1"></i> 15 Variáveis Reais
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Calculadora de Lucro Completa & Ponto de Equilíbrio</h1>
            <p class="text-muted small mb-0">Calcule em tempo real custo unitário real, margem líquida, ROI, ponto de equilíbrio (0 prejuízo) e unidades para recuperar investimento.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary border-light-subtle d-flex align-items-center" onclick="resetCalculator()">
                <i class="fa-solid fa-rotate-left me-2"></i> Limpar Simulação
            </button>
            <button class="btn btn-primary px-4 fw-bold shadow-lg" onclick="calculateAdvancedProfit()">
                <i class="fa-solid fa-chart-pie me-2"></i> Calcular com Precisão
            </button>
        </div>
    </div>

    <!-- Main Calculator Area -->
    <div class="row g-4">
        <!-- Input Form (15 variables) -->
        <div class="col-12 col-lg-7">
            <div class="card border-light-subtle p-4" style="border-radius: 18px; background: rgba(255,255,255,0.02);">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-light-subtle">
                    <i class="fa-solid fa-sliders text-accent-purple me-2"></i> Custos de Aquisição & Parâmetros de Venda
                </h5>

                <form id="profitCalcForm">
                    <!-- 1. Custo & Aquisição -->
                    <h6 class="fw-bold small text-accent-turquoise mb-2">1. AQUISIÇÃO & ESTOQUE</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Custo Unitário Bruto (R$) *</label>
                            <input type="number" step="0.01" id="calcCost" name="cost" class="form-control bg-dark text-white border-light-subtle" value="27.50" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Quantidade do Lote *</label>
                            <input type="number" id="calcQty" name="quantity" class="form-control bg-dark text-white border-light-subtle" value="100" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Frete do Lote Total (R$)</label>
                            <input type="number" step="0.01" id="calcShippingIn" name="shipping_in" class="form-control bg-dark text-white border-light-subtle" value="45.00" oninput="calculateAdvancedProfit()">
                        </div>
                    </div>

                    <!-- 2. Custos de Embalagem & Logística -->
                    <h6 class="fw-bold small text-accent-turquoise mb-2">2. INSUMOS, EMBALAGEM & ETIQUETA</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-6">
                            <label class="form-label small text-muted mb-1">Caixa / Embalagem por un. (R$)</label>
                            <input type="number" step="0.01" id="calcPkg" name="packaging" class="form-control bg-dark text-white border-light-subtle" value="1.20" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label small text-muted mb-1">Etiqueta & Proteção por un. (R$)</label>
                            <input type="number" step="0.01" id="calcLabel" name="labels" class="form-control bg-dark text-white border-light-subtle" value="0.30" oninput="calculateAdvancedProfit()">
                        </div>
                    </div>

                    <!-- 3. Marketplace & Impostos -->
                    <h6 class="fw-bold small text-accent-turquoise mb-2">3. COMISSÕES MARKETPLACE & IMPOSTOS</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Comissão do Canal (%) *</label>
                            <input type="number" step="0.1" id="calcComm" name="commission_rate" class="form-control bg-dark text-white border-light-subtle" value="14.0" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted mb-1">Taxa Fixa por Venda (R$)</label>
                            <input type="number" step="0.01" id="calcFixedFee" name="fixed_fee" class="form-control bg-dark text-white border-light-subtle" value="3.00" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Imposto (Simples / ICMS %)</label>
                            <input type="number" step="0.1" id="calcTax" name="tax_rate" class="form-control bg-dark text-white border-light-subtle" value="6.0" oninput="calculateAdvancedProfit()">
                        </div>
                    </div>

                    <!-- 4. Marketing, Devolução & Preço -->
                    <h6 class="fw-bold small text-accent-turquoise mb-2">4. ANÚNCIOS (ADS), DEVOLUÇÃO & PREÇO DE VENDA</h6>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">CPA Anúncio / Ads (R$)</label>
                            <input type="number" step="0.1" id="calcAds" name="ads_cost" class="form-control bg-dark text-white border-light-subtle" value="4.50" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Reserva Devoluções (%)</label>
                            <input type="number" step="0.1" id="calcRet" name="returns_rate" class="form-control bg-dark text-white border-light-subtle" value="2.0" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Frete Subsidiado (R$)</label>
                            <input type="number" step="0.1" id="calcSubShip" name="subsidy_shipping" class="form-control bg-dark text-white border-light-subtle" value="0.00" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Cupom / Desconto (%)</label>
                            <input type="number" step="0.1" id="calcDisc" name="discount_rate" class="form-control bg-dark text-white border-light-subtle" value="0.0" oninput="calculateAdvancedProfit()">
                        </div>
                        <div class="col-12 mt-3 pt-3 border-top border-light-subtle">
                            <label class="form-label text-success fw-bold">PREÇO FINAL DE VENDA PRATICADO (R$) *</label>
                            <input type="number" step="0.01" id="calcPrice" name="price" class="form-control form-control-lg bg-dark text-white border-success" value="69.90" style="font-weight: 700; font-size: 20px;" oninput="calculateAdvancedProfit()">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Output Cards Grid & Break-Even Analysis -->
        <div class="col-12 col-lg-5">
            <div class="card border-light-subtle p-4 h-100" style="border-radius: 18px; background: rgba(255,255,255,0.02);">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-light-subtle">
                    <i class="fa-solid fa-chart-line text-success me-2"></i> Diagnóstico Comercial & ROI
                </h5>

                <!-- Big Net Profit Card -->
                <div class="p-4 rounded mb-3 text-center border border-success-subtle" style="background: rgba(6, 214, 160, 0.08);">
                    <div class="text-muted small fw-bold">LUCRO LÍQUIDO POR UNIDADE VENDIDA</div>
                    <div class="display-5 fw-bold text-success my-1" id="resNetProfit">R$ 18,97</div>
                    <span class="badge bg-success text-white small px-3 py-1" id="resMarginBadge">Margem Líquida: 27.1%</span>
                </div>

                <!-- 4 KPI Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded border border-light-subtle text-center" style="background: rgba(255,255,255,0.02);">
                            <div class="text-muted small" style="font-size: 10px;">CUSTO UNITÁRIO REAL</div>
                            <div class="h5 fw-bold text-white my-1" id="resUnitRealCost">R$ 29,45</div>
                            <div class="text-muted small" style="font-size: 10px;">Com frete lote + insumos</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded border border-light-subtle text-center" style="background: rgba(255,255,255,0.02);">
                            <div class="text-muted small" style="font-size: 10px;">RETORNO SOBRE INVEST. (ROI)</div>
                            <div class="h5 fw-bold text-accent-turquoise my-1" id="resRoi">64.4%</div>
                            <div class="text-muted small" style="font-size: 10px;">Sobre o custo real</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded border border-light-subtle text-center" style="background: rgba(255,255,255,0.02);">
                            <div class="text-muted small" style="font-size: 10px;">PREÇO MÍNIMO (0 PREJUÍZO)</div>
                            <div class="h5 fw-bold text-warning my-1" id="resMinPrice">R$ 44,70</div>
                            <div class="text-muted small" style="font-size: 10px;">Ponto de equilíbrio unitário</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded border border-light-subtle text-center" style="background: rgba(255,255,255,0.02);">
                            <div class="text-muted small" style="font-size: 10px;">VENDAS P/ RECUPERAR CAPITAL</div>
                            <div class="h5 fw-bold text-accent-purple my-1" id="resUnitsRecover">156 un.</div>
                            <div class="text-muted small" style="font-size: 10px;">Investimento total: <span id="resTotalInvest">R$ 2.945</span></div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Deductions breakdown -->
                <h6 class="fw-bold text-white small mb-2 pt-2 border-top border-light-subtle">Detalhamento de Deduções da Venda:</h6>
                <ul class="list-group list-group-flush small border-0" style="font-size: 12px;">
                    <li class="list-group-item bg-transparent text-muted px-0 d-flex justify-content-between">
                        <span>Comissão + Tarifa Fixa:</span>
                        <strong class="text-white" id="resCommVal">R$ 12,79</strong>
                    </li>
                    <li class="list-group-item bg-transparent text-muted px-0 d-flex justify-content-between">
                        <span>Imposto (II/Simples/ICMS):</span>
                        <strong class="text-white" id="resTaxVal">R$ 4,19</strong>
                    </li>
                    <li class="list-group-item bg-transparent text-muted px-0 d-flex justify-content-between">
                        <span>Anúncios (Ads CPA) + Devoluções:</span>
                        <strong class="text-white" id="resAdsVal">R$ 5,90</strong>
                    </li>
                    <li class="list-group-item bg-transparent text-muted px-0 d-flex justify-content-between border-bottom border-light-subtle">
                        <span>Markup Bruto:</span>
                        <strong class="text-accent-turquoise" id="resMarkup">137.4%</strong>
                    </li>
                </ul>

                <div class="alert alert-dark border-light-subtle small mt-3 mb-0" style="font-size: 11px;">
                    <i class="fa-solid fa-lightbulb text-warning me-1"></i> <strong>Dica Comercial:</strong> Se o ROI for superior a 50%, o produto é excelente candidato para campanhas agressivas de tráfego pago.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateAdvancedProfit() {
    const form = document.getElementById('profitCalcForm');
    const formData = new FormData(form);
    formData.append('action', 'calculate_profit_advanced');

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const calc = data.calculation;
            document.getElementById('resNetProfit').innerText = 'R$ ' + calc.net_profit.toFixed(2).replace('.', ',');
            document.getElementById('resMarginBadge').innerText = 'Margem Líquida: ' + calc.margin_percent.toFixed(1) + '%';
            document.getElementById('resUnitRealCost').innerText = 'R$ ' + calc.unit_cost_real.toFixed(2).replace('.', ',');
            document.getElementById('resRoi').innerText = calc.roi_percent.toFixed(1) + '%';
            document.getElementById('resMinPrice').innerText = 'R$ ' + calc.min_selling_price.toFixed(2).replace('.', ',');
            document.getElementById('resUnitsRecover').innerText = calc.units_to_recover_capital + ' un.';
            document.getElementById('resTotalInvest').innerText = 'R$ ' + calc.total_capital_invested.toFixed(2).replace('.', ',');
            document.getElementById('resCommVal').innerText = 'R$ ' + (calc.commission_val + (parseFloat(document.getElementById('calcFixedFee').value)||0)).toFixed(2).replace('.', ',');
            document.getElementById('resTaxVal').innerText = 'R$ ' + calc.tax_val.toFixed(2).replace('.', ',');
            document.getElementById('resAdsVal').innerText = 'R$ ' + ((parseFloat(document.getElementById('calcAds').value)||0) + ((calc.selling_price * (parseFloat(document.getElementById('calcRet').value)||0))/100)).toFixed(2).replace('.', ',');
            document.getElementById('resMarkup').innerText = calc.markup_percent.toFixed(1) + '%';

            // Visual badge color adaptation
            const badge = document.getElementById('resMarginBadge');
            if (calc.margin_percent >= 25) {
                badge.className = 'badge bg-success text-white small px-3 py-1';
            } else if (calc.margin_percent >= 15) {
                badge.className = 'badge bg-warning text-dark small px-3 py-1';
            } else {
                badge.className = 'badge bg-danger text-white small px-3 py-1';
            }
        }
    })
    .catch(err => console.error(err));
}

function resetCalculator() {
    document.getElementById('calcCost').value = '27.50';
    document.getElementById('calcQty').value = '100';
    document.getElementById('calcShippingIn').value = '45.00';
    document.getElementById('calcPkg').value = '1.20';
    document.getElementById('calcLabel').value = '0.30';
    document.getElementById('calcComm').value = '14.0';
    document.getElementById('calcFixedFee').value = '3.00';
    document.getElementById('calcTax').value = '6.0';
    document.getElementById('calcAds').value = '4.50';
    document.getElementById('calcRet').value = '2.0';
    document.getElementById('calcSubShip').value = '0.00';
    document.getElementById('calcDisc').value = '0.0';
    document.getElementById('calcPrice').value = '69.90';
    calculateAdvancedProfit();
}

// Initial calculation on page load
document.addEventListener('DOMContentLoaded', calculateAdvancedProfit);
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
