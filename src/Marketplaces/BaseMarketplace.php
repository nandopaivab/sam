<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

use TrendHunter\Analysis\TrendScorer;

abstract class BaseMarketplace implements MarketplaceInterface {
    protected string $name;
    protected string $code;
    protected string $baseUrl;

    public function getName(): string {
        return $this->name;
    }

    public function getCode(): string {
        return $this->code;
    }

    /**
     * Common HTTP client helper
     */
    protected function makeRequest(string $url, string $method = 'GET', array $headers = [], ?string $body = null): ?string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $defaultHeaders = [
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $response;
        }

        return null;
    }

    /**
     * Generates realistic simulated marketplace product data when official APIs or scrapers are blocked or not configured.
     * This provides a highly functional, Helium10-level demo state immediately.
     */
    protected function generateSimulatedData(string $query, ?string $category, int $limit): array {
        $products = [];
        $query = trim(strtolower($query));
        
        // Define some product categories & templates based on typical search queries in Brazil
        $templates = [
            'garrafa' => [
                'titles' => [
                    'Garrafa Térmica 1.2L Stanley Vácuo Inox',
                    'Copo Térmico com Tampa Canudo 1.1L Parede Dupla',
                    'Garrafa de Água Motivacional Squeeze 2L Academia',
                    'Garrafa Térmica Infantil com Canudo Antivazamento 400ml',
                    'Garrafa Térmica Kouda Inox Vacuum Cup 500ml'
                ],
                'price_min' => 29.90,
                'price_max' => 299.90,
                'category' => 'Esportes & Academia',
                'image_prefix' => 'bottle'
            ],
            'fone' => [
                'titles' => [
                    'Fone de Ouvido Bluetooth Sem Fio Redmi Airdots 3',
                    'Headphone Bluetooth JBL Tune 510BT Sem Fio Original',
                    'Fone Bluetooth Esportivo Intra-auricular Resistente à Água',
                    'Fone de Ouvido AirPods Pro Bluetooth com Estojo de Recarga',
                    'Fone de Ouvido Bluetooth Gamer Altíssima Resolução Latência Zero'
                ],
                'price_min' => 45.00,
                'price_max' => 1299.00,
                'category' => 'Eletrônicos & Áudio',
                'image_prefix' => 'headphone'
            ],
            'maquiagem' => [
                'titles' => [
                    'Paleta de Sombras Ultimate 16 Cores Profissional',
                    'Lip Tint Gel Hidratante Longa Duração Boca Rosa',
                    'Base Líquida Matte Alta Cobertura FPS 15 Mari Maria',
                    'Kit de Pincéis de Maquiagem Profissional Cabo de Madeira',
                    'Máscara de Cílios Máximo Volume Alongadora Prova D\'Água'
                ],
                'price_min' => 15.90,
                'price_max' => 120.00,
                'category' => 'Beleza & Cuidados Pessoais',
                'image_prefix' => 'makeup'
            ],
            'smartwatch' => [
                'titles' => [
                    'Smartwatch Xiaomi Redmi Watch 3 Active Tela 1.83',
                    'Relógio Inteligente IWO 16 Ultra Serie 9 Bluetooth NFC',
                    'Smartwatch Esportivo GPS Integrado Batimentos Cardíacos',
                    'Smartwatch Monitor Cardíaco Bluetooth Notificações Android iOS',
                    'Relógio Inteligente Feminino Dourado com Pulseira de Aço'
                ],
                'price_min' => 89.90,
                'price_max' => 549.00,
                'category' => 'Vestíveis & Tecnologia',
                'image_prefix' => 'smartwatch'
            ],
            'cozinha' => [
                'titles' => [
                    'Fritadeira Sem Óleo Air Fryer Mondial 4L Digital',
                    'Jogo de Panelas Antiaderente Cerâmica 5 Peças Tramontina',
                    'Liquidificador Turbo 1200W Copo San Inquebrável 3L',
                    'Batedeira Planetária Profissional 8 Velocidades Arno',
                    'Mini Processador de Alimentos Elétrico USB Portátil Bivolt'
                ],
                'price_min' => 22.90,
                'price_max' => 489.00,
                'category' => 'Utilidades Domésticas',
                'image_prefix' => 'kitchen'
            ],
            'brinquedo' => [
                'titles' => [
                    'Blocos de Montar Infantil Lego Classic 120 Peças',
                    'Boneca Articulada Colecionável Infantil com Acessórios',
                    'Carrinho de Controle Remoto 4x4 Off-Road Recarregável',
                    'Jogo de Tabuleiro Clássico Família Divertido',
                    'Pista de Corrida Infantil com Carrinhos Lançadores Rápido'
                ],
                'price_min' => 29.90,
                'price_max' => 380.00,
                'category' => 'Brinquedos & Hobbies',
                'image_prefix' => 'toys'
            ],
            'roupas masculinas' => [
                'titles' => [
                    'Camiseta Masculina Algodão Pima Premium Lisa Gola Redonda',
                    'Calça Jeans Masculina Slim Fit com Elastano Amaciada',
                    'Camisa Social Masculina Slim Manga Longa Confort',
                    'Jaqueta Corta Vento Masculina Impermeável com Capuz',
                    'Bermuda Cargo Masculina Sarja Reforçada com Bolsos'
                ],
                'price_min' => 39.90,
                'price_max' => 189.90,
                'category' => 'Moda Masculina',
                'image_prefix' => 'mens_wear'
            ],
            'roupas femininas' => [
                'titles' => [
                    'Vestido Feminino Longo Canelado com Fenda Lateral',
                    'Blusa Feminina Tricô Lurex Manga Princesa Elegante',
                    'Calça Feminina Pantalona Alfaiataria Cintura Alta',
                    'Conjunto Feminino Moletom Cropped e Calça Jogger',
                    'Cardigan Feminino Longo Tricô Sobretudo Luxo'
                ],
                'price_min' => 34.90,
                'price_max' => 220.00,
                'category' => 'Moda Feminina',
                'image_prefix' => 'womens_wear'
            ],
            'roupas de crianças' => [
                'titles' => [
                    'Conjunto Infantil Masculino Camiseta e Bermuda Moletinho',
                    'Vestido Infantil Feminino Rodado Algodão Estampado',
                    'Pijama Infantil Unisex Soft Inverno com Punho',
                    'Kit 3 Body Bebê Algodão Suedine Manga Curta',
                    'Casaco Infantil com Capuz Moletom Forrado Pelúcia'
                ],
                'price_min' => 24.90,
                'price_max' => 149.00,
                'category' => 'Moda Infantil',
                'image_prefix' => 'kids_wear'
            ],
            'iphone' => [
                'titles' => [
                    'iPhone 14 Apple 128GB Estelar Tela 6.1" Câmera 12MP',
                    'iPhone 13 Apple 128GB Meia-noite Tela 6.1" Câmera 12MP',
                    'iPhone 15 Pro Max 256GB Titânio Natural Original',
                    'iPhone 11 Apple 64GB Preto Tela 6.1" Câmera Dupla Reforçada',
                    'iPhone 12 Apple 128GB Azul Tela 6.1" Super Retina XDR'
                ],
                'price_min' => 1999.00,
                'price_max' => 8499.00,
                'category' => 'Eletrônicos & Áudio',
                'image_prefix' => 'smartphone'
            ],
            'celular' => [
                'titles' => [
                    'Smartphone Samsung Galaxy S23 Ultra 5G 256GB',
                    'Celular Motorola Moto G54 5G 128GB Grafite',
                    'Smartphone Xiaomi Redmi Note 12 128GB Original',
                    'Celular Samsung Galaxy A54 5G 128GB Tela 6.4"',
                    'Smartphone Realme C55 256GB NFC Tela 6.72"'
                ],
                'price_min' => 799.00,
                'price_max' => 5999.00,
                'category' => 'Eletrônicos & Áudio',
                'image_prefix' => 'smartphone'
            ]
        ];

        // Match query to templates, fallback if none matches
        $activeTemplate = null;
        foreach ($templates as $key => $tpl) {
            if (str_contains($query, $key)) {
                $activeTemplate = $tpl;
                break;
            }
        }

        // Custom synonym mappings for clothing and toys variations
        if ($activeTemplate === null) {
            if (str_contains($query, 'masculin') || str_contains($query, 'homem') || str_contains($query, 'macho') || str_contains($query, 'menino')) {
                $activeTemplate = $templates['roupas masculinas'];
            } elseif (str_contains($query, 'feminin') || str_contains($query, 'mulher') || str_contains($query, 'fêmea') || str_contains($query, 'menina')) {
                $activeTemplate = $templates['roupas femininas'];
            } elseif (str_contains($query, 'criança') || str_contains($query, 'infantil') || str_contains($query, 'bebe') || str_contains($query, 'kids') || str_contains($query, 'baby')) {
                $activeTemplate = $templates['roupas de crianças'];
            }
        }

        // Standard fallback templates for generic keywords
        if ($activeTemplate === null) {
            $cleanQuery = ucwords($query ?: 'Produto');
            $activeTemplate = [
                'titles' => [
                    $cleanQuery . ' Premium com Garantia Nacional',
                    'Kit ' . $cleanQuery . ' Completo Importado Original',
                    $cleanQuery . ' Inteligente de Alta Performance',
                    'Mini ' . $cleanQuery . ' Portátil Recarregável USB',
                    $cleanQuery . ' Ultra Pro - Edição de Lançamento',
                    'Acessório Compatível com ' . $cleanQuery . ' Multifuncional'
                ],
                'price_min' => 29.90,
                'price_max' => 599.90,
                'category' => 'Geral & Variedades',
                'image_prefix' => 'general'
            ];
        }

        $stores = [
            'Lojas Premium BR', 'E-Shop Global', 'Express Outlet', 'Mundo Smart', 'Mega Utilidades',
            'Beleza Viva', 'Moda Center SP', 'Sports Brasil', 'Tech Prime', 'Casa & Conforto'
        ];

        $shippings = ['Frete Grátis', 'Frete Grátis com cupom', 'Envio Imediato Full', 'Frete Fixo R$ 9,90', 'Entrega Rápida 2 dias'];
        $competitionLevels = ['low', 'medium', 'high'];

        // Use seed based on query and marketplace to keep results deterministic per search, but randomized
        $seed = crc32($query . $this->code);
        mt_srand($seed);

        $isMixed = (empty($query) || $query === 'geral' || $query === 'todas');
        $templateKeys = array_keys($templates);

        for ($i = 0; $i < $limit; $i++) {
            $currentTpl = $activeTemplate;
            if ($isMixed) {
                $key = $templateKeys[$i % count($templateKeys)];
                $currentTpl = $templates[$key];
            }

            $titleIndex = $i % count($currentTpl['titles']);
            $baseTitle = $currentTpl['titles'][$titleIndex];
            
            // Add variety to titles
            $variants = ['', ' Premium', ' Ultra', ' Importado', ' Bivolt', ' Original', ' - Promoção'];
            $variant = $variants[mt_rand(0, count($variants) - 1)];
            $title = $baseTitle . $variant;

            // Numeric specifications
            $price = mt_rand((int)($currentTpl['price_min'] * 100), (int)($currentTpl['price_max'] * 100)) / 100;
            $hasOriginal = mt_rand(0, 100) > 30;
            $originalPrice = $hasOriginal ? round($price * mt_rand(115, 160) / 100, 2) : null;
            
            $salesCount = mt_rand(50, 4500);
            $reviewsCount = (int)($salesCount * (mt_rand(5, 25) / 100));
            $rating = mt_rand(380, 500) / 100; // 3.8 to 5.0
            
            $storeName = $stores[mt_rand(0, count($stores) - 1)];
            $shippingType = $shippings[mt_rand(0, count($shippings) - 1)];
            
            $externalId = $this->code . '_' . (10000000 + mt_rand(0, 89999999));
            
            // Build direct placeholder images using a deterministic color palette/icon
            $bgColor = substr(md5($title), 0, 6);
            $imageUrl = "https://placehold.co/300x300/{$bgColor}/FFFFFF?text=" . urlencode(explode(' ', $title)[0] . ' ' . (explode(' ', $title)[1] ?? ''));

            // Calculate metrics for Trend Score calculation
            $demand = min(100, (int)($salesCount / 40));
            $growth = mt_rand(5, 95);
            $competitionScore = mt_rand(20, 85);
            $margin = mt_rand(30, 70); // profit margin
            $seasonality = mt_rand(5, 50);

            // Compute score
            $trendScoreObj = new TrendScorer();
            $trendScore = $trendScoreObj->calculate($demand, $growth, 100 - $competitionScore, $margin, $seasonality);
            
            $competitionLevel = 'medium';
            if ($competitionScore > 65) {
                $competitionLevel = 'high';
            } elseif ($competitionScore < 35) {
                $competitionLevel = 'low';
            }

            $products[] = [
                'marketplace' => $this->code,
                'external_id' => $externalId,
                'title' => $title,
                'url' => $this->getProductUrl($this->code, $title, $externalId),
                'image_url' => $imageUrl,
                'price' => $price,
                'original_price' => $originalPrice,
                'sales_count_est' => $salesCount,
                'reviews_count' => $reviewsCount,
                'rating' => $rating,
                'store_name' => $storeName,
                'shipping_type' => $shippingType,
                'category' => $currentTpl['category'],
                'trend_score' => $trendScore,
                'competition_level' => $competitionLevel
            ];
        }

        // Sort by sales descending
        usort($products, fn($a, $b) => $b['sales_count_est'] <=> $a['sales_count_est']);

        return $products;
    }

    /**
     * Generate a real search link on the target marketplace
     */
    protected function getProductUrl(string $marketplace, string $title, string $externalId): string {
        $encodedTitle = urlencode($title);
        switch ($marketplace) {
            case 'shopee':
                return "https://shopee.com.br/search?keyword={$encodedTitle}";
            case 'mercadolivre':
                return "https://lista.mercadolivre.com.br/{$encodedTitle}#D[A:{$encodedTitle}]";
            case 'amazon':
                return "https://www.amazon.com.br/s?k={$encodedTitle}";
            case 'aliexpress':
                return "https://pt.aliexpress.com/wholesale?SearchText={$encodedTitle}";
            case 'magalu':
                return "https://www.magazineluiza.com.br/busca/{$encodedTitle}/";
            case 'casasbahia':
                return "https://www.casasbahia.com.br/c/?filtro=d17676&busca={$encodedTitle}";
            case 'temu':
                return "https://www.temu.com/br/search.html?search_key={$encodedTitle}";
            case 'shein':
                return "https://br.shein.com/pdsearch/{$encodedTitle}/";
            case 'tiktok':
            case 'tiktokshop':
                return "https://www.tiktok.com/search?q={$encodedTitle}";
            default:
                return "https://www.google.com/search?q={$encodedTitle}";
        }
    }
}
