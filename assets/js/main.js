/**
 * TrendHunter Brasil - Main Frontend JS controller
 */

$(document).ready(function() {
    let priceChart = null;

    // Load default sections
    loadTrends();
    loadFavorites();
    loadAlerts();

    // 1. Theme Toggle logic
    $('#theme-toggle-btn').on('click', function() {
        const currentTheme = $('html').attr('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        $('html').attr('data-bs-theme', newTheme);
        
        // Save user preference to backend
        $.post('api.php?action=toggle_theme', { dark_mode: newTheme === 'dark' ? 1 : 0 });
        
        // Change toggle button icon
        if (newTheme === 'light') {
            $(this).html('<i class="fa-solid fa-moon"></i>');
        } else {
            $(this).html('<i class="fa-solid fa-sun"></i>');
        }
    });

    // 1.1. Mobile Responsive Sidebar Toggle
    $(document).on('click', '#mobile-sidebar-toggle', function(e) {
        e.preventDefault();
        $('#sidebar').toggleClass('show');
        $('#sidebar-backdrop').toggleClass('show');
    });

    $(document).on('click', '#sidebar-backdrop, .sidebar-link', function() {
        if ($(window).width() < 992) {
            $('#sidebar').removeClass('show');
            $('#sidebar-backdrop').removeClass('show');
        }
    });

    // 2. Marketplace Badge multi-select toggling
    $('.marketplace-badge').on('click', function() {
        const checkbox = $(this).find('input');
        checkbox.prop('checked', !checkbox.prop('checked'));
        $(this).toggleClass('active');
    });

    // 3. Main Search Form submit
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        
        let query = $('#search-query').val().trim();
        const category = $('#search-category').val();
        
        if (!query && !category) {
            query = 'geral';
        } else if (!query && category) {
            query = category;
        }

        // Get selected marketplaces
        const selectedMarkets = [];
        $('.marketplace-badge input:checked').each(function() {
            selectedMarkets.push($(this).val());
        });

        // Show spinner
        $('#search-results-section').show();
        $('#results-placeholder').hide();
        $('#results-loading').show();
        $('#results-table-container').hide();

        // AJAX search call
        $.ajax({
            url: 'api.php?action=search',
            method: 'POST',
            data: {
                query: query,
                category: category,
                marketplaces: selectedMarkets
            },
            success: function(response) {
                $('#results-loading').hide();
                
                if (response.success && response.products.length > 0) {
                    window.currentSearchResults = response.products;
                    renderSearchResults(response.products);
                    $('#results-table-container').show();
                    
                    // Setup export URLs
                    const marketsStr = selectedMarkets.join(',');
                    $('#export-csv-btn').attr('href', `api.php?action=export&format=csv&query=${encodeURIComponent(query)}&category=${encodeURIComponent(category || '')}&marketplaces=${marketsStr}`);
                    $('#export-xls-btn').attr('href', `api.php?action=export&format=excel&query=${encodeURIComponent(query)}&category=${encodeURIComponent(category || '')}&marketplaces=${marketsStr}`);
                    $('#export-pdf-btn').attr('href', `api.php?action=export&format=pdf&query=${encodeURIComponent(query)}&category=${encodeURIComponent(category || '')}&marketplaces=${marketsStr}`);
                } else {
                    $('#results-loading').hide();
                    $('#results-placeholder').html(`
                        <div class="text-center py-5">
                            <i class="fa-regular fa-folder-open text-muted fs-1 mb-3"></i>
                            <h5>Nenhum produto encontrado</h5>
                            <p class="text-muted">Tente ajustar seus termos ou selecione mais marketplaces.</p>
                        </div>
                    `).show();
                }
            },
            error: function(xhr) {
                $('#results-loading').hide();
                const errText = xhr.responseJSON ? xhr.responseJSON.error : 'Ocorreu um erro ao buscar dados.';
                $('#results-placeholder').html(`
                    <div class="alert alert-danger mx-auto mt-4 rounded-3 border-0" style="max-width: 500px; background-color: rgba(220, 53, 69, 0.12); color: #fc5c65;">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> ${errText}
                    </div>
                `).show();
            }
        });
    });

    // Column Header Sorting handler
    let sortDirection = {};
    $('.sortable-th').on('click', function() {
        const sortField = $(this).attr('data-sort');
        if (!window.currentSearchResults || window.currentSearchResults.length === 0) return;

        // Toggle sort order
        const isAsc = sortDirection[sortField] === 'asc';
        sortDirection = { [sortField]: isAsc ? 'desc' : 'asc' }; // reset others

        const direction = sortDirection[sortField];
        
        // Reset all header icons
        $('.sortable-th i').attr('class', 'fa-solid fa-sort ms-1 small opacity-50');

        // Set active icon
        const activeIcon = direction === 'asc' ? 'fa-solid fa-sort-up text-accent-turquoise ms-1 small' : 'fa-solid fa-sort-down text-accent-turquoise ms-1 small';
        $(this).find('i').attr('class', activeIcon);

        // Sort items in place
        window.currentSearchResults.sort((a, b) => {
            let valA, valB;
            
            switch (sortField) {
                case 'title':
                    valA = a.title.toLowerCase();
                    valB = b.title.toLowerCase();
                    return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                case 'price':
                    valA = parseFloat(a.price);
                    valB = parseFloat(b.price);
                    break;
                case 'sales':
                    valA = parseInt(a.sales_count_est);
                    valB = parseInt(b.sales_count_est);
                    break;
                case 'rating':
                    valA = parseFloat(a.rating);
                    valB = parseFloat(b.rating);
                    break;
                case 'score':
                    valA = parseInt(a.trend_score);
                    valB = parseInt(b.trend_score);
                    break;
                case 'competition':
                    const compWeight = { 'low': 1, 'medium': 2, 'high': 3 };
                    valA = compWeight[a.competition_level] || 2;
                    valB = compWeight[b.competition_level] || 2;
                    break;
                default:
                    return 0;
            }

            if (valA < valB) return direction === 'asc' ? -1 : 1;
            if (valA > valB) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        // Re-render table
        renderSearchResults(window.currentSearchResults);
    });

    window.currentPage = 1;

    // Page size dropdown change listener
    $('#page-size-select').on('change', function() {
        window.currentPage = 1;
        if (window.currentSearchResults && window.currentSearchResults.length > 0) {
            renderSearchResults(window.currentSearchResults);
        }
    });

    window.goToPage = function(page) {
        window.currentPage = page;
        if (window.currentSearchResults) renderSearchResults(window.currentSearchResults);
    };

    function renderPaginationControls(totalItems, pageSize) {
        let container = $('#pagination-container');
        if (container.length === 0) {
            $('#results-table-container').append('<div id="pagination-container" class="d-flex justify-content-center mt-3 mb-2"></div>');
            container = $('#pagination-container');
        }
        
        if (pageSize >= totalItems || totalItems === 0) {
            container.empty();
            return;
        }

        const totalPages = Math.ceil(totalItems / pageSize);
        let html = '<ul class="pagination pagination-sm m-0">';
        
        // Prev
        html += `<li class="page-item ${window.currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${window.currentPage - 1}); return false;">Anterior</a></li>`;
        
        // Pages (simple version)
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= window.currentPage - 1 && i <= window.currentPage + 1)) {
                html += `<li class="page-item ${i === window.currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
            } else if (i === window.currentPage - 2 || i === window.currentPage + 2) {
                html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
            }
        }
        
        // Next
        html += `<li class="page-item ${window.currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${window.currentPage + 1}); return false;">Próximo</a></li>`;
        html += '</ul>';
        
        container.html(html);
    }

    // 4. Render Search results table
    function renderSearchResults(products) {
        const tbody = $('#results-tbody');
        tbody.empty();

        const pageSize = $('#page-size-select').val() || '15';
        let limit = products.length;
        let displayProducts = products;

        if (pageSize !== 'all') {
            limit = parseInt(pageSize);
            const start = (window.currentPage - 1) * limit;
            displayProducts = products.slice(start, start + limit);
        }

        displayProducts.forEach(p => {
            window.productsMap = window.productsMap || {};
            window.productsMap[p.id] = p;

            const priceFormatted = parseFloat(p.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const salesFormatted = parseInt(p.sales_count_est).toLocaleString('pt-BR');
            const reviewsFormatted = parseInt(p.reviews_count).toLocaleString('pt-BR');
            
            // Score Badge styling logic
            let scoreClass = 'bg-danger';
            if (p.trend_score >= 80) scoreClass = 'bg-success';
            else if (p.trend_score >= 40) scoreClass = 'bg-warning text-dark';

            // Competition level badge
            let compBadge = `<span class="badge-custom badge-custom-medium">Média</span>`;
            if (p.competition_level === 'high') {
                compBadge = `<span class="badge-custom badge-custom-high">Alta</span>`;
            } else if (p.competition_level === 'low') {
                compBadge = `<span class="badge-custom badge-custom-low">Baixa</span>`;
            }

            const cleanTitle = p.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');

            const tr = `
                <tr>
                    <td>
                        <img src="${p.image_url}" alt="Produto" class="product-img-th">
                    </td>
                    <td>
                        <a href="${p.url}" target="_blank" class="text-white hover-accent fw-semibold text-truncate d-block text-decoration-none" style="max-width: 220px;" title="${p.title}">
                            ${p.title} <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 8px; opacity: 0.7;"></i>
                        </a>
                        <small class="text-muted">${p.store_name} | <span class="text-uppercase fw-bold text-accent-turquoise">${p.marketplace}</span></small>
                    </td>
                    <td class="fw-bold">R$ ${priceFormatted}</td>
                    <td>${salesFormatted}/mês</td>
                    <td>${reviewsFormatted} (${parseFloat(p.rating).toFixed(1)} ★)</td>
                    <td>${compBadge}</td>
                    <td>
                        <div class="score-badge-circle ${scoreClass}">${p.trend_score}</div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button onclick="lookupSuppliers(${p.id}, '${cleanTitle}', ${p.price})" class="btn btn-sm btn-outline-turquoise" title="Buscar Fornecedores"><i class="fa-solid fa-truck-ramp-box"></i></button>
                            <button onclick="openCalculatorWithProduct('${cleanTitle}', ${p.price})" class="btn btn-sm btn-outline-info" title="Calcular Lucro"><i class="fa-solid fa-calculator"></i></button>
                            <button onclick="triggerAiAdvisor(${p.id}, '${cleanTitle}')" class="btn btn-sm btn-outline-purple" title="Analise Inteligência Artificial"><i class="fa-solid fa-wand-magic-sparkles"></i></button>
                            <button onclick="openPriceHistory(${p.id}, '${cleanTitle}')" class="btn btn-sm btn-outline-warning" title="Histórico de Preços"><i class="fa-solid fa-chart-line"></i></button>
                            <button onclick="toggleFavorite(${p.id}, this)" class="btn btn-sm btn-outline-danger" title="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <button onclick="openAlertModal(${p.id}, '${cleanTitle}', ${p.price})" class="btn btn-sm btn-outline-success" title="Criar Alerta"><i class="fa-regular fa-bell"></i></button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
        
        renderPaginationControls(products.length, limit);
    }

    // 5. Load Sidebar Google Trends Widget
    function loadTrends() {
        refreshTrends(false);
    }

    function refreshTrends(force = true) {
        const list = $('#google-trends-list');
        list.html('<div class="text-center py-2"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>');
        
        const url = force ? 'api.php?action=get_trends&refresh=1' : 'api.php?action=get_trends';
        
        $.getJSON(url, function(response) {
            if (response.success && response.trends.length > 0) {
                list.empty();
                // Render top 8 trends
                response.trends.slice(0, 8).forEach((term, index) => {
                    list.append(`
                        <li class="d-flex align-items-center mb-2" style="cursor: pointer;" onclick="searchKeyword('${term}')">
                            <span class="badge bg-secondary me-2">${index + 1}</span>
                            <span class="text-truncate fw-medium hover-accent">${term}</span>
                        </li>
                    `);
                });
            } else {
                list.html('<li class="text-muted text-center py-2">Sem tendências no momento</li>');
            }
        });
    }
    window.refreshTrends = refreshTrends;

    // Helper to start search from trends list click
    window.searchKeyword = function(keyword) {
        $('#search-query').val(keyword);
        $('#search-form').submit();
    };

    // 6. Manage Favorites
    window.toggleFavorite = function(productId, btnElement) {
        const icon = $(btnElement).find('i');
        const isAdding = icon.hasClass('fa-regular');
        const prod = (window.productsMap && window.productsMap[productId]) ? window.productsMap[productId] : {};

        if (isAdding) {
            $.post('api.php?action=add_favorite', {
                product_id: productId,
                title: prod.title || '',
                price: prod.price || 0,
                marketplace: prod.marketplace || 'TrendHunter',
                image_url: prod.image_url || '',
                url: prod.url || '',
                store_name: prod.store_name || '',
                category: prod.category || '',
                sales_count_est: prod.sales_count_est || 0,
                trend_score: prod.trend_score || 80
            }, function(res) {
                if (res.success) {
                    icon.removeClass('fa-regular').addClass('fa-solid');
                    showSaveToast('❤️ Produto salvo! Acesse o menu "Produtos Salvos & IA" para gerenciar e criar anúncios.');
                    loadFavorites();
                } else {
                    alert('Erro ao favoritar: ' + (res.error || 'Não foi possível salvar'));
                }
            }).fail(function(xhr) {
                alert('Erro de conexão ao salvar produto.');
            });
        } else {
            $.post('api.php?action=remove_favorite', { product_id: productId }, function(res) {
                if (res.success) {
                    icon.removeClass('fa-solid').addClass('fa-regular');
                    showSaveToast('🗑️ Produto removido dos salvos.');
                    loadFavorites();
                }
            });
        }
    };

    function showSaveToast(message) {
        let toast = $('#save-feedback-toast');
        if (toast.length === 0) {
            toast = $(`<div id="save-feedback-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
                <div class="toast show bg-card border border-light-subtle shadow-lg" role="alert" style="background: rgba(15, 17, 31, 0.95) !important; color: #fff;">
                    <div class="toast-body d-flex align-items-center gap-2 fw-semibold" id="save-toast-msg"></div>
                </div>
            </div>`);
            $('body').append(toast);
        }
        $('#save-toast-msg').html(message);
        toast.fadeIn(200);
        setTimeout(() => toast.fadeOut(500), 3500);
    }

    function loadFavorites() {
        $.getJSON('api.php?action=get_favorites', function(response) {
            const list = $('#favorites-container');
            if (response.success && response.favorites.length > 0) {
                list.empty();
                response.favorites.forEach(f => {
                    const priceFormatted = parseFloat(f.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    list.append(`
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded border border-light-subtle" style="background-color: rgba(255,255,255,0.01)">
                            <div class="d-flex align-items-center text-truncate">
                                <img src="${f.image_url}" class="rounded me-2" style="width:36px;height:36px;object-fit:cover;">
                                <div class="text-truncate" style="max-width: 140px;">
                                    <div class="fw-semibold text-truncate small">${f.title}</div>
                                    <small class="text-uppercase text-accent-turquoise fw-bold" style="font-size:10px;">${f.marketplace}</small>
                                </div>
                            </div>
                            <div class="text-end ms-2">
                                <div class="fw-bold small">R$ ${priceFormatted}</div>
                                <button onclick="removeFavoriteItem(${f.id})" class="btn btn-link btn-sm text-danger p-0 border-0"><i class="fa-regular fa-trash-can"></i></button>
                            </div>
                        </div>
                    `);
                });
            } else {
                list.html('<p class="text-muted text-center py-3 small">Nenhum favorito salvo.</p>');
            }
        });
    }

    window.removeFavoriteItem = function(productId) {
        $.post('api.php?action=remove_favorite', { product_id: productId }, function(res) {
            if (res.success) {
                loadFavorites();
                // If it is in search results, toggle icon back to regular
                // (simple approach is to re-render or let it reload next search)
            }
        });
    };

    // 7. Manage Alerts
    window.openAlertModal = function(productId, title, currentPrice) {
        $('#alert-product-id').val(productId);
        $('#alert-product-title').text(title);
        $('#alert-current-price').text(parseFloat(currentPrice).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#alert-target-value').val(currentPrice);

        const alertModal = new bootstrap.Modal(document.getElementById('alertSetupModal'));
        alertModal.show();
    };

    $('#save-alert-btn').on('click', function() {
        const prodId = $('#alert-product-id').val();
        const type = $('#alert-type-select').val();
        const val = $('#alert-target-value').val();

        $.post('api.php?action=create_alert', {
            product_id: prodId,
            alert_type: type,
            target_value: val
        }, function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('alertSetupModal')).hide();
                loadAlerts();
            } else {
                alert('Erro: ' + response.error);
            }
        });
    });

    function loadAlerts() {
        $.getJSON('api.php?action=get_alerts', function(response) {
            const container = $('#alerts-container');
            const alertNotificationBar = $('#alert-notification-banners');
            
            container.empty();
            alertNotificationBar.empty();

            if (response.success && response.alerts.length > 0) {
                response.alerts.forEach(a => {
                    const targetFormatted = parseFloat(a.target_value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const priceFormatted = parseFloat(a.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                    // Add standard alert list entry
                    container.append(`
                        <div class="p-2 mb-2 rounded border border-light-subtle small" style="background-color: rgba(255,255,255,0.01)">
                            <div class="fw-semibold text-truncate mb-1">${a.title}</div>
                            <div class="d-flex justify-content-between text-muted">
                                <span>Alerta: ${a.alert_type === 'price_drop' ? 'Queda de Preço' : 'Alta de Vendas'}</span>
                                <span>Alvo: <strong>${a.alert_type === 'price_drop' ? 'R$ ' + targetFormatted : a.target_value}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge ${a.is_active == 2 ? 'bg-danger text-light' : 'bg-secondary text-light'}">${a.is_active == 2 ? 'Disparado!' : 'Ativo'}</span>
                                <small class="text-muted">Atual: R$ ${priceFormatted}</small>
                            </div>
                        </div>
                    `);

                    // 3. If alert is triggered (is_active = 2), display a beautiful banner notification on top of dashboard
                    if (a.is_active == 2) {
                        alertNotificationBar.append(`
                            <div class="alert alert-warning border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between p-3 rounded-4" role="alert" style="background: linear-gradient(135deg, rgba(253, 150, 68, 0.2) 0%, rgba(252, 92, 101, 0.1) 100%);">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size:18px;">
                                        <i class="fa-solid fa-bell-ring animate-bounce"></i>
                                    </div>
                                    <div>
                                        <strong class="text-white">Alerta Disparado!</strong> 
                                        <span class="text-light-subtle d-block small">O produto "${a.title}" atingiu seu alvo de monitoramento. Preço atual R$ ${priceFormatted} (Alvo: R$ ${targetFormatted}).</span>
                                    </div>
                                </div>
                                <button onclick="dismissAlertBanner(${a.id})" class="btn btn-sm btn-outline-warning border-0"><i class="fa-solid fa-circle-check fs-5"></i></button>
                            </div>
                        `);
                    }
                });
            } else {
                container.html('<p class="text-muted text-center py-3 small">Nenhum alerta programado.</p>');
            }
        });
    }

    window.dismissAlertBanner = function(alertId) {
        $.post('api.php?action=dismiss_alert', { alert_id: alertId }, function(response) {
            if (response.success) {
                loadAlerts();
            }
        });
    };

    // 8. AI Niche & SEO Advisor Modal
    window.triggerAiAdvisor = function(productId, title) {
        $('#ai-product-title').text(title);
        $('#ai-insights-modal-body').hide();
        $('#ai-loading-spinner').show();

        const aiModal = new bootstrap.Modal(document.getElementById('aiAdvisorModal'));
        aiModal.show();

        $.getJSON('api.php?action=ai_analyze', { product_id: productId }, function(response) {
            $('#ai-loading-spinner').hide();
            if (response.success && response.analysis) {
                const analysis = response.analysis;
                
                // Set optimized Title
                $('#ai-seo-title').text(analysis.seo_title);
                
                // Set optimized Description
                $('#ai-seo-desc').html(analysis.seo_description.replace(/\n/g, '<br>'));
                
                // Set target audience & strategy
                $('#ai-audience').text(analysis.target_audience);
                $('#ai-strategy').text(analysis.marketing_strategy);

                // Niches list
                const nichesContainer = $('#ai-niches-list');
                nichesContainer.empty();
                analysis.niches.forEach(n => {
                    nichesContainer.append(`<span class="badge bg-purple-glow text-accent-purple border border-purple-stroke p-2 me-2 mb-2 fw-medium">${n}</span>`);
                });

                // Keywords list
                const keywordsContainer = $('#ai-keywords-list');
                keywordsContainer.empty();
                analysis.keywords.forEach(k => {
                    keywordsContainer.append(`<span class="badge bg-turquoise-glow text-accent-turquoise border border-turquoise-stroke p-2 me-2 mb-2 fw-medium" style="cursor:pointer;" onclick="searchKeyword('${k}')">${k}</span>`);
                });

                $('#ai-insights-modal-body').fadeIn(300);
            } else {
                $('#ai-insights-modal-body').html('<div class="alert alert-danger">Falha ao processar análise por inteligência artificial.</div>').show();
            }
        });
    };

    // 9. Load Historical Price Chart Modal
    window.openPriceHistory = function(productId, title) {
        $('#history-product-title').text(title);
        
        const historyModal = new bootstrap.Modal(document.getElementById('priceHistoryModal'));
        historyModal.show();

        $.getJSON('api.php?action=price_history', { product_id: productId }, function(response) {
            if (response.success && response.history.length > 0) {
                const historyData = response.history;
                
                const labels = historyData.map(h => h.date_label);
                const prices = historyData.map(h => parseFloat(h.price));
                const sales = historyData.map(h => parseInt(h.sales_count_est));

                // Draw/Update Chart
                const ctx = document.getElementById('priceHistoryChart').getContext('2d');
                
                if (priceChart !== null) {
                    priceChart.destroy();
                }

                // Custom charts style for dark/light modes
                const isDark = $('html').attr('data-bs-theme') === 'dark';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
                const labelColor = isDark ? '#8d90a6' : '#64748b';

                priceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Preço (R$)',
                                data: prices,
                                borderColor: '#745df7',
                                backgroundColor: 'rgba(116, 93, 247, 0.08)',
                                fill: true,
                                yAxisID: 'yPrice',
                                tension: 0.3,
                                borderWidth: 3
                            },
                            {
                                label: 'Vendas Estimadas',
                                data: sales,
                                borderColor: '#06e1cc',
                                backgroundColor: 'transparent',
                                fill: false,
                                yAxisID: 'ySales',
                                tension: 0.3,
                                borderWidth: 2,
                                borderDash: [5, 5]
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: labelColor }
                            },
                            yPrice: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: gridColor },
                                ticks: { color: labelColor, callback: (v) => 'R$ ' + v },
                                title: { display: true, text: 'Preço de Venda', color: '#745df7' }
                            },
                            ySales: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false }, // only draw grid lines for price axis
                                ticks: { color: labelColor },
                                title: { display: true, text: 'Vendas Mensais Est.', color: '#06e1cc' }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: labelColor }
                            }
                        }
                    }
                });
            }
        });
    };

    // 10. Supplier Lookup & Margin Analysis Modal
    window.lookupSuppliers = function(productId, title, retailPrice) {
        $('#supplier-product-title').text(title);
        $('#supplier-retail-price').text(parseFloat(retailPrice).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#supplier-loading-spinner').show();
        $('#supplier-results-body').hide();

        const supplierModal = new bootstrap.Modal(document.getElementById('supplierLookupModal'));
        supplierModal.show();

        $.getJSON('api.php?action=find_suppliers', { product_id: productId, price: retailPrice }, function(response) {
            $('#supplier-loading-spinner').hide();
            if (response.success && response.suppliers.length > 0) {
                const tbody = $('#supplier-tbody');
                tbody.empty();
                
                response.suppliers.forEach(s => {
                    const priceFormatted = parseFloat(s.wholesale_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const profitFormatted = parseFloat(s.profit_margin).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const marginFormatted = parseFloat(s.margin_percent).toFixed(1);
                    const roiFormatted = parseFloat(s.roi_percent).toFixed(1);
                    
                    let badgeClass = 'bg-danger-glow text-danger';
                    let statusLabel = 'Margem Apertada';
                    if (s.margin_percent >= 30) {
                        badgeClass = 'text-success bg-success-glow';
                        statusLabel = 'Alta Margem (Excelente)';
                    } else if (s.margin_percent >= 15) {
                        badgeClass = 'text-warning bg-warning-glow';
                        statusLabel = 'Margem Aceitável';
                    }

                    const escapedName = s.name.replace(/'/g, "\\'");
                    const escapedType = s.type.replace(/'/g, "\\'");
                    const escapedAddress = s.address.replace(/'/g, "\\'");
                    const escapedPhone = s.phone.replace(/'/g, "\\'");
                    const escapedNotes = s.notes.replace(/'/g, "\\'");
                    const escapedTitle = title.replace(/'/g, "\\'");

                    const tr = `
                        <tr>
                            <td>
                                <strong class="text-white">${s.name}</strong>
                                <small class="text-muted d-block">${s.type} | Entrega: ${s.delivery_days} dias</small>
                                <small class="text-muted d-block" style="font-size: 11px; color: #a5a6c9;"><i class="fa-solid fa-location-dot text-danger me-1"></i> ${s.address}</small>
                                <small class="text-muted d-block" style="font-size: 11px; color: #a5a6c9;"><i class="fa-solid fa-phone text-success me-1"></i> ${s.phone}</small>
                                <small class="text-warning d-block" style="font-size: 10px; margin-top: 2px;"><i class="fa-solid fa-circle-info me-1"></i> ${s.notes}</small>
                            </td>
                            <td class="fw-bold text-accent-turquoise">R$ ${priceFormatted}</td>
                            <td class="fw-semibold">R$ ${profitFormatted}</td>
                            <td>
                                <span class="fw-bold">${marginFormatted}%</span>
                                <small class="text-muted d-block" style="font-size:10px;">ROI: ${roiFormatted}%</small>
                            </td>
                            <td>
                                <span class="badge ${badgeClass} p-1" style="font-size:10px; border: 1px solid rgba(255,255,255,0.05);">${statusLabel}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <a href="${s.url}" target="_blank" class="btn btn-sm btn-outline-info py-1 px-2" style="border-radius: 6px; font-size:10px; text-align: left;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir Site</a>
                                    ${s.is_saved 
                                        ? `<button disabled class="btn btn-sm btn-success py-1 px-2" style="border-radius: 6px; font-size:10px; text-align: left;"><i class="fa-solid fa-check"></i> Salvo!</button>`
                                        : `<button onclick="saveSupplier('${escapedName}', '${escapedType}', ${s.wholesale_price}, ${s.profit_margin}, ${s.margin_percent}, ${s.roi_percent}, '${s.url}', '${escapedAddress}', '${escapedPhone}', '${escapedNotes}', '${escapedTitle}', this)" class="btn btn-sm btn-outline-success py-1 px-2" style="border-radius: 6px; font-size:10px; text-align: left;"><i class="fa-regular fa-bookmark"></i> Salvar Forn.</button>`
                                    }
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
                $('#supplier-results-body').fadeIn(300);
            } else {
                $('#supplier-results-body').html('<div class="alert alert-danger mx-3 mt-3">Nenhum fornecedor encontrado para este termo.</div>').show();
            }
        });
    };

    // 11. Save Supplier to database
    window.saveSupplier = function(name, type, wholesalePrice, profitMargin, marginPercent, roiPercent, url, address, phone, notes, productTitle, btnElement) {
        const btn = $(btnElement);
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Salvando...');

        $.post('api.php?action=save_supplier', {
            name: name,
            type: type,
            wholesale_price: wholesalePrice,
            profit_margin: profitMargin,
            margin_percent: marginPercent,
            roi_percent: roiPercent,
            url: url,
            address: address,
            phone: phone,
            notes: notes,
            product_title: productTitle
        }, function(response) {
            if (response.success) {
                btn.removeClass('btn-outline-success').addClass('btn-success').html('<i class="fa-solid fa-check"></i> Salvo!');
            } else {
                btn.prop('disabled', false).html('<i class="fa-regular fa-bookmark"></i> Erro');
                alert(response.error || 'Falha ao salvar fornecedor.');
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa-regular fa-bookmark"></i> Erro');
            alert('Erro de conexão ao salvar fornecedor.');
        });
    };

    // Auto-trigger search if 'query' or 'q' param is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    const q = urlParams.get('query') || urlParams.get('q');
    if (q) {
        $('#search-query').val(q);
        // Add a small delay to ensure UI is ready
        setTimeout(function() {
            $('#search-form').trigger('submit');
        }, 100);
    }
    window.triggerAiReportGeneration = function() {
        const query = $('#search-query').val().trim() || 'geral';
        if (!window.currentSearchResults || window.currentSearchResults.length === 0) {
            alert('Por favor, realize uma busca antes de gerar o relatório.');
            return;
        }

        const reportBtn = $('#generate-ai-report-btn');
        const reportCard = $('#ai-report-card');
        const reportContent = $('#ai-report-content');

        reportBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin text-white me-1"></i> Analisando...');
        reportCard.slideDown();
        reportContent.html(`
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-info mb-2" role="status"></div>
                <div>A IA está analisando os concorrentes e compilando as maiores buscas. Por favor, aguarde...</div>
            </div>
        `);

        // Send current search results to API to analyze
        $.ajax({
            url: 'api.php?action=generate_ai_report',
            method: 'POST',
            data: {
                query: query,
                products: JSON.stringify(window.currentSearchResults)
            },
            success: function(response) {
                reportBtn.prop('disabled', false).html('<i class="fa-solid fa-robot text-white me-1"></i> Gerar Relatório IA');
                if (response.success && response.report) {
                    reportContent.html(parseMarkdownToHtml(response.report));
                } else {
                    reportContent.html('<div class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Falha ao gerar relatório de IA.</div>');
                }
            },
            error: function(xhr) {
                reportBtn.prop('disabled', false).html('<i class="fa-solid fa-robot text-white me-1"></i> Gerar Relatório IA');
                const errText = xhr.responseJSON ? xhr.responseJSON.error : 'Erro ao conectar-se com a IA.';
                reportContent.html(`<div class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> ${errText}</div>`);
            }
        });
    };

    function parseMarkdownToHtml(markdown) {
        if (!markdown) return '';
        return markdown
            // Headers
            .replace(/^### (.*$)/gim, '<h5 class="fw-bold text-white mt-3 mb-2">$1</h5>')
            .replace(/^#### (.*$)/gim, '<h6 class="fw-bold text-metrify-cyan mt-3 mb-2">$1</h6>')
            .replace(/^## (.*$)/gim, '<h4 class="fw-bold text-white mt-4 mb-2">$1</h4>')
            .replace(/^# (.*$)/gim, '<h3 class="fw-bold text-white mt-4 mb-2">$1</h3>')
            // Bold
            .replace(/\*\*(.*?)\*\*/g, '<strong class="text-white">$1</strong>')
            // Bullet lists
            .replace(/^\* (.*$)/gim, '<li class="ms-3 mb-1 text-muted">$1</li>')
            .replace(/^\- (.*$)/gim, '<li class="ms-3 mb-1 text-muted">$1</li>')
            // Line breaks
            .replace(/\n/g, '<br>');
    }
});
