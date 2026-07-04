<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
?>

<div class="dashboard-wrapper">
    <h1 class="dashboard-title">My Account</h1>
    
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <nav class="sidebar-nav">
                <a href="<?= $base ?>/dashboard" class="sidebar-link <?= ($active_tab === 'profile') ? 'active' : '' ?>">Profile Management</a>
                <a href="<?= $base ?>/dashboard/orders" class="sidebar-link <?= ($active_tab === 'orders') ? 'active' : '' ?>">Order History</a>
                <a href="<?= $base ?>/dashboard/addresses" class="sidebar-link <?= ($active_tab === 'addresses') ? 'active' : '' ?>">Saved Addresses</a>
                <a href="<?= $base ?>/logout" class="sidebar-link" style="color: var(--color-danger); border-top: 1px solid var(--color-grey-border); margin-top: 16px; padding-top: 16px;">Logout</a>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="dashboard-content">
            <h3 class="dashboard-section-title">Order History</h3>
            
            <?php if (empty($orders)): ?>
                <div style="background-color: var(--color-white); padding: 48px; border-radius: var(--border-radius-md); border: 1px solid var(--color-grey-border); text-align: center;">
                    <div style="color: var(--color-charcoal-light); margin-bottom: 16px;">
                        <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" style="margin: 0 auto;">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h4 style="font-family: var(--font-headers); font-size: 18px; margin-bottom: 8px; color: var(--color-brand-blue);">No Orders Found</h4>
                    <p style="font-size: 13px; color: var(--color-charcoal-light); margin-bottom: 20px;">You haven't placed any orders yet.</p>
                    <a href="<?= $base ?>/products" class="btn btn-primary" style="background-color: var(--color-brand-blue); color: var(--color-white); padding: 8px 24px; display: inline-block;">Shop Catalog</a>
                </div>
            <?php else: ?>
                <div style="background-color: var(--color-white); border: 1px solid var(--color-grey-border); border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                        <thead>
                            <tr style="background-color: var(--color-alabaster); border-bottom: 1px solid var(--color-grey-border);">
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700;">Order Number</th>
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700;">Date</th>
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700;">Method</th>
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700;">Grand Total</th>
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700;">Status</th>
                                <th style="padding: 16px; font-family: var(--font-headers); color: var(--color-brand-blue); font-weight: 700; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom: 1px solid var(--color-grey-border); transition: var(--transition-smooth);" onmouseover="this.style.backgroundColor='var(--color-alabaster)'" onmouseout="this.style.backgroundColor='transparent'">
                                    <td style="padding: 16px; font-weight: 700; color: var(--color-brand-blue);">
                                        <a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" style="color: var(--color-brand-blue); text-decoration: underline;">
                                            <?= htmlspecialchars($order['order_number']) ?>
                                        </a>
                                    </td>
                                    <td style="padding: 16px; color: var(--color-charcoal-light);">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td style="padding: 16px; text-transform: uppercase; font-size: 12px; font-weight: 600; color: var(--color-charcoal-light);">
                                        <?= htmlspecialchars($order['payment_method']) ?>
                                    </td>
                                    <td style="padding: 16px; font-weight: 600;">
                                        <?= number_format($order['total_amount'], 2) ?> EGP
                                    </td>
                                    <td style="padding: 16px;">
                                        <?php
                                        $status = strtolower($order['status']);
                                        $bg = '#E5E5EA'; $fg = '#48484A';
                                        if ($status === 'pending') { $bg = '#FFF2CC'; $fg = '#B2A100'; }
                                        elseif ($status === 'processing') { $bg = '#E1F5FE'; $fg = '#0288D1'; }
                                        elseif ($status === 'shipped') { $bg = '#E8F5E9'; $fg = '#2E7D32'; }
                                        elseif ($status === 'delivered') { $bg = 'var(--color-success-bg)'; $fg = 'var(--color-success)'; }
                                        elseif ($status === 'cancelled') { $bg = 'var(--color-danger-bg)'; $fg = 'var(--color-danger)'; }
                                        ?>
                                        <span style="background-color: <?= $bg ?>; color: <?= $fg ?>; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase;">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px; text-align: right;">
                                        <a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--color-brand-blue); color: var(--color-white); border: none; border-radius: 4px; display: inline-block;">
                                            Track Order
                                        </a>
                                        <a href="<?= $base ?>/orders/receipt/<?= $order['id'] ?>" target="_blank" class="btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--color-alabaster); color: var(--color-brand-blue); border: 1px solid var(--color-grey-border); border-radius: 4px; display: inline-block; margin-left: 6px;">
                                            Receipt 📄
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
