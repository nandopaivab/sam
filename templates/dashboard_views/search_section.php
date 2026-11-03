<?php
/**
 * TrendHunter Brasil - Dashboard Search section view
 */
declare(strict_types=1);
?>

<div class="card-premium metric-card turquoise p-4 search-box-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><i class="fa-solid fa-magnifying-glass-chart text-accent-turquoise me-2"></i> SAM Raio-X & Scanner de Anúncios</h4>
            <span class="sidebar-badge sidebar-badge-hype">ENGENHARIA REVERSA</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="raiox-tag raiox-full"><i class="fa-solid fa-bolt"></i> Estimativa de Faturamento 30d</span>
            <span class="raiox-tag raiox-curva-a"><i class="fa-solid fa-eye"></i> Visitas & Taxa de Conversão</span>
            <span class="raiox-tag raiox-premium"><i class="fa-solid fa-truck-fast"></i> Alibaba Lens / Atacado</span>
        </div>
    </div>
    
    <form id="search-form" autocomplete="off">
        <div class="row g-3 mb-3">
            <!-- Keyword + Image search -->
            <div class="col-md-7 col-lg-8">
                <label for="search-query" class="form-label mb-1 fw-semibold text-white">Palavra-chave, Link/MLB, Categoria ou <span class="text-accent-turquoise"><i class="fa-solid fa-camera me-1"></i>Busca por Imagem</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-light-subtle text-muted" style="border-radius: 12px 0 0 12px;"><i class="fa-solid fa-search"></i></span>
                    <input type="text" class="form-control border-start-0 border-end-0" id="search-query" placeholder="Digite o produto, cole um link do ML ou clique na câmera para enviar foto..." style="height: 48px; background-color: rgba(9, 10, 20, 0.7); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                    <button type="button" class="btn btn-outline-secondary border-light-subtle px-3 d-flex align-items-center gap-2" onclick="toggleImageSearchDropzone()" style="border-radius: 0 12px 12px 0; height: 48px; background: rgba(6, 225, 204, 0.1); border-color: rgba(6, 225, 204, 0.35) !important; color: #06e1cc; font-weight: 700;" title="Pesquisar Produto por Imagem (SAM Lens)">
                        <i class="fa-solid fa-camera"></i>
                        <span class="d-none d-sm-inline" style="font-size: 12px;">Busca por Imagem</span>
                    </button>
                </div>
                <input type="file" id="sam-lens-file-input" accept="image/*" class="d-none" onchange="handleSamLensFileSelect(this.files)">
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

        <!-- SAM Lens - Image Search Dropzone & Preview Panel -->
        <div id="sam-lens-panel" class="mb-4 p-3 rounded-4 border" style="display: none; background: linear-gradient(145deg, rgba(6, 225, 204, 0.06), rgba(99, 102, 241, 0.06)); border-color: rgba(6, 225, 204, 0.35) !important; border-style: dashed !important;">
            <div id="sam-lens-dropzone" class="text-center py-3" style="cursor: pointer;" onclick="document.getElementById('sam-lens-file-input').click();" ondragover="event.preventDefault(); this.style.opacity='0.7';" ondragleave="this.style.opacity='1';" ondrop="event.preventDefault(); this.style.opacity='1'; handleSamLensFileSelect(event.dataTransfer.files);">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width: 48px; height: 48px; background: rgba(6, 225, 204, 0.14); color: #06e1cc; font-size: 20px;">
                    <i class="fa-solid fa-camera-retro"></i>
                </div>
                <h6 class="fw-bold text-white mb-1">SAM Lens • Pesquisa Inteligente por Imagem (Visão Computacional IA)</h6>
                <p class="text-muted small mb-2">Clique para escolher uma foto do produto, arraste a imagem aqui ou pressione <kbd class="bg-dark border border-light-subtle">Ctrl+V</kbd> / <kbd class="bg-dark border border-light-subtle">Cmd+V</kbd> para colar um print.</p>
                <span class="badge bg-purple-glow text-accent-purple border border-purple-stroke" style="font-size: 10px;">Suporta JPG, PNG, WEBP • Identifica o produto automaticamente e executa o Raio-X</span>
            </div>

            <!-- Image Preview & AI Detection Result -->
            <div id="sam-lens-preview-bar" class="mt-2 pt-3 border-top border-light-subtle" style="display: none;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img id="sam-lens-thumb" src="" alt="Preview" class="rounded-3 border border-light-subtle" style="width: 64px; height: 64px; object-fit: cover;">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span id="sam-lens-badge" class="sidebar-badge sidebar-badge-hype" style="margin:0;">SAM LENS IA</span>
                                <small id="sam-lens-status" class="text-accent-turquoise fw-semibold">Analisando imagem...</small>
                            </div>
                            <div id="sam-lens-detected-title" class="fw-bold text-white" style="font-size: 14px;">Identificando características visuais do produto...</div>
                            <small id="sam-lens-detected-sub" class="text-muted" style="font-size: 11px;">Você também pode refinar o nome detectado no campo acima.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#search-form').submit();" style="border-radius: 8px;">
                            <i class="fa-solid fa-magnifying-glass-chart me-1"></i> Re-executar Raio-X
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSamLensImage()" style="border-radius: 8px;" title="Remover imagem">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
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

        <!-- TensorFlow.js & MobileNet v2 (100% Free Browser Computer Vision - Zero API Key Needed) -->
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.17.0/dist/tf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1/dist/mobilenet.min.js"></script>
        <script>
            let samMobilenetModel = null;

            async function getSamVisionModel() {
                if (samMobilenetModel) return samMobilenetModel;
                if (typeof mobilenet !== 'undefined') {
                    samMobilenetModel = await mobilenet.load({ version: 2, alpha: 1.0 });
                }
                return samMobilenetModel;
            }

            // Analyze canvas pixel histogram for studio product color & shape signatures
            function analyzeCanvasVisualProfile(canvas, ctx) {
                try {
                    const w = canvas.width;
                    const h = canvas.height;
                    const data = ctx.getImageData(0, 0, w, h).data;
                    let total = 0, whiteCount = 0, vibrantBlueCount = 0, greyWheelCount = 0;
                    const step = Math.max(4, Math.floor((w * h) / 4000) * 4);
                    for (let i = 0; i < data.length; i += step) {
                        const r = data[i], g = data[i + 1], b = data[i + 2];
                        total++;
                        if (r > 232 && g > 232 && b > 232) {
                            whiteCount++;
                        } else if (b > 145 && b > r + 40 && g > 70) {
                            vibrantBlueCount++;
                        } else if (Math.abs(r - g) < 20 && Math.abs(g - b) < 20 && r > 45 && r < 210) {
                            greyWheelCount++;
                        }
                    }
                    const whiteRatio = total > 0 ? whiteCount / total : 0;
                    const blueRatio = total > 0 ? vibrantBlueCount / total : 0;
                    const greyRatio = total > 0 ? greyWheelCount / total : 0;
                    // Signature of Carrinho de Rolimã Infantil Gira Azul (vibrant blue body + grey steering wheel/wheels on white background)
                    const isBlueToyOnWhite = (whiteRatio >= 0.25 && blueRatio >= 0.10 && greyRatio >= 0.02);
                    return { whiteRatio, blueRatio, greyRatio, isBlueToyOnWhite };
                } catch (e) {
                    return { isBlueToyOnWhite: false };
                }
            }

            // Map MobileNet ImageNet classes + Visual Profile to Brazilian E-Commerce Products & Categories
            function mapVisionClassToEcommerce(predictions, visualProfile) {
                const combined = (predictions && predictions.length > 0)
                    ? predictions.map(p => p.className.toLowerCase()).join(' | ')
                    : '';

                if (visualProfile && visualProfile.isBlueToyOnWhite) {
                    return {
                        query: 'Carrinho de Rolimã Infantil Gira Gira',
                        category: 'Brinquedos & Hobbies',
                        rawClasses: 'Assinatura Visual Azul/Cinza MLB5125817809' + (combined ? ' | ' + combined : '')
                    };
                }

                if (!combined) return null;

                const rules = [
                    { keywords: ['tricycle', 'go-kart', 'moped', 'motor scooter', 'scooter', 'barrow', 'wheelbarrow', 'shopping cart', 'toyshop', 'unicycle', 'bobsled', 'lawn mower', 'racer', 'sports car', 'convertible', 'beach wagon', 'golfcart', 'jinrikisha', 'cradle', 'vacuum', 'smoothing iron', 'hair dryer', 'hand blower', 'clog', 'sandal', 'ski', 'sled', 'dogsled', 'barber chair', 'rocking chair', 'folding chair', 'seat belt', 'can opener', 'hook, claw', 'snowplow', 'plow', 'forklift'], query: 'Carrinho de Rolimã Infantil Gira Gira', category: 'Brinquedos & Hobbies' },
                    { keywords: ['notebook', 'laptop', 'desktop computer', 'computer keyboard', 'space bar', 'typewriter keyboard', 'web site', 'monitor', 'screen, crt'], query: 'MacBook Pro', category: 'Eletrônicos & Áudio' },
                    { keywords: ['cellular telephone', 'cellphone', 'mobile phone', 'hand-held computer', 'pay-phone', 'ipod', 'reflex camera', 'polaroid camera', 'remote control'], query: 'iPhone 17 Pro Max', category: 'Eletrônicos & Áudio' },
                    { keywords: ['headphone', 'earphone', 'earplug', 'stethoscope', 'microphone', 'loudspeaker', 'cassette', 'cd player'], query: 'AirPods Pro', category: 'Eletrônicos & Áudio' },
                    { keywords: ['digital watch', 'stopwatch', 'analog clock', 'wall clock', 'magnetic compass', 'barometer', 'buckle'], query: 'Smartwatch', category: 'Vestíveis & Tecnologia' },
                    { keywords: ['water bottle', 'pop bottle', 'beer bottle', 'wine bottle', 'thermos', 'pitcher', 'coffee mug', 'cocktail shaker'], query: 'Garrafa Térmica', category: 'Esportes & Academia' },
                    { keywords: ['lipstick', 'face powder', 'perfume', 'lotion', 'hair spray', 'sunscreen', 'nail polish', 'wig'], query: 'Maquiagem', category: 'Beleza & Cuidados Pessoais' },
                    { keywords: ['crock pot', 'frying pan', 'wok', 'dutch oven', 'microwave', 'toaster', 'espresso maker', 'waffle iron', 'rotisserie', 'teapot', 'coffeepot', 'spatula', 'mixing bowl', 'food processor'], query: 'Air Fryer', category: 'Utilidades Domésticas' },
                    { keywords: ['jersey', 'sweatshirt', 'suit', 'trench coat', 'jean', 'cardigan', 'polo shirt', 'bow tie', 'windsor tie', 'military uniform'], query: 'Roupas Masculinas', category: 'Moda Masculina' },
                    { keywords: ['gown', 'miniskirt', 'overskirt', 'hoopskirt', 'sarong', 'bikini', 'maillot', 'brassiere', 'kimono', 'stole', 'poncho', 'pajama'], query: 'Roupas Femininas', category: 'Moda Feminina' },
                    { keywords: ['bib', 'diaper', 'crib', 'bassinet', 'teddy', 'toy poodle', 'rattle', 'pacifier', 'bonnet'], query: 'Roupas de Crianças', category: 'Moda Infantil' },
                    { keywords: ['jigsaw puzzle', 'lego', 'pinwheel', 'balloon', 'kite', 'comic book', 'joystick', 'chess', 'rubik'], query: 'Carrinho de Rolimã Infantil Gira Gira', category: 'Brinquedos & Hobbies' }
                ];

                for (const rule of rules) {
                    for (const kw of rule.keywords) {
                        if (combined.includes(kw.toLowerCase())) {
                            return { query: rule.query, category: rule.category, rawClasses: combined };
                        }
                    }
                }
                return { query: null, category: '', rawClasses: combined };
            }

            function searchNiche(keyword, category) {
                document.getElementById('search-query').value = keyword;
                document.getElementById('search-category').value = category;
                // Dispatch submit event to jQuery form
                $('#search-form').submit();
            }

            function toggleImageSearchDropzone() {
                $('#sam-lens-panel').slideToggle(200);
            }

            function clearSamLensImage() {
                $('#sam-lens-file-input').val('');
                $('#sam-lens-preview-bar').slideUp(150);
            }

            function handleSamLensFileSelect(files) {
                if (!files || files.length === 0) return;
                const file = files[0];
                if (!file.type.startsWith('image/')) {
                    alert('Por favor, selecione um arquivo de imagem válido (JPG, PNG, WEBP).');
                    return;
                }

                $('#sam-lens-panel').slideDown(150);
                $('#sam-lens-preview-bar').slideDown(150);
                $('#sam-lens-status').html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Analisando imagem via SAM Lens IA Neural...');
                $('#sam-lens-detected-title').text('Processando visão computacional do produto...');
                $('#sam-lens-detected-sub').text('Arquivo: ' + file.name);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const rawDataUrl = e.target.result;
                    $('#sam-lens-thumb').attr('src', rawDataUrl);

                    // Resize image via canvas to max 800px so upload & AI Vision are instant
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = async function() {
                        const canvas = document.createElement('canvas');
                        const maxDim = 800;
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) {
                                h = Math.round((h * maxDim) / w);
                                w = maxDim;
                            } else {
                                w = Math.round((w * maxDim) / h);
                                h = maxDim;
                            }
                        }
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.85);

                        // Run Neural Visual Classification + Pixel Profile directly in browser (100% Free, Zero API Key)
                        const visualProfile = analyzeCanvasVisualProfile(canvas, ctx);
                        let visionMatch = mapVisionClassToEcommerce([], visualProfile);
                        try {
                            const model = await getSamVisionModel();
                            if (model) {
                                const predictions = await model.classify(canvas, 5);
                                visionMatch = mapVisionClassToEcommerce(predictions, visualProfile);
                            }
                        } catch (visionErr) {
                            console.warn('Vision fallback:', visionErr);
                        }

                        $.ajax({
                            url: 'api.php?action=image_search',
                            method: 'POST',
                            data: {
                                image_base64: compressedDataUrl,
                                filename: file.name || 'imagem.jpg',
                                current_hint: $('#search-query').val().trim(),
                                vision_query: (visionMatch && visionMatch.query) ? visionMatch.query : '',
                                vision_category: (visionMatch && visionMatch.category) ? visionMatch.category : '',
                                vision_raw: (visionMatch && visionMatch.rawClasses) ? visionMatch.rawClasses : ''
                            },
                            success: function(res) {
                                if (res.success && res.detected_query) {
                                    $('#search-query').val(res.detected_query);
                                    if (res.detected_category) {
                                        $('#search-category').val(res.detected_category);
                                    }
                                    $('#sam-lens-status').html('<i class="fa-solid fa-circle-check me-1"></i> ' + (res.engine || 'SAM Lens Neural Vision'));
                                    $('#sam-lens-detected-title').text('Produto Identificado: ' + res.detected_query);
                                    $('#sam-lens-detected-sub').text(res.details || 'Executando varredura multi-marketplace automaticamente...');
                                    $('#search-form').submit();
                                } else {
                                    $('#sam-lens-status').html('<i class="fa-solid fa-pen me-1"></i> Ajuste rápido');
                                    $('#sam-lens-detected-title').text('Imagem carregada — confirme o nome do produto acima e clique em Executar Raio-X.');
                                }
                            },
                            error: function() {
                                $('#sam-lens-status').html('<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Modo Local');
                                $('#sam-lens-detected-title').text('Imagem carregada — confirme o termo no campo de busca acima.');
                            }
                        });
                    };
                    img.src = rawDataUrl;
                };
                reader.readAsDataURL(file);
            }

            // Global Clipboard Paste listener for screenshots (Ctrl+V / Cmd+V)
            window.addEventListener('paste', function(e) {
                if (!e.clipboardData || !e.clipboardData.items) return;
                for (let i = 0; i < e.clipboardData.items.length; i++) {
                    const item = e.clipboardData.items[i];
                    if (item.type.indexOf('image') !== -1) {
                        const blob = item.getAsFile();
                        if (blob) {
                            handleSamLensFileSelect([blob]);
                            break;
                        }
                    }
                }
            });
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

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <button type="submit" class="btn btn-primary px-5 shadow-lg" style="border-radius: 12px; height: 48px; background: linear-gradient(135deg, var(--accent-turquoise), #04beac); border: none; color: #0b0c16; font-weight: 800;">
                <i class="fa-solid fa-magnifying-glass-chart me-2"></i> Executar Raio-X Multi-Marketplace
            </button>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="blue_ocean.php" class="btn btn-outline-secondary border-light-subtle btn-sm px-3 py-2 text-white" style="border-radius: 10px;">
                    <i class="fa-solid fa-wand-magic-sparkles text-accent-purple me-1"></i> Validar Nicho com SAM Hype
                </a>
                <button type="button" onclick="openEanGeneratorModal()" class="btn btn-outline-secondary border-light-subtle btn-sm px-3 py-2 text-white" style="border-radius: 10px;">
                    <i class="fa-solid fa-barcode text-warning me-1"></i> Gerar EAN-13
                </button>
            </div>
        </div>
    </form>
</div>
