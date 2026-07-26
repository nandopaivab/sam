<?php
/**
 * TrendHunter Brasil - Affiliate Ad & Listing Generator
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

// Retrieve all favorited products for the logged-in user
$stmt = $db->prepare("
    SELECT p.*, f.id AS favorite_id 
    FROM products p
    INNER JOIN favorites f ON f.product_id = p.id
    WHERE f.user_id = ?
    ORDER BY f.id DESC
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-white mb-1"><i class="fa-solid fa-wand-magic-sparkles text-accent-purple me-2"></i> Gerador de Anúncios em Lote</h2>
        <p class="text-muted">Selecione seus favoritos ou escolha uma plataforma para gerar títulos SEO, copys de vendas (AIDA) e roteiros de vídeos virais em lote.</p>
    </div>
</div>

<div class="row">
    <!-- Left Column: Automation Controls -->
    <div class="col-xl-4 mb-4">
        
        <!-- Card 1: Elite Top 10 Generator -->
        <div class="card-premium p-4 mb-4">
            <h5 class="fw-bold text-white mb-2"><i class="fa-solid fa-bolt text-warning me-2"></i> Gerador Elite: Top 10 Vendas</h5>
            <p class="text-muted small">Pesquisa e gera anúncios das 10 principais ofertas da plataforma com base em volume de vendas e taxa de crescimento.</p>
            
            <div class="mb-3">
                <label class="form-label text-white small">Escolha a Plataforma:</label>
                <select id="elite-platform-select" class="form-select bg-dark text-white border-secondary border-opacity-25 form-select-sm">
                    <option value="all">Todas as Plataformas (Misto)</option>
                    <option value="shopee">Shopee</option>
                    <option value="mercadolivre">Mercado Livre</option>
                    <option value="amazon">Amazon</option>
                    <option value="tiktok">TikTok Shop</option>
                </select>
            </div>
            
            <button id="btn-generate-elite" class="btn btn-purple-glow text-accent-purple w-100 fw-bold py-2"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Gerar Top 10 Anúncios</button>
        </div>

        <!-- Card 2: Favorites Selection List -->
        <div class="card-premium p-4">
            <h5 class="fw-bold text-white mb-3"><i class="fa-regular fa-heart text-danger me-2"></i> Seus Favoritos</h5>
            
            <?php if (empty($favorites)): ?>
                <div class="text-center py-4">
                    <i class="fa-regular fa-folder-open text-muted fs-1 mb-2"></i>
                    <h6 class="text-white">Nenhum favorito encontrado</h6>
                    <p class="text-muted small">Vá para o Pesquisador Principal, faça uma busca e clique no coração (❤️) dos produtos.</p>
                    <a href="index.php" class="btn btn-sm btn-turquoise mt-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Ir para Pesquisas</a>
                </div>
            <?php else: ?>
                <form id="ads-generator-form">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all-favs" checked>
                            <label class="form-check-label text-white small" for="select-all-favs">Selecionar Todos</label>
                        </div>
                        <span class="badge bg-purple-glow text-accent-purple small"><?php echo count($favorites); ?> itens</span>
                    </div>

                    <div style="max-height: 250px; overflow-y: auto; padding-right: 5px;" class="pe-2">
                        <?php foreach ($favorites as $f): ?>
                            <div class="p-2 mb-2 rounded border border-light-subtle d-flex align-items-center" style="background-color: rgba(255,255,255,0.01);">
                                <input class="form-check-input fav-checkbox me-3" type="checkbox" name="product_ids[]" value="<?php echo $f['id']; ?>" checked>
                                <img src="<?php echo htmlspecialchars($f['image_url']); ?>" alt="Foto" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div class="text-truncate flex-grow-1" style="max-width: 180px;">
                                    <strong class="text-white small text-truncate d-block" title="<?php echo htmlspecialchars($f['title']); ?>"><?php echo htmlspecialchars($f['title']); ?></strong>
                                    <small class="text-muted d-block">R$ <?php echo number_format((float)$f['price'], 2, ',', '.'); ?> | <span class="text-uppercase text-accent-turquoise fw-semibold" style="font-size: 9px;"><?php echo htmlspecialchars($f['marketplace']); ?></span></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-turquoise w-100 mt-3 py-2 fw-bold"><i class="fa-solid fa-wand-magic-sparkles me-2 animate-pulse"></i> Gerar Anúncios de Favoritos</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Generation Output Area -->
    <div class="col-xl-8 mb-4">
        <div class="card-premium p-4 h-100" id="generator-output-card">
            <h5 class="fw-bold text-white mb-3"><i class="fa-solid fa-file-invoice text-accent-turquoise me-2"></i> Material de Divulgação Gerado</h5>
            
            <!-- Placeholder state -->
            <div id="output-placeholder" class="text-center py-5">
                <i class="fa-solid fa-wand-magic-sparkles text-muted fs-1 mb-3"></i>
                <h6 class="text-white">Aguardando geração...</h6>
                <p class="text-muted small">Selecione os favoritos ou use o painel de elite acima para carregar copys e roteiros de vendas.</p>
            </div>

            <!-- Loader spinner -->
            <div id="output-loader" class="text-center py-5" style="display: none;">
                <i class="fa-solid fa-spinner fa-spin text-accent-purple fs-1 mb-3"></i>
                <h6 class="text-white">Criando campanhas com Inteligência Artificial...</h6>
                <p class="text-muted small">Gerando copys, tags SEO e roteiros de engajamento do TikTok Shop. Por favor, aguarde.</p>
            </div>

            <!-- Results contents -->
            <div id="output-container" style="display: none; max-height: 550px; overflow-y: auto;" class="pe-2">
                <!-- Generated cards injected dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Inline Javascript Operations -->
<script>
$(document).ready(function() {
    // Checkbox toggle all favorites selection
    $('#select-all-favs').on('change', function() {
        $('.fav-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Render loop helper
    window.renderGeneratedKits = function(kits) {
        const container = $('#output-container');
        container.empty();

        kits.forEach(kit => {
            const invideoPrompt = `Create a 30-second TikTok Shop video for "${kit.title}". Theme: Highly converting, engaging, and modern. Voiceover: Energetic and friendly Portuguese voice. First 3 seconds (Hook): Start with a major statement/struggle, e.g. "Pare tudo o que você está fazendo se você quer economizar tempo e dinheiro com isso...". 3 to 15 seconds (Body): Show the product resolving the issue instantly. Use bright close-up shots of "${kit.title}" in action. Emphasize its main benefits. 15 to 25 seconds (Benefits): Highlight key features as text overlay on screen. Last 5 seconds (CTA): Call to action: "Gostou? O link com o menor preço de afiliado e frete grátis tá na minha bio!". Visual style: Fast cuts, yellow/white captions, trending background music.`;
            const invideoPromptEscaped = invideoPrompt.replace(/"/g, '&quot;');

            const card = `
                <div class="card mb-4 rounded-3 border-0" style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                    <div class="card-header bg-transparent border-bottom border-light-subtle d-flex justify-content-between align-items-center py-3 flex-wrap">
                        <div class="d-flex align-items-center">
                            <img src="${kit.image_url}" alt="Foto" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0 text-white fw-bold text-truncate" style="max-width: 300px;">${kit.title}</h6>
                                <small class="text-muted">Origem: <span class="text-uppercase text-accent-turquoise fw-bold">${kit.marketplace}</span> | Preço Sugerido: <strong>R$ ${parseFloat(kit.suggested_price).toFixed(2).replace('.', ',')}</strong></small>
                            </div>
                        </div>
                        <button onclick="copyFullAdText(this, ${kit.id})" class="btn btn-sm btn-outline-turquoise mt-2 mt-md-0"><i class="fa-solid fa-copy me-1"></i> Copiar Kit Inteiro</button>
                    </div>
                    <div class="card-body p-4 text-white" id="kit-content-${kit.id}">
                        
                        <!-- 1. SEO Title Block -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-accent-purple small fw-bold"><i class="fa-solid fa-tags"></i> TÍTULO SEO SUGERIDO (Shopee / TikTok)</span>
                                <button onclick="copyToClipboard(this.getAttribute('data-text'), this)" data-text="${kit.seo_title.replace(/"/g, '&quot;')}" class="btn btn-link p-0 text-muted small" style="text-decoration:none;"><i class="fa-solid fa-clone me-1"></i>Copiar Título</button>
                            </div>
                            <div class="p-2 rounded bg-dark border border-secondary border-opacity-25 fw-semibold font-monospace" style="font-size:12px;">${kit.seo_title}</div>
                        </div>

                        <!-- 2. AIDA Sales Copy Block -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-accent-turquoise small fw-bold"><i class="fa-solid fa-message-captions"></i> DESCRIÇÃO DE VENDAS (MÉTODO AIDA)</span>
                                <button onclick="copyBlockContent('desc-box-${kit.id}', this)" class="btn btn-link p-0 text-muted small" style="text-decoration:none;"><i class="fa-solid fa-clone me-1"></i>Copiar Descrição</button>
                            </div>
                            <textarea id="desc-box-${kit.id}" class="form-control bg-dark border border-secondary border-opacity-25 text-white small" rows="5" readonly style="font-family: inherit; font-size:12px; resize:none;">${kit.aida_copy}</textarea>
                        </div>

                        <!-- 3. TikTok Viral Script Block -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-danger small fw-bold"><i class="fa-brands fa-tiktok"></i> ROTEIRO DE VÍDEO VIRAL (TIKTOK SHOP / REELS)</span>
                                <button onclick="copyBlockContent('script-box-${kit.id}', this)" class="btn btn-link p-0 text-muted small" style="text-decoration:none;"><i class="fa-solid fa-clone me-1"></i>Copiar Roteiro</button>
                            </div>
                            <textarea id="script-box-${kit.id}" class="form-control bg-dark border border-secondary border-opacity-25 text-white small" rows="5" readonly style="font-family: inherit; font-size:12px; resize:none;">${kit.tiktok_script}</textarea>
                        </div>

                        <!-- 4. Tags / Links Block -->
                        <div class="mb-3">
                            <span class="text-warning small fw-bold"><i class="fa-solid fa-hashtag"></i> HASHTAGS RECOMENDADAS</span>
                            <div class="p-2 rounded bg-dark border border-secondary border-opacity-25 text-muted small mt-1" style="font-size:11px;">
                                ${kit.hashtags}
                            </div>
                        </div>

                        <!-- 5. InVideo AI Video Prompt Block -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-info small fw-bold"><i class="fa-solid fa-video"></i> PROMPT DE GERAÇÃO VÍDEO IA (INVIDEO AI)</span>
                                <button onclick="copyToClipboard(this.getAttribute('data-text'), this)" data-text="${invideoPromptEscaped}" class="btn btn-link p-0 text-muted small" style="text-decoration:none;"><i class="fa-solid fa-clone me-1"></i>Copiar Prompt</button>
                            </div>
                            <textarea id="video-prompt-box-${kit.id}" class="form-control bg-dark border border-secondary border-opacity-25 text-white small" rows="3" readonly style="font-family: inherit; font-size:11px; resize:none;">${invideoPrompt}</textarea>
                            <small class="text-muted" style="font-size: 10px; margin-top: 3px; display: block;"><i class="fa-solid fa-circle-info me-1"></i> Copie o prompt acima, cole no <a href="https://invideo.io" target="_blank" class="text-accent-turquoise">invideo.io</a> ou CapCut e gere seu vídeo completo por inteligência artificial!</small>
                        </div>

                    </div>
                </div>
            `;
            container.append(card);
        });

        container.fadeIn(300);
    };

    // Favorites form submission
    $('#ads-generator-form').on('submit', function(e) {
        e.preventDefault();

        const selectedIds = [];
        $('.fav-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Por favor, selecione pelo menos um produto favoritado para gerar.');
            return;
        }

        $('#output-placeholder').hide();
        $('#output-container').hide();
        $('#output-loader').fadeIn(300);

        $.ajax({
            url: 'api.php?action=generate_ads',
            method: 'POST',
            data: {
                product_ids: selectedIds
            },
            success: function(response) {
                $('#output-loader').hide();
                if (response.success && response.kits.length > 0) {
                    renderGeneratedKits(response.kits);
                } else {
                    $('#output-placeholder').html(`
                        <div class="text-center py-5">
                            <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                            <h6 class="text-white">Falha na geração</h6>
                            <p class="text-muted small">Não foi possível gerar os anúncios de divulgação. Tente novamente mais tarde.</p>
                        </div>
                    `).show();
                }
            },
            error: function() {
                $('#output-loader').hide();
                $('#output-placeholder').html(`
                    <div class="text-center py-5">
                        <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                        <h6 class="text-white">Erro de Conexão</h6>
                        <p class="text-muted small">Ocorreu um erro ao contatar o servidor de inteligência artificial.</p>
                    </div>
                `).show();
            }
        });
    });

    // Elite Top 10 button click handler
    $('#btn-generate-elite').on('click', function() {
        const platform = $('#elite-platform-select').val();

        $('#output-placeholder').hide();
        $('#output-container').hide();
        $('#output-loader').fadeIn(300);

        $.ajax({
            url: 'api.php?action=generate_top_ten',
            method: 'GET',
            data: {
                marketplace: platform
            },
            success: function(response) {
                $('#output-loader').hide();
                if (response.success && response.kits.length > 0) {
                    renderGeneratedKits(response.kits);
                } else {
                    $('#output-placeholder').html(`
                        <div class="text-center py-5">
                            <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                            <h6 class="text-white">Falha na geração</h6>
                            <p class="text-muted small">Não foi possível compilar os 10 melhores itens. Tente novamente mais tarde.</p>
                        </div>
                    `).show();
                }
            },
            error: function() {
                $('#output-loader').hide();
                $('#output-placeholder').html(`
                    <div class="text-center py-5">
                        <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                        <h6 class="text-white">Erro de Conexão</h6>
                        <p class="text-muted small">Ocorreu um erro ao contatar o servidor de inteligência artificial.</p>
                    </div>
                `).show();
            }
        });
    });

    // Helper functions to handle copy clipboard actions
    window.copyToClipboard = function(text, btnElement) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = $(btnElement).html();
            $(btnElement).html('<i class="fa-solid fa-check text-success me-1"></i>Copiado!');
            setTimeout(function() {
                $(btnElement).html(originalHtml);
            }, 2000);
        });
    };

    window.copyBlockContent = function(boxId, btnElement) {
        const text = document.getElementById(boxId).value;
        window.copyToClipboard(text, btnElement);
    };

    window.copyFullAdText = function(btnElement, kitId) {
        const title = $(`#kit-content-${kitId} div div + div`).first().text();
        const desc = document.getElementById(`desc-box-${kitId}`).value;
        const script = document.getElementById(`script-box-${kitId}`).value;
        
        const fullText = `--- TÍTULO DO ANÚNCIO ---\n${title}\n\n--- DESCRIÇÃO AIDA ---\n${desc}\n\n--- ROTEIRO TIKTOK ---\n${script}`;
        
        window.copyToClipboard(fullText, btnElement);
    };
});
</script>

<!-- Floating Profit margins & ROI Calculator Widget -->
<?php require __DIR__ . '/templates/dashboard_views/calculator_widget.php'; ?>

<!-- Modals wrapper templates -->
<?php require __DIR__ . '/templates/dashboard_views/modals.php'; ?>

<!-- Include Layout Footer -->
<?php require __DIR__ . '/templates/footer.php'; ?>
