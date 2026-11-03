<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class ShopeeAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'Shopee Brasil';
        $this->code = 'shopee';
        $this->baseUrl = 'https://shopee.com.br';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        // Implement official API or specific public scraper here when tokens are provided.
        // E.g., if (isset($this->apiClient)) { return $this->searchOfficial($query); }
        
        // Default: use the simulated engine
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
