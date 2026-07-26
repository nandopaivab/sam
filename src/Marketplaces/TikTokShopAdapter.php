<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class TikTokShopAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'TikTok Shop';
        $this->code = 'tiktok';
        $this->baseUrl = 'https://shop.tiktok.com';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
