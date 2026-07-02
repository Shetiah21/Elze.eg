<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ProductRepository;

class ProductController extends Controller
{
    private ProductRepository $productRepo;

    public function __construct()
    {
        $this->productRepo = new ProductRepository();
    }

    /**
     * Product listing page — with optional filters
     */
    public function index(): void
    {
        $query = $this->getQueryParams();

        $filters = [
            'category' => $query['category'] ?? null,
            'size'     => $query['size']     ?? null,
            'color'    => $query['color']    ?? null,
            'search'   => $query['search']   ?? null,
        ];

        $products   = $this->productRepo->getAll($filters);
        $categories = $this->productRepo->getCategories();

        // Collect all distinct sizes and colors across the full catalog
        // for the filter sidebar (unfiltered)
        $allSizes  = ['S', 'M', 'L', 'XL'];
        $allColors = [
            'White', 'Black', 'Navy', 'Burgundy', 'Blue',
            'Olive', 'Grey', 'Brown', 'Pink', 'Yellow',
            'White ra2aba black', 'Black ra2aba white',
            'Navy ra2aba white', 'Burgundy ra2aba white',
        ];

        $activeCategory = $filters['category'] ?? '';
        $activeSize     = $filters['size']     ?? '';
        $activeColor    = $filters['color']    ?? '';
        $searchQuery    = $filters['search']   ?? '';

        $this->render('products/list', [
            'title'          => 'Shop All | Elze.eg',
            'products'       => $products,
            'categories'     => $categories,
            'allSizes'       => $allSizes,
            'allColors'      => $allColors,
            'activeCategory' => $activeCategory,
            'activeSize'     => $activeSize,
            'activeColor'    => $activeColor,
            'searchQuery'    => $searchQuery,
        ]);
    }

    /**
     * Product detail page — by slug
     */
    public function detail(string $slug): void
    {
        $product = $this->productRepo->getBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Product Not Found | Elze.eg']);
            return;
        }

        $variants       = $this->productRepo->getVariants($product['id']);
        $images         = $this->productRepo->getImages($product['id']);
        $availableColors = $this->productRepo->getAvailableColors($product['id']);
        $availableSizes  = $this->productRepo->getAvailableSizes($product['id']);
        $related        = $this->productRepo->getRelated($product['id'], $product['category_id'], 4);

        // Decode size chart JSON
        $sizeChart = null;
        if (!empty($product['size_chart_details'])) {
            $sizeChart = json_decode($product['size_chart_details'], true);
        }

        // Build a lookup: variant[color][size] => stock (for JS variant selection)
        $stockMap = [];
        foreach ($variants as $v) {
            $stockMap[$v['color']][$v['size']] = (int)$v['stock'];
        }

        $this->render('products/detail', [
            'title'          => $product['name'] . ' | Elze.eg',
            'product'        => $product,
            'variants'       => $variants,
            'images'         => $images,
            'availableColors' => $availableColors,
            'availableSizes'  => $availableSizes,
            'related'        => $related,
            'sizeChart'      => $sizeChart,
            'stockMap'       => $stockMap,
        ]);
    }
}
