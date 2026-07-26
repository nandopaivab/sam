<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

class CasasBahiaAdapter extends BaseMarketplace {
    public function __construct() {
        $this->name = 'Casas Bahia';
        $this->code = 'casasbahia';
        $this->baseUrl = 'https://www.casasbahia.com.br';
    }

    public function search(string $query, ?string $category = null, int $limit = 20): array {
        return $this->generateSimulatedData($query, $category, $limit);
    }
}
