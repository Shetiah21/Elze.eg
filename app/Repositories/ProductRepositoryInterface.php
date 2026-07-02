<?php

namespace App\Repositories;

interface ProductRepositoryInterface
{
    /**
     * Get all products with optional filters:
     *   - category (slug string)
     *   - size (S / M / L / XL)
     *   - color (string)
     *   - search (string - matches product name)
     *   - limit / offset for pagination
     */
    public function getAll(array $filters = []): array;

    /** Fetch a single product by its URL slug */
    public function getBySlug(string $slug): ?array;

    /** Fetch all variants for a product */
    public function getVariants(int $productId): array;

    /** Fetch primary image for a product */
    public function getPrimaryImage(int $productId): ?string;

    /** Fetch all images for a product */
    public function getImages(int $productId): array;

    /** Fetch related products (same category, different product) */
    public function getRelated(int $productId, int $categoryId, int $limit = 4): array;

    /** Fetch all categories */
    public function getCategories(): array;

    /** Get available colors for a product (distinct) */
    public function getAvailableColors(int $productId): array;

    /** Get available sizes for a product (distinct) */
    public function getAvailableSizes(int $productId): array;
}
