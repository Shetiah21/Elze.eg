<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class AdminService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getDashboardMetrics(): array
    {
        $salesStmt = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS total_sales
            FROM orders
            WHERE status != 'cancelled' AND payment_status != 'failed'
        ");
        $totalSales = (float) $salesStmt->fetchColumn();

        $completedStmt = $this->db->query("
            SELECT COUNT(*) FROM orders WHERE status = 'delivered'
        ");
        $completedOrders = (int) $completedStmt->fetchColumn();

        $usersStmt = $this->db->query("
            SELECT COUNT(*) FROM users WHERE role = 'user'
        ");
        $totalUsers = (int) $usersStmt->fetchColumn();

        $couponsStmt = $this->db->query("
            SELECT COUNT(*) FROM coupons
            WHERE is_active = 1
              AND (expires_at IS NULL OR expires_at > NOW())
              AND (starts_at IS NULL OR starts_at <= NOW())
        ");
        $activeCoupons = (int) $couponsStmt->fetchColumn();

        return [
            'total_sales' => $totalSales,
            'completed_orders' => $completedOrders,
            'total_users' => $totalUsers,
            'active_coupons' => $activeCoupons,
        ];
    }

    public function getOrderStatusBreakdown(): array
    {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) AS count
            FROM orders
            GROUP BY status
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentOrders(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name AS customer_name, u.email AS customer_email
            FROM orders o
            JOIN users u ON u.id = o.user_id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
