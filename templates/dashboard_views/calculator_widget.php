<?php
/**
 * TrendHunter Brasil - Dashboard Profit Calculator Widget view
 */
declare(strict_types=1);
?>

<!-- Floating calculator button trigger -->
<div id="floating-calc-trigger" class="floating-calculator-btn" title="Calculadora de Margem & ROI">
    <i class="fa-solid fa-calculator"></i>
</div>

<!-- Calculator Panel container -->
<div id="calculator-panel" class="calculator-panel">
    <div class="calculator-header">
        <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-percent text-accent-turquoise me-2"></i> Calculadora de Lucro e ROI</h6>
        <button id="close-calculator" class="btn-close" style="filter: invert(1); font-size: 10px;"></button>
    </div>
    
    <div class="calculator-body">
        <!-- Variable Inputs -->
        <div class="mb-2">
            <label class="form-label text-muted small mb-1">Preço de Venda (R$)</label>
            <input type="number" step="0.01" id="calc-price" class="form-control calc-input" placeholder="0,00">
        </div>
        
        <div class="mb-2">
            <label class="form-label text-muted small mb-1">Custo do Produto (Fornecedor) (R$)</label>
            <input type="number" step="0.01" id="calc-cost" class="form-control calc-input" placeholder="0,00">
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label text-muted small mb-1">Imposto (%)</label>
                <input type="number" step="0.1" id="calc-tax" class="form-control calc-input" value="6.0" placeholder="6,0">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small mb-1">Comissão Mkt (%)</label>
                <input type="number" step="0.1" id="calc-fee" class="form-control calc-input" value="12.0" placeholder="12,0">
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small mb-1">Frete / Envios (R$)</label>
                <input type="number" step="0.01" id="calc-shipping" class="form-control calc-input" value="5.00" placeholder="5,00">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small mb-1">Custos Fixos (R$)</label>
                <input type="number" step="0.01" id="calc-fixed" class="form-control calc-input" value="1000.00" placeholder="1.000,00" title="Custo fixo da sua empresa (internet, aluguel, etc) para calcular ponto de equilíbrio">
            </div>
        </div>

        <!-- Calculated Metrics Outputs -->
        <div class="border-top border-light-subtle pt-3 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Lucro Líquido Unitário:</span>
                <strong id="res-profit" class="fs-5 text-accent-turquoise">R$ 0,00</strong>
            </div>

            <div class="row g-2 pt-2 border-top border-light-subtle border-opacity-10 text-center">
                <div class="col-4">
                    <small class="text-muted d-block" style="font-size: 10px;">Margem</small>
                    <span id="res-margin" class="fw-bold text-white">0,0%</span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block" style="font-size: 10px;">ROI</small>
                    <span id="res-roi" class="fw-bold text-white">0,0%</span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block" style="font-size: 10px;">Markup</small>
                    <span id="res-markup" class="fw-bold text-white">0,0%</span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded" style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color);">
                <div class="small text-muted"><i class="fa-solid fa-scale-balanced me-1 text-warning"></i> Ponto de Equilíbrio:</div>
                <strong id="res-breakeven" class="text-white small">0 un / mês</strong>
            </div>
        </div>
    </div>
</div>
