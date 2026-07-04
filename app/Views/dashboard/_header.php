<?php
$firstName = explode(' ', trim($user['name'] ?? 'Guest'))[0];
$today = date('l, F j, Y');
$initials = strtoupper(substr($firstName, 0, 1) . substr(explode(' ', trim($user['name'] ?? 'G'))[1] ?? $firstName, 0, 1));
?>
<header class="dash-header">
    <div class="dash-header-left">
        <button type="button" class="dash-menu-toggle" id="dash-menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="dash-sidebar">
            <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
        <div class="dash-welcome">
            <h1 class="dash-welcome-title">Welcome back, <?= htmlspecialchars($firstName) ?></h1>
            <p class="dash-welcome-date"><?= $today ?></p>
        </div>
    </div>
    <div class="dash-header-right">
        <span class="dash-status-pill">
            <span class="dash-status-dot"></span>
            Active Member
        </span>
        <div class="dash-avatar" aria-label="Profile avatar" title="<?= htmlspecialchars($user['name'] ?? '') ?>">
            <?= htmlspecialchars($initials) ?>
        </div>
    </div>
</header>
