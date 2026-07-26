<?php
declare(strict_types=1);

namespace TrendHunter\Analysis;

use TrendHunter\Cache;
use Exception;

class AiAdvisor {
    /**
     * Generate niche analysis, SEO titles, descriptions and keywords
     */
    public static function analyzeProduct(string $title, float $price, string $marketplace, string $competition): array {
        $cacheKey = 'ai_analysis_' . md5($title . '_' . $price . '_' . $marketplace);
        $cached = Cache::get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $aiConfig = $config['ai'] ?? [];
        
        $provider = $aiConfig['provider'] ?? 'mock';
        $apiKey = $aiConfig['api_key'] ?? '';

        if ($provider === 'mock' || empty($apiKey)) {
            $analysis = self::generateLocalFallback($title, $price, $marketplace, $competition);
        } else {
            try {
                if ($provider === 'gemini') {
                    $analysis = self::callGemini($apiKey, $aiConfig['gemini']['model'], $title, $price, $marketplace, $competition);
                } elseif ($provider === 'openai') {
                    $analysis = self::callOpenAi($apiKey, $aiConfig['openai']['model'], $title, $price, $marketplace, $competition);
                } else {
                    $analysis = self::generateLocalFallback($title, $price, $marketplace, $competition);
                }
            } catch (Exception) {
                // If API call fails, gracefully fallback
                $analysis = self::generateLocalFallback($title, $price, $marketplace, $competition);
            }
        }

        // Cache the analysis for 24 hours since AI calls are expensive
        Cache::set($cacheKey, $analysis, 86400);

        return $analysis;
    }

    /**
     * Call Gemini API
     */
    private static function callGemini(string $apiKey, string $model, string $title, float $price, string $marketplace, string $competition): array {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        
        $prompt = self::buildPrompt($title, $price, $marketplace, $competition);
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                $decoded = json_decode(trim($text), true);
                if (is_array($decoded) && isset($decoded['seo_title'])) {
                    return $decoded;
                }
            }
        }

        throw new Exception("Gemini API call failed");
    }

    /**
     * Call OpenAI API
     */
    private static function callOpenAi(string $apiKey, string $model, string $title, float $price, string $marketplace, string $competition): array {
        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        
        $prompt = self::buildPrompt($title, $price, $marketplace, $competition);
        
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um especialista em e-commerce brasileiro e SEO técnico para marketplaces. Responda estritamente com JSON válido contendo chaves: niches (array de strings), keywords (array de strings), seo_title (string), seo_description (string), target_audience (string) e marketing_strategy (string).'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $data = json_decode($response, true);
            $text = $data['choices'][0]['message']['content'] ?? '';
            if (!empty($text)) {
                $decoded = json_decode(trim($text), true);
                if (is_array($decoded) && isset($decoded['seo_title'])) {
                    return $decoded;
                }
            }
        }

        throw new Exception("OpenAI API call failed");
    }

    /**
     * Construct Prompt for AI APIs
     */
    private static function buildPrompt(string $title, float $price, string $marketplace, string $competition): string {
        return "Analise o seguinte produto à venda no Brasil para identificar oportunidades de vendas e SEO:\n" .
               "Produto: {$title}\n" .
               "Preço Atual: R$ {$price}\n" .
               "Marketplace: {$marketplace}\n" .
               "Nível de Concorrência: {$competition}\n\n" .
               "Forneça uma análise estruturada no seguinte formato JSON:\n" .
               "{\n" .
               "  \"niches\": [\"3 sub-nichos específicos para explorar esse produto\"],\n" .
               "  \"keywords\": [\"5 palavras-chave de alta conversão para anúncios e buscas\"],\n" .
               "  \"seo_title\": \"Título otimizado para marketplaces com até 60 caracteres contendo palavras-chave chave\",\n" .
               "  \"seo_description\": \"Uma descrição completa e vendedora com especificações e benefícios com quebras de linha\",\n" .
               "  \"target_audience\": \"Quem compra esse produto e por quê\",\n" .
               "  \"marketing_strategy\": \"Estratégia para vencer a concorrência {$competition} no {$marketplace}\"\n" .
               "}";
    }

    /**
     * Generate context-aware fallback heuristics for localized products
     */
    private static function generateLocalFallback(string $title, float $price, string $marketplace, string $competition): array {
        // Simple NLP split to extract base nouns
        $words = explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9áéíóúâêôãõç\s]/', '', $title)));
        $keywords = array_filter($words, fn($w) => strlen($w) > 3 && !in_array($w, ['para', 'com', 'sem', 'original', 'preto', 'azul', 'rosa', 'premium', 'frete', 'gratis', 'envio']));
        $keywords = array_slice(array_unique(array_values($keywords)), 0, 4);

        $mainNoun = !empty($keywords) ? ucfirst($keywords[0]) : 'Produto';
        $secondNoun = count($keywords) > 1 ? $keywords[1] : 'Premium';

        $seoTitle = "{$mainNoun} {$secondNoun} Profissional Alta Qualidade Promoção";
        if (strlen($seoTitle) > 60) {
            $seoTitle = substr($seoTitle, 0, 57) . '...';
        }

        $priceLow = round($price * 0.85, 2);
        $priceHigh = round($price * 1.4, 2);

        return [
            'niches' => [
                "{$mainNoun} Personalizado de Alta Durabilidade",
                "Kits de Acessórios para {$mainNoun} E-commerce",
                "Linha Premium Corporativa com {$mainNoun}"
            ],
            'keywords' => [
                "{$mainNoun} em promoção",
                "comprar {$mainNoun} {$secondNoun}",
                "{$mainNoun} melhor preço {$marketplace}",
                "{$mainNoun} frete grátis",
                "melhor {$mainNoun} custo benefício"
            ],
            'seo_title' => $seoTitle,
            'seo_description' => "✨ PRODUTO PREMIUM DE ALTA QUALIDADE ✨\n\n" .
                                 "Se você busca excelência e praticidade, o {$title} é a escolha perfeita para o seu dia a dia! Produzido com materiais de alto padrão, garante durabilidade e um desempenho fantástico.\n\n" .
                                 "📋 ESPECIFICAÇÕES TÉCNICAS:\n" .
                                 "- Modelo: Pro {$secondNoun} Edition\n" .
                                 "- Tecnologia avançada de fabricação\n" .
                                 "- Compatível com as principais soluções do mercado\n" .
                                 "- Design ergonômico e moderno\n\n" .
                                 "💡 BENEFÍCIOS DO PRODUTO:\n" .
                                 "✔️ Excelente relação custo-benefício\n" .
                                 "✔️ Ideal para presentear ou para uso profissional\n" .
                                 "✔️ Garantia de satisfação de 90 dias contra defeitos de fábrica\n\n" .
                                 "Garanta já o seu com o melhor preço e envio imediato!",
            'target_audience' => "Consumidores finais de classe B/C em busca de soluções para o dia a dia, e jovens adultos ativos nas redes sociais.",
            'marketing_strategy' => "Como a concorrência é {$competition} no {$marketplace}, invista em kits combos com produtos complementares, ofereça cupons de desconto progressivos e trabalhe com frete grátis para se destacar nas buscas e obter maior relevância orgânica."
        ];
    }
}
