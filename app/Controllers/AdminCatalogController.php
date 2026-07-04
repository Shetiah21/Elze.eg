<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use PDO;

class AdminCatalogController extends AdminController
{
    // --- Categories ---

    public function categories(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");

        $this->renderAdmin('admin/categories/index', [
            'title' => 'Categories | Admin',
            'active_section' => 'categories',
            'categories' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function createCategory(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();
            $name = trim($data['name'] ?? '');

            if ($name === '') {
                $this->session->setFlash('error', 'Category name is required.');
                $this->redirect('/admin/categories/create');
            }

            $slug = $this->slugify($name);
            $existing = $this->db->prepare("SELECT id FROM categories WHERE slug = :slug");
            $existing->execute(['slug' => $slug]);
            if ($existing->fetch()) {
                $slug .= '-' . time();
            }

            $cat = new Category();
            $cat->name = $name;
            $cat->slug = $slug;
            $cat->save();

            $this->session->setFlash('success', 'Category created successfully.');
            $this->redirect('/admin/categories');
        }

        $this->renderAdmin('admin/categories/form', [
            'title' => 'Add Category | Admin',
            'active_section' => 'categories',
            'category' => null,
        ]);
    }

    public function editCategory(string $id): void
    {
        $this->requireAdmin();
        $category = Category::find((int) $id);
        if (!$category) {
            $this->session->setFlash('error', 'Category not found.');
            $this->redirect('/admin/categories');
        }

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();
            $name = trim($data['name'] ?? '');

            if ($name === '') {
                $this->session->setFlash('error', 'Category name is required.');
                $this->redirect('/admin/categories/edit/' . $id);
            }

            $category->name = $name;
            $category->slug = $this->slugify($name);
            $category->save();

            $this->session->setFlash('success', 'Category updated successfully.');
            $this->redirect('/admin/categories');
        }

        $this->renderAdmin('admin/categories/form', [
            'title' => 'Edit Category | Admin',
            'active_section' => 'categories',
            'category' => $category,
        ]);
    }

    public function deleteCategory(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $category = Category::find((int) $id);
            if ($category) {
                $check = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
                $check->execute(['id' => $id]);
                if ((int) $check->fetchColumn() > 0) {
                    $this->session->setFlash('error', 'Cannot delete category with assigned products.');
                } else {
                    $category->delete();
                    $this->session->setFlash('success', 'Category deleted.');
                }
            }
        }
        $this->redirect('/admin/categories');
    }

    // --- Products ---

