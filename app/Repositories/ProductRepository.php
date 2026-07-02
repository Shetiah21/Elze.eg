<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ProductRepository implements ProductRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch products with flexible filters: category, size, color, search, limit, offset
     */
    public function getAll(array $filters = []): array
    {
        $params = [];
        $joins  = '';
        $where  = ['p.is_active = 1'];

        // Category filter (by slug)
        if (!empty($filters['category'])) {
            $where[]  = 'c.slug = :cat_slug';
            $params[':cat_slug'] = $filters['category'];
        }

        // Size filter
        if (!empty($filters['size'])) {
            $joins .= ' INNER JOIN product_variants pv_size
                        ON pv_size.product_id = p.id
                        AND pv_size.size = :size
                        AND pv_size.stock > 0';
            $params[':size'] = $filters['size'];
        }

        // Color filter
        if (!empty($filters['color'])) {
            $joins .= ' INNER JOIN product_variants pv_color
                        ON pv_color.product_id = p.id
                        AND pv_color.color LIKE :color
                        AND pv_color.stock > 0';
            $params[':color'] = '%' . $filters['color'] . '%';
        }

        // Search by product name
        if (!empty($filters['search'])) {
            $where[] = 'p.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $limitClause = '';

        if (!empty($filters['limit'])) {
            $limitClause = ' LIMIT ' . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $limitClause .= ' OFFSET ' . (int)$filters['offset'];
            }
        }

        $sql = "SELECT DISTINCT
                    p.id, p.name, p.slug, p.base_price, p.description,
                    c.name AS category_name, c.slug AS category_slug,
                    (SELECT pi.image_path
                     FROM product_images pi
                     WHERE pi.product_id = p.id AND pi.is_primary = 1
                     LIMIT 1) AS primary_image
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                {$joins}
                {$whereClause}
                ORDER BY p.created_at DESC
                {$limitClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Fetch a single product by slug, including category name
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.slug = :slug AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Get all variants for a product grouped by color, then size
     */
    public function getVariants(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_variants
            WHERE product_id = :id
            ORDER BY color ASC, FIELD(size, 'S', 'M', 'L', 'XL', 'XXL')
        ");
        $stmt->execute([':id' => $productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get only the primary image path for a product
     */
    public function getPrimaryImage(int $productId): ?string
    {
        $stmt = $this->db->prepare("
            SELECT image_path FROM product_images
            WHERE product_id = :id AND is_primary = 1
            LIMIT 1
        ");
        $stmt->execute([':id' => $productId]);
        $row = $stmt->fetch();

        return $row ? $row['image_path'] : null;
    }

    /**
     * Get all images for a product
     */
    public function getImages(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_images
            WHERE product_id = :id
            ORDER BY is_primary DESC
        ");
        $stmt->execute([':id' => $productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get related products from the same category
     */
    public function getRelated(int $productId, int $categoryId, int $limit = 4): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.slug, p.base_price,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM products p
            WHERE p.category_id = :cat_id
              AND p.id != :pid
              AND p.is_active = 1
            ORDER BY RAND()
            LIMIT :lim
        ");
        $stmt->bindValue(':cat_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':pid',    $productId,  PDO::PARAM_INT);
        $stmt->bindValue(':lim',    $limit,       PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Fetch all active categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get distinct colors available in stock for a product
     */
    public function getAvailableColors(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT color FROM product_variants
            WHERE product_id = :id AND stock > 0
            ORDER BY color ASC
        ");
        $stmt->execute([':id' => $productId]);
        return array_column($stmt->fetchAll(), 'color');
    }

    /**
     * Get distinct sizes available in stock for a product
     */
    public function getAvailableSizes(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT size FROM product_variants
            WHERE product_id = :id AND stock > 0
            ORDER BY FIELD(size, 'S', 'M', 'L', 'XL', 'XXL')
        ");
        $stmt->execute([':id' => $productId]);
        return array_column($stmt->fetchAll(), 'size');
    }
}
