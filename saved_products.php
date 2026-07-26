<?php
/**
 * TrendHunter Brasil - Produtos Salvos & Gerador de Anúncios IA
 * Central de gestão de favoritos e criação automatizada de anúncios
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

use TrendHunter\Database;

$db = Database::getConnection();

// Retrieve all favorited products for the logged-in user
$stmt = $db->prepare("
    SELECT p.*, f.id AS favorite_id, f.created_at AS saved_at
    FROM products p
    INNER JOIN favorites f ON f.product_id = p.id
    WHERE f.user_id = ?
    ORDER BY f.id DESC
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="fw-bold text-white mb-1">
                <i class="fa-regular fa-heart text-danger me-2"></i> Meus Produtos Salvos & IA
            </h2>
            <p class="text-muted mb-0">
                Gerencie seus produtos favoritos e crie anúncios de alta conversão (Títulos SEO, Copy AIDA e Roteiros de Vídeo) com Inteligência Artificial.
            </p>
        </div>
        <?php if (!empty($favorites)): ?>
        <div class="d-flex flex-wrap gap-2">
            <button onclick="generateAdsForSelected()" class="btn btn-turquoise fw-bold px-4 py-2 shadow-sm" id="btn-generate-selected">
                <i class="fa-solid fa-wand-magic-sparkles me-2 animate-pulse"></i> Gerar IA (Selecionados)
            </button>
            <button onclick="generateAdsForAll()" class="btn btn-outline-purple fw-bold px-3 py-2" id="btn-generate-all">
                <i class="fa-solid fa-layer-group me-2"></i> Gerar IA (Todos os <?php echo count($favorites); ?>)
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($favorites)): ?>
    <div class="card-premium p-5 text-center my-4">
        <div class="py-4">
            <i class="fa-regular fa-heart text-muted mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
            <h4 class="text-white fw-bold">Nenhum produto salvo ainda</h4>
            <p class="text-muted max-w-500 mx-auto">
                Para salvar produtos aqui, acesse o <strong>Pesquisador Principal</strong> ou o <strong>Kalodata TikTok Intel</strong> e clique no ícone de coração (<i class="fa-regular fa-heart text-danger"></i>) nos produtos que você deseja divulgar.
            </p>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="index.php" class="btn btn-turquoise px-4 py-2 fw-bold">
                    <i class="fa-solid fa-magnifying-glass me-2"></i> Pesquisar Produtos
                </a>
                <a href="kalodata.php" class="btn btn-outline-light px-4 py-2 fw-bold">
                    <i class="fa-brands fa-tiktok text-danger me-2"></i> Produtos Virais TikTok
                </a>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- Tabela de Produtos Salvos -->
    <div class="card-premium p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom border-light-subtle">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="check-all-saved" checked>
                    <label class="form-check-label text-white fw-semibold small" for="check-all-saved">Selecionar Todos</label>
                </div>
                <span class="badge bg-purple-glow text-accent-purple px-3 py-1"><?php echo count($favorites); ?> PRODUTOS SALVOS</span>
            </div>
            <div>
                <button onclick="removeSelectedFavorites()" class="btn btn-sm btn-outline-danger" id="btn-remove-selected">
                    <i class="fa-solid fa-trash-can me-1"></i> Remover Selecionados
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="saved-products-table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 40px;" class="text-center">Sel.</th>
                        <th style="width: 70px;">Foto</th>
                        <th>Produto</th>
                        <th>Plataforma</th>
                        <th>Preço</th>
                        <th>Vendas Est.</th>
                        <th>Tendência</th>
                        <th class="text-end" style="min-width: 190px;">Ações IA & Salvo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($favorites as $p): ?>
                        <tr id="row-saved-<?php echo (int)$p['id']; ?>" class="saved-row">
                            <td class="text-center">
                                <input class="form-check-input product-check" type="checkbox" value="<?php echo (int)$p['id']; ?>" checked>
                            </td>
                            <td>
                                <img src="<?php echo htmlspecialchars($p['image_url'] ?: 'assets/img/no-image.png'); ?>" 
                                     alt="Thumb" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-white text-truncate" style="max-width: 260px;" title="<?php echo htmlspecialchars($p['title']); ?>">
                                    <?php echo htmlspecialchars($p['title']); ?>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="fa-solid fa-store me-1"></i> <?php echo htmlspecialchars($p['store_name'] ?: 'Loja Verificada'); ?>
                                    <?php if (!empty($p['category'])): ?>
                                        | <span class="badge bg-dark text-muted"><?php echo htmlspecialchars($p['category']); ?></span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $mp = strtolower((string)$p['marketplace']);
                                $badgeClass = 'bg-secondary';
                                if (str_contains($mp, 'mercadolivre')) $badgeClass = 'bg-warning text-dark';
                                elseif (str_contains($mp, 'shopee')) $badgeClass = 'bg-danger text-white';
                                elseif (str_contains($mp, 'tiktok')) $badgeClass = 'bg-dark text-info border border-info';
                                elseif (str_contains($mp, 'amazon')) $badgeClass = 'bg-primary text-white';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?> text-uppercase px-2 py-1" style="font-size: 10px;">
                                    <?php echo htmlspecialchars($p['marketplace']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-accent-turquoise" style="font-size: 15px;">
                                    R$ <?php echo number_format((float)$p['price'], 2, ',', '.'); ?>
                                </div>
                                <?php if (!empty($p['original_price']) && (float)$p['original_price'] > (float)$p['price']): ?>
                                    <small class="text-muted text-decoration-line-through" style="font-size: 11px;">
                                        R$ <?php echo number_format((float)$p['original_price'], 2, ',', '.'); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-white fw-semibold">
                                    <?php echo number_format((int)($p['sales_count_est'] ?? 0), 0, '', '.'); ?>
                                </span>
                                <small class="text-muted d-block">vendas</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px; background-color: rgba(255,255,255,0.1);">
                                        <div class="progress-bar bg-turquoise" role="progressbar" style="width: <?php echo min(100, (int)($p['trend_score'] ?? 70)); ?>%"></div>
                                    </div>
                                    <span class="text-white small fw-bold"><?php echo (int)($p['trend_score'] ?? 70); ?></span>
                                </div>
                            </td>
                            <td class="text-end">
                                <button onclick="generateAdForSingle(<?php echo (int)$p['id']; ?>, '<?php echo addslashes($p['title']); ?>')" 
                                        class="btn btn-sm btn-purple-glow text-accent-purple me-1" title="Criar Anúncio IA para este produto">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> IA
                                </button>
                                <?php if (!empty($p['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank" class="btn btn-sm btn-outline-light me-1" title="Ver no Marketplace">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                <?php endif; ?>
                                <button onclick="removeItemFromSaved(<?php echo (int)$p['id']; ?>)" 
                                        class="btn btn-sm btn-outline-danger" title="Remover dos Salvos">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Seção do Gerador de Anúncios IA Gerados -->
    <div class="card-premium p-4 mb-5" id="ai-ads-output-card" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-light-subtle pb-3">
            <div>
                <h4 class="fw-bold text-white mb-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-accent-purple me-2"></i> Anúncios Gerados pela Inteligência Artificial
                </h4>
                <p class="text-muted small mb-0">Títulos SEO, Copys de alta conversão (Método AIDA) e Roteiros para Vídeos Virais prontas para copiar.</p>
            </div>
            <button onclick="copyAllGeneratedAds()" class="btn btn-turquoise fw-bold px-3 py-2">
                <i class="fa-regular fa-copy me-2"></i> Copiar Todos os Anúncios
            </button>
        </div>

        <!-- Spinner de Carregamento IA -->
        <div id="ai-loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-accent-purple mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Gerando com Inteligência Artificial...</span>
            </div>
            <h5 class="text-white fw-bold">Criando Anúncios de Alta Conversão com IA...</h5>
            <p class="text-muted small">Analisando o gatilho mental, benefícios e criando roteiros de vídeo focados no público brasileiro.</p>
        </div>

        <!-- Área dos Anúncios Gerados -->
        <div id="ai-ads-list" class="row g-4"></div>
    </div>

<?php endif; ?>

<script>
$(document).ready(function() {
    // Check All / Uncheck All functionality
    $('#check-all-saved').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.product-check').prop('checked', isChecked);
    });

    // Individual checkbox change checks if select all should be checked
    $(document).on('change', '.product-check', function() {
        const total = $('.product-check').length;
        const checked = $('.product-check:checked').length;
        $('#check-all-saved').prop('checked', total === checked);
    });
});

function getSelectedProductIds() {
    const ids = [];
    $('.product-check:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function generateAdsForSelected() {
    const ids = getSelectedProductIds();
    if (ids.length === 0) {
        alert('Selecione pelo menos um produto salvo para gerar os anúncios.');
        return;
    }
    runAiAdGenerator(ids);
}

function generateAdsForAll() {
    const ids = [];
    $('.product-check').each(function() {
        ids.push($(this).val());
    });
    if (ids.length === 0) {
        alert('Não há produtos salvos para gerar anúncios.');
        return;
    }
    runAiAdGenerator(ids);
}

function generateAdForSingle(productId, productTitle) {
    runAiAdGenerator([productId]);
}

function runAiAdGenerator(productIds) {
    $('#ai-ads-output-card').fadeIn(400);
    $('#ai-loading-spinner').show();
    $('#ai-ads-list').empty();

    // Rolagem suave até a área de geração
    $('html, body').animate({
        scrollTop: $('#ai-ads-output-card').offset().top - 80
    }, 500);

    $.ajax({
        url: 'api.php?action=generate_ads',
        method: 'POST',
        data: {
            product_ids: productIds
        },
        success: function(response) {
            $('#ai-loading-spinner').hide();
            if (response.success && response.kits && response.kits.length > 0) {
                renderAiAds(response.kits);
            } else {
                $('#ai-ads-list').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                        <h6 class="text-white">Não foi possível gerar os anúncios</h6>
                        <p class="text-muted small">Tente novamente em alguns instantes.</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#ai-loading-spinner').hide();
            $('#ai-ads-list').html(`
                <div class="col-12 text-center py-5">
                    <i class="fa-solid fa-circle-xmark text-danger fs-1 mb-3"></i>
                    <h6 class="text-white">Erro de Comunicação com o Servidor IA</h6>
                    <p class="text-muted small">Verifique sua conexão e tente novamente.</p>
                </div>
            `);
        }
    });
}

function renderAiAds(kits) {
    const container = $('#ai-ads-list');
    container.empty();

    kits.forEach((kit, index) => {
        const hashtagsFormatted = Array.isArray(kit.hashtags) ? kit.hashtags.join(' ') : (kit.hashtags || '');
        const cardHtml = `
            <div class="col-12 mb-3">
                <div class="p-4 rounded border border-light-subtle bg-dark-subtle" style="background-color: rgba(18, 20, 38, 0.75) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-light-subtle">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-purple-glow text-accent-purple me-2">ANÚNCIO #${index + 1}</span>
                            <h5 class="fw-bold text-white mb-0">${escapeHtml(kit.title || kit.product_title || 'Produto Selecionado')}</h5>
                        </div>
                        <button onclick="copySingleAdKit(${index})" class="btn btn-sm btn-outline-turquoise fw-bold" id="btn-copy-kit-${index}">
                            <i class="fa-regular fa-copy me-1"></i> Copiar Este Anúncio
                        </button>
                    </div>

                    <div class="row g-3">
                        <!-- Coluna 1: Título SEO & Copy AIDA -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="text-accent-turquoise fw-bold small text-uppercase mb-1">
                                    <i class="fa-solid fa-heading me-1"></i> Título Otimizado para Vendas (SEO & CTR)
                                </label>
                                <div class="p-3 rounded bg-black border border-light-subtle text-white fw-semibold" id="kit-title-${index}">
                                    ${escapeHtml(kit.seo_title || kit.title_seo || '')}
                                </div>
                            </div>

                            <div>
                                <label class="text-accent-purple fw-bold small text-uppercase mb-1">
                                    <i class="fa-solid fa-fire me-1"></i> Copy de Vendas (Método AIDA: Atenção, Interesse, Desejo, Ação)
                                </label>
                                <div class="p-3 rounded bg-black border border-light-subtle text-light small" style="white-space: pre-wrap; line-height: 1.6;" id="kit-copy-${index}">${escapeHtml(kit.aida_copy || kit.copy_aida || '')}</div>
                            </div>
                        </div>

                        <!-- Coluna 2: Roteiro de Vídeo & Hashtags -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="text-warning fw-bold small text-uppercase mb-1">
                                    <i class="fa-brands fa-tiktok me-1"></i> Roteiro de Vídeo Curto (30 Segundos - Alta Retenção)
                                </label>
                                <div class="p-3 rounded bg-black border border-light-subtle text-light small" style="white-space: pre-wrap; line-height: 1.6;" id="kit-video-${index}">${escapeHtml(kit.tiktok_script || kit.video_script || '')}</div>
                            </div>

                            <div>
                                <label class="text-muted fw-bold small text-uppercase mb-1">
                                    <i class="fa-solid fa-hashtag me-1"></i> Hashtags Recomendadas
                                </label>
                                <div class="p-2 rounded bg-black border border-light-subtle text-accent-turquoise small" id="kit-tags-${index}">
                                    ${escapeHtml(hashtagsFormatted)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(cardHtml);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function copySingleAdKit(index) {
    const title = $('#kit-title-' + index).text().trim();
    const copy = $('#kit-copy-' + index).text().trim();
    const video = $('#kit-video-' + index).text().trim();
    const tags = $('#kit-tags-' + index).text().trim();

    const fullText = `=== TÍTULO DO ANÚNCIO ===\n${title}\n\n=== COPY DE VENDAS (AIDA) ===\n${copy}\n\n=== ROTEIRO PARA VÍDEO (30S) ===\n${video}\n\n=== HASHTAGS ===\n${tags}`;

    navigator.clipboard.writeText(fullText).then(() => {
        const btn = $('#btn-copy-kit-' + index);
        const origText = btn.html();
        btn.html('<i class="fa-solid fa-check me-1"></i> Copiado!');
        btn.removeClass('btn-outline-turquoise').addClass('btn-turquoise');
        setTimeout(() => {
            btn.html(origText);
            btn.removeClass('btn-turquoise').addClass('btn-outline-turquoise');
        }, 2000);
    });
}

function copyAllGeneratedAds() {
    let fullText = "";
    $('#ai-ads-list .col-12.mb-3').each(function(i) {
        const title = $(this).find('[id^="kit-title-"]').text().trim();
        const copy = $(this).find('[id^="kit-copy-"]').text().trim();
        const video = $(this).find('[id^="kit-video-"]').text().trim();
        const tags = $(this).find('[id^="kit-tags-"]').text().trim();

        fullText += `--- ANÚNCIO #${i+1} ---\n`;
        fullText += `TÍTULO: ${title}\n\n`;
        fullText += `COPY (AIDA):\n${copy}\n\n`;
        fullText += `ROTEIRO DE VÍDEO:\n${video}\n\n`;
        fullText += `HASHTAGS:\n${tags}\n`;
        fullText += `========================================\n\n`;
    });

    if (!fullText) {
        alert('Nenhum anúncio gerado para copiar.');
        return;
    }

    navigator.clipboard.writeText(fullText).then(() => {
        alert('Todos os anúncios foram copiados para sua área de transferência!');
    });
}

function removeItemFromSaved(productId) {
    if (!confirm('Deseja remover este produto dos seus produtos salvos?')) return;

    $.post('api.php?action=remove_favorite', { product_id: productId }, function(response) {
        if (response.success) {
            $('#row-saved-' + productId).fadeOut(300, function() {
                $(this).remove();
                const remaining = $('.saved-row').length;
                if (remaining === 0) {
                    location.reload();
                }
            });
        } else {
            alert(response.error || 'Erro ao remover item.');
        }
    });
}

function removeSelectedFavorites() {
    const ids = getSelectedProductIds();
    if (ids.length === 0) {
        alert('Selecione pelo menos um produto para remover.');
        return;
    }

    if (!confirm(`Deseja remover ${ids.length} produto(s) dos seus salvos?`)) return;

    let completed = 0;
    ids.forEach(id => {
        $.post('api.php?action=remove_favorite', { product_id: id }, function() {
            completed++;
            $('#row-saved-' + id).remove();
            if (completed === ids.length) {
                if ($('.saved-row').length === 0) {
                    location.reload();
                }
            }
        });
    });
}
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