    public function products(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->query("
            SELECT p.*, c.name AS category_name,
                   (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) AS variant_count,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.created_at DESC
        ");

        $this->renderAdmin('admin/products/index', [
            'title' => 'Products | Admin',
            'active_section' => 'products',
            'products' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function createProduct(): void
    {
        $this->requireAdmin();
        $categories = $this->db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();

            $name = trim($data['name'] ?? '');
            $categoryId = (int) ($data['category_id'] ?? 0);
            $description = trim($data['description'] ?? '');
            $basePrice = (float) ($data['base_price'] ?? 0);
            $imagePath = trim($data['image_path'] ?? '');

            if ($name === '' || $categoryId <= 0 || $description === '') {
                $this->session->setFlash('error', 'Name, category, and description are required.');
                $this->redirect('/admin/products/create');
            }

            $slug = $this->slugify($name);
            $existing = $this->db->prepare("SELECT id FROM products WHERE slug = :slug");
            $existing->execute(['slug' => $slug]);
            if ($existing->fetch()) {
                $slug .= '-' . time();
            }

            $product = new Product();
            $product->category_id = $categoryId;
            $product->name = $name;
            $product->slug = $slug;
            $product->description = $description;
            $product->base_price = $basePrice;
            $product->size_chart_details = trim($data['size_chart_details'] ?? '') ?: null;
            $product->is_active = isset($data['is_active']) ? 1 : 0;
            $product->save();

            if ($imagePath !== '') {
                $img = new ProductImage();
                $img->product_id = (int) $product->id;
                $img->image_path = $imagePath;
                $img->is_primary = 1;
                $img->save();
            }

            $this->session->setFlash('success', 'Product created. Add variants next.');
            $this->redirect('/admin/products/' . $product->id . '/variants');
        }

        $this->renderAdmin('admin/products/form', [
            'title' => 'Add Product | Admin',
            'active_section' => 'products',
            'product' => null,
            'categories' => $categories,
            'primary_image' => '',
        ]);
    }

    public function editProduct(string $id): void
    {
        $this->requireAdmin();
        $product = Product::find((int) $id);
        if (!$product) {
            $this->session->setFlash('error', 'Product not found.');
            $this->redirect('/admin/products');
        }

        $categories = $this->db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $imgStmt = $this->db->prepare("SELECT image_path FROM product_images WHERE product_id = :id AND is_primary = 1 LIMIT 1");
        $imgStmt->execute(['id' => $id]);
        $primaryImage = $imgStmt->fetchColumn() ?: '';

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();

            $name = trim($data['name'] ?? '');
            $categoryId = (int) ($data['category_id'] ?? 0);
            $description = trim($data['description'] ?? '');
            $imagePath = trim($data['image_path'] ?? '');

            if ($name === '' || $categoryId <= 0 || $description === '') {
                $this->session->setFlash('error', 'Name, category, and description are required.');
                $this->redirect('/admin/products/edit/' . $id);
            }

            $product->name = $name;
            $product->category_id = $categoryId;
            $product->slug = $this->slugify($name);
            $product->description = $description;
            $product->base_price = (float) ($data['base_price'] ?? 0);
            $product->size_chart_details = trim($data['size_chart_details'] ?? '') ?: null;
            $product->is_active = isset($data['is_active']) ? 1 : 0;
            $product->save();

            if ($imagePath !== '') {
                $this->db->prepare("DELETE FROM product_images WHERE product_id = :id AND is_primary = 1")
                    ->execute(['id' => $id]);
                $img = new ProductImage();
                $img->product_id = (int) $id;
                $img->image_path = $imagePath;
                $img->is_primary = 1;
                $img->save();
            }

            $this->session->setFlash('success', 'Product updated successfully.');
            $this->redirect('/admin/products');
        }

        $this->renderAdmin('admin/products/form', [
            'title' => 'Edit Product | Admin',
            'active_section' => 'products',
            'product' => $product,
            'categories' => $categories,
            'primary_image' => $primaryImage,
        ]);
    }

    public function deleteProduct(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $product = Product::find((int) $id);
            if ($product) {
                $product->delete();
                $this->session->setFlash('success', 'Product deleted.');
            }
        }
        $this->redirect('/admin/products');
    }

    // --- Variants ---

    public function variants(string $id): void
    {
        $this->requireAdmin();
        $product = Product::find((int) $id);
        if (!$product) {
            $this->session->setFlash('error', 'Product not found.');
            $this->redirect('/admin/products');
        }

        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :id ORDER BY size, color");
        $stmt->execute(['id' => $id]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderAdmin('admin/products/variants', [
            'title' => 'Variants | ' . $product->name,
            'active_section' => 'products',
            'product' => $product,
            'variants' => $variants,
        ]);
    }

    public function createVariant(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();

            $variant = new ProductVariant();
            $variant->product_id = (int) $id;
            $variant->size = trim($data['size'] ?? '');
            $variant->color = trim($data['color'] ?? '');
            $variant->stock = max(0, (int) ($data['stock'] ?? 0));
            $variant->price_modifier = (float) ($data['price_modifier'] ?? 0);

            if ($variant->size === '' || $variant->color === '') {
                $this->session->setFlash('error', 'Size and color are required.');
            } else {
                $variant->save();
                $this->session->setFlash('success', 'Variant added.');
            }
        }
        $this->redirect('/admin/products/' . $id . '/variants');
    }

    public function editVariant(string $productId, string $variantId): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $variant = ProductVariant::find((int) $variantId);
            if ($variant && (int) $variant->product_id === (int) $productId) {
                $data = $this->getPostData();
                $variant->size = trim($data['size'] ?? '');
                $variant->color = trim($data['color'] ?? '');
                $variant->stock = max(0, (int) ($data['stock'] ?? 0));
                $variant->price_modifier = (float) ($data['price_modifier'] ?? 0);
                $variant->save();
                $this->session->setFlash('success', 'Variant updated.');
            }
        }
        $this->redirect('/admin/products/' . $productId . '/variants');
    }

    public function deleteVariant(string $productId, string $variantId): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $variant = ProductVariant::find((int) $variantId);
            if ($variant && (int) $variant->product_id === (int) $productId) {
                $variant->delete();
                $this->session->setFlash('success', 'Variant deleted.');
            }
        }
        $this->redirect('/admin/products/' . $productId . '/variants');
    }
}
