<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CartService;
use Exception;

class CartController extends Controller
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    /**
     * View Cart Page (GET /cart)
     */
    public function view(): void
    {
        $cartItems = $this->cartService->getCart();

        // Calculate Subtotals
        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += $item['subtotal'];
        }

        // Egyptian VAT Tax Estimation (14%)
        $tax = $subtotal * 0.14;
        $total = $subtotal; // Usually base price already includes VAT or is subtotal. Let's show: Subtotal + Tax = Total or Tax is included. Let's display Subtotal, Estimated Tax (14% VAT), and a grand Total.

        $this->render('cart/view', [
            'title' => 'Shopping Cart | Elze.eg',
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal // Let's keep total = subtotal (with tax listed as included) or total = subtotal + tax. Let's assume prices are tax-inclusive by default in Egypt (which is legally required), so we show VAT as "Estimated 14% VAT (Included): ... EGP". This is standard and premium.
        ]);
    }

    /**
     * Add Item to Cart (POST /cart/add) - JSON response
     */
    public function add(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        $variantId = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        if ($variantId <= 0 || $quantity <= 0) {
            $this->json(['error' => 'Invalid product variant or quantity.'], 400);
        }

        try {
            $this->cartService->addToCart($variantId, $quantity);
            $newCount = $this->cartService->getCartCount();
            $this->json([
                'success' => true,
                'message' => 'Product variant successfully added to your cart.',
                'cart_count' => $newCount
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update Variant Quantity in Cart (POST /cart/update) - JSON response
     */
    public function update(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        $variantId = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;

        if ($variantId <= 0) {
            $this->json(['error' => 'Invalid product variant.'], 400);
        }

        try {
            $this->cartService->updateQuantity($variantId, $quantity);

            // Re-calculate cart status
            $cartItems = $this->cartService->getCart();
            $subtotal = 0.0;
            $itemSubtotal = 0.0;
            $newQty = 0;

            foreach ($cartItems as $item) {
                $subtotal += $item['subtotal'];
                if ($item['variant_id'] === $variantId) {
                    $itemSubtotal = $item['subtotal'];
                    $newQty = $item['quantity'];
                }
            }

            $tax = $subtotal * 0.14;
            $newCount = $this->cartService->getCartCount();

            $this->json([
                'success' => true,
                'message' => 'Quantity updated successfully.',
                'cart_count' => $newCount,
                'item_quantity' => $newQty,
                'item_subtotal' => number_format($itemSubtotal, 2) . ' EGP',
                'subtotal' => number_format($subtotal, 2) . ' EGP',
                'tax' => number_format($tax, 2) . ' EGP',
                'total' => number_format($subtotal, 2) . ' EGP',
                'is_empty' => empty($cartItems)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove Variant from Cart (POST /cart/remove) - JSON response
     */
    public function remove(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        $variantId = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;

        if ($variantId <= 0) {
            $this->json(['error' => 'Invalid product variant.'], 400);
        }

        try {
            $this->cartService->removeFromCart($variantId);

            // Re-calculate cart status
            $cartItems = $this->cartService->getCart();
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['subtotal'];
            }

            $tax = $subtotal * 0.14;
            $newCount = $this->cartService->getCartCount();

            $this->json([
                'success' => true,
                'message' => 'Product variant removed from cart.',
                'cart_count' => $newCount,
                'subtotal' => number_format($subtotal, 2) . ' EGP',
                'tax' => number_format($tax, 2) . ' EGP',
                'total' => number_format($subtotal, 2) . ' EGP',
                'is_empty' => empty($cartItems)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
