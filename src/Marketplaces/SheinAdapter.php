<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class SheinAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'Shein';
        $this->code = 'shein';
        $this->baseUrl = 'https://br.shein.com';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
