<?php
/**
 * TrendHunter Brasil - Dashboard Modals View
 */
declare(strict_types=1);
?>

<!-- 1. Alert Setup Modal -->
<div class="modal fade" id="alertSetupModal" tabindex="-1" aria-labelledby="alertSetupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title fw-bold text-white" id="alertSetupModalLabel"><i class="fa-solid fa-bell-plus text-success me-2"></i> Agendar Monitoramento Automático</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Defina um gatilho de alerta para monitorar flutuações de mercado. O CRON rodará e notificará você assim que o alvo for atingido.</p>
                <div class="p-3 mb-3 rounded border border-light-subtle" style="background-color: rgba(255, 255, 255, 0.01);">
                    <div class="fw-semibold text-white small" id="alert-product-title">Nome do Produto</div>
                    <small class="text-muted">Valor Atual: <strong class="text-accent-turquoise">R$ <span id="alert-current-price">0,00</span></strong></small>
                </div>
                
                <input type="hidden" id="alert-product-id">

                <div class="mb-3">
                    <label for="alert-type-select" class="form-label text-muted small">Condição de Gatilho</label>
                    <select class="form-select" id="alert-type-select" style="background-color: rgba(0,0,0,0.3); border: 1px solid var(--border-color); color: #fff; border-radius: 8px;">
                        <option value="price_drop">Preço cai abaixo ou igual a (R$)</option>
                        <option value="sales_spike">Volume de vendas supera (un/mês)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="alert-target-value" class="form-label text-muted small">Valor Alvo</label>
                    <input type="number" step="0.01" class="form-control" id="alert-target-value" placeholder="0.00" style="background-color: rgba(0,0,0,0.3); border: 1px solid var(--border-color); color: #fff; border-radius: 8px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                <button type="button" class="btn btn-success" id="save-alert-btn" style="border-radius: 8px; font-weight: 600;"><i class="fa-solid fa-clock-rotate-left me-1"></i> Ativar Alerta</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. AI Advisor Insights Modal -->
<div class="modal fade" id="aiAdvisorModal" tabindex="-1" aria-labelledby="aiAdvisorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title fw-bold text-white" id="aiAdvisorModalLabel"><i class="fa-solid fa-wand-magic-sparkles text-accent-purple me-2"></i> Analise por Inteligência Artificial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" style="filter: invert(1);"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- AI Loader -->
                <div id="ai-loading-spinner" class="text-center py-5">
                    <div class="spinner-grow text-accent-purple mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h5>IA consultando dados e criando redação...</h5>
                    <p class="text-muted small">Estruturando nichos de mercado, estratégias de anúncios e copywriting SEO.</p>
                </div>

                <!-- Insights content -->
                <div id="ai-insights-modal-body" style="display: none;">
                    <div class="mb-4">
                        <small class="text-uppercase fw-bold text-accent-purple" style="letter-spacing:1px; font-size:11px;">Produto Selecionado</small>
                        <h5 class="text-white fw-bold" id="ai-product-title">Produto</h5>
                    </div>

                    <!-- SEO Title Recommendation -->
                    <div class="p-3 mb-4 rounded border border-light-subtle" style="background-color: rgba(116, 93, 247, 0.03);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white fw-bold small"><i class="fa-solid fa-heading text-accent-purple me-1"></i> Título Recomendado para Marketplace (SEO)</span>
                            <button onclick="copyToClipboard('ai-seo-title')" class="btn btn-link text-accent-turquoise p-0 text-decoration-none small"><i class="fa-regular fa-copy me-1"></i> Copiar</button>
                        </div>
                        <div id="ai-seo-title" class="fw-semibold text-white fs-6 py-2 px-3 rounded" style="background-color: rgba(0,0,0,0.3); border: 1px solid var(--border-color);">Título SEO</div>
                    </div>

                    <!-- Suggested Niches & Related Keywords -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-arrows-to-eye text-accent-turquoise me-1"></i> Sub-nichos Recomendados</h6>
                            <div id="ai-niches-list" class="d-flex flex-wrap">
                                <!-- Pills -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-key text-warning me-1"></i> Palavras-chave Recomendadas</h6>
                            <div id="ai-keywords-list" class="d-flex flex-wrap">
                                <!-- Pills -->
                            </div>
                        </div>
                    </div>

                    <!-- Marketing Strategy & Target Audience -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card p-3 h-100 border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                                <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-users text-info me-1"></i> Público Alvo</h6>
                                <p id="ai-audience" class="text-muted small mb-0">Descrição do público alvo.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card p-3 h-100 border border-light-subtle" style="background-color: rgba(255,255,255,0.01);">
                                <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-chess-knight text-danger me-1"></i> Estratégia de Anúncio</h6>
                                <p id="ai-strategy" class="text-muted small mb-0">Estratégias de vendas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Optimized SEO Description -->
                    <div class="mb-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white fw-bold small"><i class="fa-solid fa-align-left text-accent-purple me-1"></i> Redação de Descrição Otimizada (SEO Copy)</span>
                            <button onclick="copyToClipboard('ai-seo-desc')" class="btn btn-link text-accent-turquoise p-0 text-decoration-none small"><i class="fa-regular fa-copy me-1"></i> Copiar</button>
                        </div>
                        <div id="ai-seo-desc" class="text-light-subtle small p-3 rounded overflow-y-auto" style="background-color: rgba(0,0,0,0.3); border: 1px solid var(--border-color); max-height: 250px; white-space: pre-line;">Descrição</div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Fechar Painel</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Price History Chart Modal -->
