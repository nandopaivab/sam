<?php
/**
 * TrendHunter Brasil - Dashboard Results section view
 */
declare(strict_types=1);
?>

<div id="search-results-section" style="display: none;">
    <div class="card-premium">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-check text-accent-purple me-2"></i> Resultados Comparativos Encontrados</h5>
            
            <div class="d-flex align-items-center flex-wrap">
                <!-- Page size selector -->
                <div class="d-flex align-items-center me-3 my-2">
                    <label for="page-size-select" class="text-muted small me-2 mb-0" style="white-space: nowrap;"><i class="fa-solid fa-list-ol text-accent-purple me-1"></i> Mostrar:</label>
                    <select id="page-size-select" class="form-select form-select-sm" style="width: 95px; border-radius: 8px; background-color: rgba(9, 10, 20, 0.7); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 12px; height: 32px;">
                        <option value="5">5 itens</option>
                        <option value="10">10 itens</option>
                        <option value="15" selected>15 itens</option>
                        <option value="25">25 itens</option>
                        <option value="50">50 itens</option>
                        <option value="all">Todos</option>
                    </select>
                </div>

                <!-- Exporters buttons group -->
                <div id="export-buttons-group" class="d-flex my-2">
                    <a id="export-csv-btn" href="#" class="btn btn-sm btn-outline-secondary me-2 border-light-subtle" title="Exportar CSV"><i class="fa-solid fa-file-csv text-success me-1"></i> Exportar CSV</a>
                    <a id="export-xls-btn" href="#" class="btn btn-sm btn-outline-secondary me-2 border-light-subtle" title="Exportar Excel"><i class="fa-solid fa-file-excel text-info me-1"></i> Exportar Excel</a>
                    <a id="export-pdf-btn" href="#" target="_blank" class="btn btn-sm btn-outline-secondary border-light-subtle" title="Exportar PDF"><i class="fa-solid fa-file-pdf text-danger me-1"></i> Visualizar Relatório</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Loading Indicator spinner -->
            <div id="results-loading" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-accent-purple mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5>Executando Varredura Multi-Plataforma...</h5>
                <p class="text-muted">Aguarde enquanto consultamos e analisamos os produtos nas APIs e bancos de dados.</p>
            </div>

            <!-- Table Container -->
            <div id="results-table-container" class="table-responsive p-3" style="display: none;">
                <table class="table-premium">
                    <thead>
                        <tr class="user-select-none">
                            <th style="width: 60px;">Imagem</th>
                            <th class="sortable-th" data-sort="title" style="cursor: pointer;">Produto & Canal <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th class="sortable-th" data-sort="price" style="cursor: pointer;">Preço Atual <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th class="sortable-th" data-sort="sales" style="cursor: pointer;">Vendas Est. <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th class="sortable-th" data-sort="rating" style="cursor: pointer;">Avaliações <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th class="sortable-th" data-sort="competition" style="cursor: pointer;">Concorrência <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th class="sortable-th" data-sort="score" style="cursor: pointer;">Trend Score <i class="fa-solid fa-sort ms-1 small opacity-50"></i></th>
                            <th style="width: 180px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="results-tbody">
                        <!-- Filled by main.js -->
                    </tbody>
                </table>
            </div>

            <!-- Error or Empty state placeholder -->
            <div id="results-placeholder">
                <div class="text-center py-5">
                    <i class="fa-solid fa-chart-line-up text-muted fs-1 mb-3"></i>
                    <h5>Pronto para Pesquisa</h5>
                    <p class="text-muted">Digite as palavras-chave no campo de pesquisa acima para iniciar a varredura.</p>
                </div>
            </div>
        </div>
    </div>
</div>
