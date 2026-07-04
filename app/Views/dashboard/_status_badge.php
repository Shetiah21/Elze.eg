<?php
$status = strtolower($status ?? 'pending');
$classes = [
    'pending'    => 'dash-badge-pending',
    'processing' => 'dash-badge-processing',
    'shipped'    => 'dash-badge-shipped',
    'delivered'  => 'dash-badge-delivered',
    'cancelled'  => 'dash-badge-cancelled',
];
$class = $classes[$status] ?? 'dash-badge-pending';
?>
<span class="dash-badge <?= $class ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
