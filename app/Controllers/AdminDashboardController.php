<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Services\AdminService;

class AdminDashboardController extends AdminController
{
    public function index(): void
    {
        $this->requireAdmin();
        $adminService = new AdminService();

        $this->renderAdmin('admin/dashboard', [
            'title' => 'Admin Dashboard | Elze.eg',
            'active_section' => 'dashboard',
            'metrics' => $adminService->getDashboardMetrics(),
            'order_breakdown' => $adminService->getOrderStatusBreakdown(),
            'recent_orders' => $adminService->getRecentOrders(),
        ]);
    }
}
