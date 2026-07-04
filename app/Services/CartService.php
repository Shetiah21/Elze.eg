<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use PDO;
use Exception;

class CartService
{
    private PDO $db;
    private Session $session;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->session = Session::getInstance();
    }

    /**
     * Get user session ID if logged in, or null
     */
    private function getUserId(): ?int
    {
        $user = $this->session->get('user');
        return $user ? (int)$user['id'] : null;
    }

    /**
     * Get itemized list of cart items with full product/variant info, capped by stock
     */
    public function getCart(): array
    {
        $userId = $this->getUserId();
        $items = [];

        if ($userId) {
            // Logged-in user: read from database
            $stmt = $this->db->prepare("SELECT variant_id, quantity FROM cart_items WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            while ($row = $stmt->fetch()) {
                $items[(int)$row['variant_id']] = (int)$row['quantity'];
            }
        } else {
            // Guest: read from session
            $items = $this->session->get('cart', []);
        }

        if (empty($items)) {
            return [];
        }

        // Fetch details for the variant IDs
        $variantIds = array_keys($items);
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
        
        $sql = "SELECT 
                    pv.id AS variant_id,
                    pv.size,
                    pv.color,
                    pv.stock,
                    pv.price_modifier,
                    p.id AS product_id,
                    p.name AS product_name,
                    p.slug AS product_slug,
                    p.base_price,
                    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image
                FROM product_variants pv
                JOIN products p ON p.id = pv.product_id
                WHERE pv.id IN ($placeholders) AND p.is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($variantIds);
        $details = $stmt->fetchAll();

        $cartList = [];
        $hasCapping = false;

        foreach ($details as $row) {
            $variantId = (int)$row['variant_id'];
            $reqQty = $items[$variantId];
            $stock = (int)$row['stock'];

            // Cap quantity at stock level if stock is lower
            if ($reqQty > $stock) {
                $reqQty = $stock;
                $hasCapping = true;
                
                // Persist the capped quantity immediately
                $this->updateQtyDirectly($variantId, $stock);
            }

            if ($reqQty <= 0) {
                // If stock is 0, remove item from cart
                $this->removeFromCart($variantId);
                continue;
            }

            $price = (float)$row['base_price'] + (float)$row['price_modifier'];
            $subtotal = $price * $reqQty;

            $cartList[] = [
                'variant_id' => $variantId,
                'product_id' => (int)$row['product_id'],
                'product_name' => $row['product_name'],
                'product_slug' => $row['product_slug'],
                'size' => $row['size'],
                'color' => $row['color'],
                'stock' => $stock,
                'price' => $price,
                'quantity' => $reqQty,
                'subtotal' => $subtotal,
                'primary_image' => $row['primary_image'] ?: null
            ];
        }

        return $cartList;
    }

    /**
     * Get the total count of items in the cart (sum of quantities)
     */
    public function getCartCount(): int
    {
        $userId = $this->getUserId();
        if ($userId) {
            $stmt = $this->db->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $row = $stmt->fetch();
            return (int)($row['count'] ?? 0);
        } else {
            $cart = $this->session->get('cart', []);
            return array_sum($cart);
        }
    }

    /**
     * Add a variant to the cart with stock validation
     */
    public function addToCart(int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be at least 1.");
        }

        // Validate variant exists and check stock
        $stmt = $this->db->prepare("SELECT stock, size, color FROM product_variants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $variantId]);
        $variant = $stmt->fetch();

        if (!$variant) {
            throw new Exception("Selected product variant does not exist.");
        }

        $stock = (int)$variant['stock'];
        if ($stock <= 0) {
            throw new Exception("The selected variant ({$variant['size']} - {$variant['color']}) is currently out of stock.");
        }

        $userId = $this->getUserId();
        if ($userId) {
            // Check if variant already in DB cart
            $stmt = $this->db->prepare("SELECT quantity FROM cart_items WHERE user_id = :user_id AND variant_id = :variant_id LIMIT 1");
            $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId]);
            $existing = $stmt->fetch();

            $newQty = $quantity;
            if ($existing) {
                $newQty += (int)$existing['quantity'];
            }

            if ($newQty > $stock) {
                throw new Exception("Cannot add item. Requested quantity exceeds available stock ({$stock} left).");
            }

            if ($existing) {
                $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND variant_id = :variant_id");
                $stmt->execute(['quantity' => $newQty, 'user_id' => $userId, 'variant_id' => $variantId]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO cart_items (user_id, variant_id, quantity) VALUES (:user_id, :variant_id, :quantity)");
                $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId, 'quantity' => $newQty]);
            }
        } else {
            // Guest: Session
            $cart = $this->session->get('cart', []);
            $existingQty = $cart[$variantId] ?? 0;
            $newQty = $existingQty + $quantity;

            if ($newQty > $stock) {
                throw new Exception("Cannot add item. Requested quantity exceeds available stock ({$stock} left).");
            }

            $cart[$variantId] = $newQty;
            $this->session->set('cart', $cart);
        }
    }

    /**
     * Update quantity of a variant in the cart with stock validation
     */
    public function updateQuantity(int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($variantId);
            return;
        }

        // Validate variant exists and check stock
        $stmt = $this->db->prepare("SELECT stock, size, color FROM product_variants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $variantId]);
        $variant = $stmt->fetch();

        if (!$variant) {
            throw new Exception("Product variant does not exist.");
        }

        $stock = (int)$variant['stock'];
        if ($quantity > $stock) {
            throw new Exception("Cannot update. Requested quantity exceeds available stock ({$stock} left).");
        }

        $userId = $this->getUserId();
        if ($userId) {
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND variant_id = :variant_id");
            $stmt->execute(['quantity' => $quantity, 'user_id' => $userId, 'variant_id' => $variantId]);
        } else {
            $cart = $this->session->get('cart', []);
            $cart[$variantId] = $quantity;
            $this->session->set('cart', $cart);
        }
    }

    /**
     * Remove a variant from the cart
     */
    public function removeFromCart(int $variantId): void
    {
        $userId = $this->getUserId();
        if ($userId) {
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = :user_id AND variant_id = :variant_id");
            $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId]);
        } else {
            $cart = $this->session->get('cart', []);
            if (isset($cart[$variantId])) {
                unset($cart[$variantId]);
                $this->session->set('cart', $cart);
            }
        }
    }

    /**
     * Helper to update quantity directly without throwing stock limit exceptions (used for capping during getCart)
     */
    private function updateQtyDirectly(int $variantId, int $quantity): void
    {
        $userId = $this->getUserId();
        if ($userId) {
            if ($quantity <= 0) {
                $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = :user_id AND variant_id = :variant_id");
                $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId]);
            } else {
                $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND variant_id = :variant_id");
                $stmt->execute(['quantity' => $quantity, 'user_id' => $userId, 'variant_id' => $variantId]);
            }
        } else {
            $cart = $this->session->get('cart', []);
            if ($quantity <= 0) {
                unset($cart[$variantId]);
            } else {
                $cart[$variantId] = $quantity;
            }
            $this->session->set('cart', $cart);
        }
    }

    /**
     * Merge guest session cart items into DB on login, and empty the session cart
     */
    public function mergeSessionCartIntoDb(int $userId): void
    {
        $sessionCart = $this->session->get('cart', []);
        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $variantId => $quantity) {
            try {
                // Get stock details
                $stmt = $this->db->prepare("SELECT stock FROM product_variants WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $variantId]);
                $variant = $stmt->fetch();
                if (!$variant) continue;

                $stock = (int)$variant['stock'];

                // Check existing quantity in database
                $stmt = $this->db->prepare("SELECT quantity FROM cart_items WHERE user_id = :user_id AND variant_id = :variant_id LIMIT 1");
                $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId]);
                $existing = $stmt->fetch();

                $mergedQty = $quantity;
                if ($existing) {
                    $mergedQty += (int)$existing['quantity'];
                }

                // Cap at stock limit
                if ($mergedQty > $stock) {
                    $mergedQty = $stock;
                }

                if ($mergedQty <= 0) {
                    continue;
                }

                if ($existing) {
                    $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND variant_id = :variant_id");
                    $stmt->execute(['quantity' => $mergedQty, 'user_id' => $userId, 'variant_id' => $variantId]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO cart_items (user_id, variant_id, quantity) VALUES (:user_id, :variant_id, :quantity)");
                    $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId, 'quantity' => $mergedQty]);
                }
            } catch (Exception $e) {
                // Silently skip issues during merge
            }
        }

        // Clear session cart
        $this->session->remove('cart');
    }
}
