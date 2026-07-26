<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class MagaluAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'Magalu';
        $this->code = 'magalu';
        $this->baseUrl = 'https://www.magazineluiza.com.br';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
