<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class AmazonAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'Amazon Brasil';
        $this->code = 'amazon';
        $this->baseUrl = 'https://www.amazon.com.br';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        // Amazon Product Advertising API requires credentials, so we fallback to simulator
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
