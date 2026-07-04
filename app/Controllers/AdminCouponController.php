<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Coupon;
use PDO;

class AdminCouponController extends AdminController
{
    public function index(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->query("SELECT * FROM coupons ORDER BY created_at DESC");

        $this->renderAdmin('admin/coupons/index', [
            'title' => 'Coupons | Admin',
            'active_section' => 'coupons',
            'coupons' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();
            $code = strtoupper(trim($data['code'] ?? ''));

            if ($code === '') {
                $this->session->setFlash('error', 'Coupon code is required.');
                $this->redirect('/admin/coupons/create');
            }

            $existing = $this->db->prepare("SELECT id FROM coupons WHERE code = :code");
            $existing->execute(['code' => $code]);
            if ($existing->fetch()) {
                $this->session->setFlash('error', 'Coupon code already exists.');
                $this->redirect('/admin/coupons/create');
            }

            $coupon = new Coupon();
            $coupon->code = $code;
            $coupon->discount_type = in_array($data['discount_type'] ?? '', ['fixed', 'percent'], true)
                ? $data['discount_type'] : 'fixed';
            $coupon->discount_value = (float) ($data['discount_value'] ?? 0);
            $coupon->min_order_amount = (float) ($data['min_order_amount'] ?? 0);
            $coupon->starts_at = !empty($data['starts_at']) ? $data['starts_at'] : null;
            $coupon->expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
            $coupon->max_uses = max(1, (int) ($data['max_uses'] ?? 100));
            $coupon->is_active = isset($data['is_active']) ? 1 : 0;
            $coupon->save();

            $this->session->setFlash('success', 'Coupon created successfully.');
            $this->redirect('/admin/coupons');
        }

        $this->renderAdmin('admin/coupons/form', [
            'title' => 'Create Coupon | Admin',
            'active_section' => 'coupons',
            'coupon' => null,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $coupon = Coupon::find((int) $id);
        if (!$coupon) {
            $this->session->setFlash('error', 'Coupon not found.');
            $this->redirect('/admin/coupons');
        }

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();

            $coupon->discount_type = in_array($data['discount_type'] ?? '', ['fixed', 'percent'], true)
                ? $data['discount_type'] : 'fixed';
            $coupon->discount_value = (float) ($data['discount_value'] ?? 0);
            $coupon->min_order_amount = (float) ($data['min_order_amount'] ?? 0);
            $coupon->starts_at = !empty($data['starts_at']) ? $data['starts_at'] : null;
            $coupon->expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
            $coupon->max_uses = max(1, (int) ($data['max_uses'] ?? 100));
            $coupon->is_active = isset($data['is_active']) ? 1 : 0;
            $coupon->save();

            $this->session->setFlash('success', 'Coupon updated successfully.');
            $this->redirect('/admin/coupons');
        }

        $this->renderAdmin('admin/coupons/form', [
            'title' => 'Edit Coupon | Admin',
            'active_section' => 'coupons',
            'coupon' => $coupon,
        ]);
    }

    public function toggle(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $coupon = Coupon::find((int) $id);
            if ($coupon) {
                $coupon->is_active = $coupon->is_active ? 0 : 1;
                $coupon->save();
                $this->session->setFlash('success', 'Coupon status updated.');
            }
        }
        $this->redirect('/admin/coupons');
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $coupon = Coupon::find((int) $id);
            if ($coupon) {
                $coupon->delete();
                $this->session->setFlash('success', 'Coupon deleted.');
            }
        }
        $this->redirect('/admin/coupons');
    }
}
