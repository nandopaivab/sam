<?php
declare(strict_types=1);

namespace TrendHunter\Analysis;

use TrendHunter\Cache;
use Exception;

class TrendsClient {
    private const CACHE_KEY = 'google_trends_br';
    private const CACHE_TTL = 43200; // 12 hours

    /**
     * Fetch trending keywords in Brazil from Google Trends RSS
     * 
     * @return array<string>
     */
    public static function getTrendingKeywords(): array {
        // Try getting from cache first
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        $trends = [];
        $rssUrl = 'https://trends.google.com/trends/trendingsearches/daily/rss?geo=BR';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $rssUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $xmlString = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($xmlString)) {
                // Parse XML feed safely
                $xml = simplexml_load_string($xmlString);

                if ($xml !== false && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = (string)$item->title;
                        if (!empty($title)) {
                            $trends[] = trim($title);
                        }
                    }
                }
            }
        } catch (Exception) {
            // Silence exceptions to use fallback
        }

        // Fallback trending terms if the RSS feed is blocked/rate-limited by Google
        if (empty($trends)) {
            $trends = [
                'Garrafa Térmica Stanley',
                'Fone Bluetooth Redmi Airdots',
                'Air Fryer Mondial 4L',
                'Smartwatch Watch 9 Ultra',
                'Mini Projetor Portátil',
                'Luminária de Mesa Inteligente',
                'Maquiagem Lip Tint',
                'Organizador de Acrílico',
                'Mochila Impermeável USB',
                'Kit Camisetas Masculinas Premium',
                'Ring Light de Mesa USB',
                'Umidificador de Ar Ultrassônico',
                'Escova Secadora Modeladora',
                'Suporte Articulado Monitor',
                'Teclado Mecânico RGB'
            ];
        }

        // Save to cache
        Cache::set(self::CACHE_KEY, $trends, self::CACHE_TTL);

        return $trends;
    }
}