<div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-labelledby="priceHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title fw-bold text-white" id="priceHistoryModalLabel"><i class="fa-solid fa-chart-line text-warning me-2"></i> Histórico de Preços e Vendas Estimadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <small class="text-uppercase fw-bold text-warning" style="letter-spacing:1px; font-size:11px;">Produto em Monitoramento</small>
                    <h5 class="text-white fw-bold mb-1" id="history-product-title">Produto</h5>
                    <p class="text-muted small mb-0">Demonstrativo de flutuação de preço (R$) e estimativa de vendas nas últimas semanas.</p>
                </div>
                
                <!-- Chart Container -->
                <div style="position: relative; width: 100%; height: 350px;">
                    <canvas id="priceHistoryChart"></canvas>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Fechar Painel</button>
            </div>
        </div>
    </div>
</div>

<!-- 4. Wholesale Supplier Lookup Modal -->
<div class="modal fade" id="supplierLookupModal" tabindex="-1" aria-labelledby="supplierLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title fw-bold text-white" id="supplierLookupModalLabel"><i class="fa-solid fa-truck-ramp-box text-accent-turquoise me-2"></i> Análise de Fornecedores & Margem Arbitragem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <small class="text-uppercase fw-bold text-accent-turquoise" style="letter-spacing:1px; font-size:11px;">Produto Analisado</small>
                    <h5 class="text-white fw-bold mb-1" id="supplier-product-title">Nome do Produto</h5>
                    <p class="text-muted small mb-0">Preço de Venda Praticado no E-commerce: <strong class="text-white">R$ <span id="supplier-retail-price">0,00</span></strong></p>
                </div>

                <!-- Loader -->
                <div id="supplier-loading-spinner" class="text-center py-5">
                    <div class="spinner-border text-accent-turquoise mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h5>Rastreando Fontes de Atacado...</h5>
                    <p class="text-muted small">Pesquisando preços de aquisição em distribuidores nacionais e internacionais.</p>
                </div>

                <!-- Supplier list -->
                <div id="supplier-results-body" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover border-light-subtle align-middle" style="background-color: transparent; font-size: 13px;">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Distribuidor</th>
                                    <th>Preço Atacado</th>
                                    <th>Lucro Unitário</th>
                                    <th>Margem Líquida</th>
                                    <th>Classificação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="supplier-tbody">
                                <!-- Filled by main.js -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 p-3 rounded" style="background-color: rgba(6, 225, 204, 0.03); border: 1px dashed rgba(6, 225, 204, 0.2);">
                        <small class="text-accent-turquoise fw-bold d-block mb-1"><i class="fa-solid fa-circle-info me-1"></i> Regras de Cálculo da Margem Líquida:</small>
                        <span class="text-muted small d-block">
                            O lucro exibido deduz automaticamente os seguintes custos estimados: 
                            <strong>Impostos (6%)</strong>, <strong>Comissão de Venda (12%)</strong> e <strong>Custo Fixo de Frete (R$ 5,00 por envio)</strong>, além do preço de compra no atacado.
                        </span>
                    </div>

                    <div class="mt-3 p-3 rounded" style="background-color: rgba(116, 93, 247, 0.05); border: 1px dashed rgba(116, 93, 247, 0.25);">
                        <small class="text-accent-purple fw-bold d-block mb-1"><i class="fa-solid fa-circle-nodes me-1"></i> Metodologia de Preços & Acesso B2B:</small>
                        <span class="text-muted small d-block mb-2">
                            1. <strong>Preço de Atacado Estimado</strong>: O sistema projeta o preço de compra necessário com base nos markups de mercado reais praticados por importadores da 25 de Março e fábricas do Brás (custeando de 30% a 52% do varejo).
                        </span>
                        <span class="text-muted small d-block">
                            2. <strong>Cadastro de CNPJ Exigido</strong>: Os distribuidores nacionais oficiais listados no painel possuem **portais protegidos** (como <i>Mastertronic</i> e <i>Atacado Malu</i>). Você precisa ter um **CNPJ ativo no setor de comércio** e se cadastrar no site deles para liberar as tabelas oficiais e comprar em lote.
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Fechar Painel</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global copy to clipboard function
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).innerText || document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(function() {
            alert('Texto copiado com sucesso!');
        }, function(err) {
            alert('Erro ao copiar: ', err);
        });
    }
</script>
