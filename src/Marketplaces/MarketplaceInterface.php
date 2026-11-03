<?php
declare(strict_types=1);

namespace TrendHunter\Marketplaces;

interface MarketplaceInterface {
    /**
     * Get displayable name of the marketplace
     */
    public function getName(): string;

    /**
     * Get unique short code representing the marketplace
     */
    public function getCode(): string;

    /**
     * Search products by keyword or category
     * Returns an array of normalized product data arrays
     * 
     * @return array<array{
     *   external_id: string,
     *   title: string,
     *   url: string,
     *   image_url: string,
     *   price: float,
     *   original_price: ?float,
     *   sales_count_est: int,
     *   reviews_count: int,
     *   rating: float,
     *   store_name: string,
     *   shipping_type: string,
     *   category: string,
     *   trend_score: int,
     *   competition_level: string
     * }>
     */
    public function search(string $query, ?string $category = null, int $limit = 20): array;
}
