<?php
/**
 * TrendHunter Brasil - Dashboard Search section view
 */
declare(strict_types=1);
?>

<div class="card-premium metric-card turquoise p-4 search-box-container">
    <h4 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass-chart text-accent-turquoise me-2"></i> Scanner de Produtos Integrado</h4>
    
    <form id="search-form" autocomplete="off">
        <div class="row g-3 mb-4">
            <!-- Keyword search -->
            <div class="col-md-7 col-lg-8">
                <label for="search-query" class="form-label mb-1 fw-semibold text-white">Palavra-chave, Categoria ou Produto</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-light-subtle text-muted" style="border-radius: 12px 0 0 12px;"><i class="fa-solid fa-search"></i></span>
                    <input type="text" class="form-control border-start-0" id="search-query" placeholder="Digite uma palavra-chave OU deixe em branco para varrer por Categoria..." style="border-radius: 0 12px 12px 0; background-color: rgba(9, 10, 20, 0.7); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                </div>
            </div>
            
            <!-- Category dropdown -->
            <div class="col-md-5 col-lg-4">
                <label for="search-category" class="form-label mb-1 fw-semibold text-white">Filtro de Categoria</label>
                <select class="form-select" id="search-category" style="border-radius: 12px; height: 48px; background-color: rgba(9, 10, 20, 0.7); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                    <option value="">Todas as Categorias</option>
                    <option value="Eletrônicos & Áudio">Eletrônicos & Áudio</option>
                    <option value="Beleza & Cuidados Pessoais">Beleza & Cuidados Pessoais</option>
                    <option value="Vestíveis & Tecnologia">Vestíveis & Tecnologia</option>
                    <option value="Utilidades Domésticas">Utilidades Domésticas</option>
                    <option value="Esportes & Academia">Esportes & Academia</option>
                    <option value="Casa & Decoração">Casa & Decoração</option>
                    <option value="Moda Masculina">Moda Masculina</option>
                    <option value="Moda Feminina">Moda Feminina</option>
                    <option value="Moda Infantil">Moda Infantil</option>
                    <option value="Brinquedos & Hobbies">Brinquedos & Hobbies</option>
                </select>
            </div>
        </div>

        <!-- Quick Niche Buttons -->
        <div class="mb-4 mt-2">
            <span class="text-muted small me-2"><i class="fa-solid fa-fire text-accent-turquoise me-1 animate-bounce"></i> Varredura Rápida de Nichos (1 Clique):</span>
            <div class="d-inline-flex flex-wrap gap-2 mt-1">
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Roupas Masculinas', 'Moda Masculina')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-shirt text-primary me-1"></i> Roupas Masculinas</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Roupas Femininas', 'Moda Feminina')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-person-dress text-danger me-1"></i> Roupas Femininas</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Roupas de Crianças', 'Moda Infantil')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-baby text-warning me-1"></i> Roupas Infantis</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Brinquedos', 'Brinquedos & Hobbies')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-gamepad text-info me-1"></i> Brinquedos & Hobbies</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Garrafa Térmica', 'Esportes & Academia')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-wine-bottle text-warning me-1"></i> Garrafas Térmicas</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Fone Bluetooth', 'Eletrônicos & Áudio')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-headphones text-primary me-1"></i> Fones de Ouvido</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Smartwatch', 'Vestíveis & Tecnologia')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-clock text-info me-1"></i> Smartwatches</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Maquiagem', 'Beleza & Cuidados Pessoais')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-wand-magic-sparkles text-danger me-1"></i> Beleza & Cosméticos</button>
                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle py-1 px-3 text-white" onclick="searchNiche('Air Fryer', 'Utilidades Domésticas')" style="border-radius: 20px; font-size:11px; background-color: rgba(255,255,255,0.02);"><i class="fa-solid fa-kitchen-set text-success me-1"></i> Eletroportáteis</button>
            </div>
        </div>

        <script>
            function searchNiche(keyword, category) {
                document.getElementById('search-query').value = keyword;
                document.getElementById('search-category').value = category;
                // Dispatch submit event to jQuery form
                $('#search-form').submit();
            }
        </script>

        <!-- Marketplaces selection row -->
        <div class="mb-4">
            <label class="form-label mb-2 fw-semibold text-white">Marketplaces para Pesquisa Simultânea</label>
            <div class="d-flex flex-wrap">
                <!-- Shopee -->
                <div class="marketplace-badge active" data-code="shopee">
                    <input type="checkbox" value="shopee" checked>
                    <i class="fa-solid fa-bag-shopping me-2 text-warning"></i> Shopee Brasil
                </div>
                <!-- Mercado Livre -->
                <div class="marketplace-badge active" data-code="mercadolivre">
                    <input type="checkbox" value="mercadolivre" checked>
                    <i class="fa-solid fa-handshake me-2 text-primary"></i> Mercado Livre
                </div>
                <!-- Amazon -->
                <div class="marketplace-badge active" data-code="amazon">
                    <input type="checkbox" value="amazon" checked>
                    <i class="fa-brands fa-amazon me-2 text-warning"></i> Amazon BR
                </div>
                <!-- TikTok Shop -->
                <div class="marketplace-badge active" data-code="tiktok">
                    <input type="checkbox" value="tiktok" checked>
                    <i class="fa-brands fa-tiktok me-2 text-white"></i> TikTok Shop
                </div>
                <!-- Magalu -->
                <div class="marketplace-badge active" data-code="magalu">
                    <input type="checkbox" value="magalu" checked>
                    <i class="fa-solid fa-store me-2 text-info"></i> Magalu
                </div>
                <!-- Casas Bahia -->
                <div class="marketplace-badge active" data-code="casasbahia">
                    <input type="checkbox" value="casasbahia" checked>
                    <i class="fa-solid fa-house me-2 text-danger"></i> Casas Bahia
                </div>
                <!-- AliExpress -->
                <div class="marketplace-badge active" data-code="aliexpress">
                    <input type="checkbox" value="aliexpress" checked>
                    <i class="fa-solid fa-plane me-2 text-danger"></i> AliExpress
                </div>
                <!-- Temu -->
                <div class="marketplace-badge active" data-code="temu">
                    <input type="checkbox" value="temu" checked>
                    <i class="fa-solid fa-cart-shopping me-2 text-warning"></i> Temu
                </div>
                <!-- Shein -->
                <div class="marketplace-badge active" data-code="shein">
                    <input type="checkbox" value="shein" checked>
                    <i class="fa-solid fa-shirt me-2 text-success"></i> Shein
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-5" style="border-radius: 12px; height: 48px; background: linear-gradient(135deg, var(--accent-turquoise), #04beac); border: none; color: #0b0c16; font-weight: 700;">
            <i class="fa-solid fa-circle-nodes me-2"></i> Iniciar Varredura Paralela
        </button>
    </form>
</div>
