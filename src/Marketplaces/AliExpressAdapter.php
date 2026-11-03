<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class AliExpressAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'AliExpress';
        $this->code = 'aliexpress';
        $this->baseUrl = 'https://pt.aliexpress.com';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
