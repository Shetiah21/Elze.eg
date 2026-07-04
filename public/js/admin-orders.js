/**
 * Elze.eg Admin Order Management — Accept / Reject / Verify Payment with AJAX
 */
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken     = document.getElementById('admin-csrf-token')?.value;
    const basePath      = document.getElementById('admin-base-path')?.value || '';
    const modal         = document.getElementById('order-action-modal');
    const modalMessage  = document.getElementById('order-modal-message');
    const modalTitle    = document.getElementById('order-modal-title');
    const modalConfirm  = document.getElementById('order-modal-confirm');
    const modalCancel   = document.getElementById('order-modal-cancel');
    const modalClose    = document.getElementById('order-modal-close');
    const toastContainer = document.getElementById('admin-toast-container');

    let pendingAction = null;

    // ── Standard Order Accept / Reject ────────────────────────────────────
    document.querySelectorAll('.btn-accept-order').forEach(btn => {
        btn.addEventListener('click', () => openModal(
            'accept',
            btn.dataset.orderId,
            btn.dataset.orderNumber,
            `Accept order <strong>${escapeHtml(btn.dataset.orderNumber)}</strong>?<br><br>This will move the order to <strong>Processing</strong> and deduct inventory from stock.`
        ));
    });

    document.querySelectorAll('.btn-reject-order').forEach(btn => {
        btn.addEventListener('click', () => openModal(
            'reject',
            btn.dataset.orderId,
            btn.dataset.orderNumber,
            `Reject order <strong>${escapeHtml(btn.dataset.orderNumber)}</strong>?<br><br>This will cancel the order. This action cannot be undone.`
        ));
    });

    // ── InstaPay Payment Verify / Reject ──────────────────────────────────
    document.querySelectorAll('.btn-verify-payment').forEach(btn => {
        btn.addEventListener('click', () => openModal(
            'verify-payment',
            btn.dataset.orderId,
            btn.dataset.orderNumber,
            `Verify InstaPay payment for order <strong>${escapeHtml(btn.dataset.orderNumber)}</strong>?<br><br>` +
            `Transaction Reference: <code style="background:#f0effe;padding:2px 8px;border-radius:4px;">${escapeHtml(btn.dataset.reference || '—')}</code><br><br>` +
            `Confirming this will mark the payment as <strong>Paid</strong> and move the order to <strong>Processing</strong>.`
        ));
    });

    document.querySelectorAll('.btn-reject-payment').forEach(btn => {
        btn.addEventListener('click', () => openModal(
            'reject-payment',
            btn.dataset.orderId,
            btn.dataset.orderNumber,
            `Reject InstaPay payment for order <strong>${escapeHtml(btn.dataset.orderNumber)}</strong>?<br><br>` +
            `Transaction Reference: <code style="background:#fce4ec;padding:2px 8px;border-radius:4px;">${escapeHtml(btn.dataset.reference || '—')}</code><br><br>` +
            `This will mark the payment as <strong>Failed</strong> and cancel the order. This cannot be undone.`
        ));
    });

    // ── Modal controls ────────────────────────────────────────────────────
    modalCancel?.addEventListener('click', closeModal);
    modalClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    modalConfirm?.addEventListener('click', async () => {
        if (!pendingAction) return;
        setLoading(true);

        const { type, orderId } = pendingAction;

        // Map action type to endpoint
        const endpointMap = {
            'accept':         'accept',
            'reject':         'reject',
            'verify-payment': 'verify-payment',
            'reject-payment': 'reject-payment',
        };
        const endpoint = endpointMap[type] || type;
        const url = `${basePath}/admin/orders/${orderId}/${endpoint}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${encodeURIComponent(csrfToken)}`,
            });
            const data = await response.json();

            if (data.success) {
                closeModal();
                showToast(data.message, 'success');
                updateOrderRow(orderId, data.status, data.payment_status);
                updateDetailPage(data.status, data.payment_status, orderId);
            } else {
                showToast(data.message || 'Action failed.', 'error');
                setLoading(false);
            }
        } catch {
            showToast('Network error. Please try again.', 'error');
            setLoading(false);
        }
    });

    // ── Modal helpers ─────────────────────────────────────────────────────
    function openModal(type, orderId, orderNumber, message) {
        pendingAction = { type, orderId, orderNumber };

        const titles = {
            'accept':         'Accept Order',
            'reject':         'Reject Order',
            'verify-payment': '✓ Verify InstaPay Payment',
            'reject-payment': '✕ Reject InstaPay Payment',
        };
        const btnLabels = {
            'accept':         'Accept Order',
            'reject':         'Reject Order',
            'verify-payment': 'Verify Payment',
            'reject-payment': 'Reject Payment',
        };
        const btnClasses = {
            'accept':         'btn-admin-primary btn-admin-success',
            'reject':         'btn-admin-primary btn-admin-danger',
            'verify-payment': 'btn-admin-primary btn-admin-success',
            'reject-payment': 'btn-admin-primary btn-admin-danger',
        };

        modalTitle.textContent = titles[type] || 'Confirm Action';
        modalMessage.innerHTML = message;
        modal.hidden = false;
        modalConfirm.className = btnClasses[type] || 'btn-admin-primary';
        modalConfirm.querySelector('.btn-text').textContent = btnLabels[type] || 'Confirm';
        modalConfirm.querySelector('.btn-loading').hidden = true;
        modalConfirm.disabled = false;
    }

    function closeModal() {
        modal.hidden = true;
        pendingAction = null;
        setLoading(false);
    }

    function setLoading(loading) {
        const btnText    = modalConfirm.querySelector('.btn-text');
        const btnLoading = modalConfirm.querySelector('.btn-loading');
        modalConfirm.disabled = loading;
        btnText.hidden    = loading;
        btnLoading.hidden = !loading;
    }

    // ── DOM update helpers ────────────────────────────────────────────────
    function updateOrderRow(orderId, newStatus, newPaymentStatus) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) return;

        // Update row classes
        row.classList.remove('row-pending', 'row-instapay-verify');
        if (newStatus === 'pending') row.classList.add('row-pending');

        // Update order status badge
        const statusCell = row.querySelector('.order-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="badge badge-${newStatus}">${capitalize(newStatus)}</span>`;
        }

        // Update pay status badge (second badge column)
        const payStatusBadge = row.querySelector(`.badge[class*="badge-pay-"]`);
        if (payStatusBadge && newPaymentStatus) {
            const label = newPaymentStatus.replace('_', ' ');
            payStatusBadge.className = `badge badge-pay-${newPaymentStatus}`;
            payStatusBadge.textContent = capitalize(label);
        }

        // Remove action buttons if no longer actionable
        const actionsCell = row.querySelector('.order-actions-cell');
        if (actionsCell) {
            actionsCell.querySelectorAll(
                '.btn-accept-order, .btn-reject-order, .btn-verify-payment, .btn-reject-payment'
            ).forEach(el => el.remove());
        }
    }

    function updateDetailPage(newStatus, newPaymentStatus, orderId) {
        // Update status badges
        const badge = document.getElementById('detail-status-badge');
        if (badge) {
            badge.className = `badge badge-${newStatus}`;
            badge.textContent = capitalize(newStatus);
        }

        const payBadge = document.getElementById('detail-pay-status-badge');
        if (payBadge && newPaymentStatus) {
            payBadge.className = `badge badge-pay-${newPaymentStatus}`;
            payBadge.textContent = capitalize(newPaymentStatus.replace('_', ' '));
        }

        const payBadgeInner = document.getElementById('detail-pay-badge-inner');
        if (payBadgeInner && newPaymentStatus) {
            payBadgeInner.className = `badge badge-pay-${newPaymentStatus}`;
            payBadgeInner.textContent = capitalize(newPaymentStatus.replace('_', ' '));
        }

        // Hide action buttons
        const actionBtns = document.getElementById('detail-action-buttons');
        if (actionBtns) {
            actionBtns.querySelectorAll(
                '.btn-accept-order, .btn-reject-order, .btn-verify-payment, .btn-reject-payment'
            ).forEach(el => el.remove());
        }

        // Hide inline verify actions panel
        const inlineActions = document.getElementById('instapay-verify-actions');
        if (inlineActions) inlineActions.remove();

        // Reload after short delay for full page refresh of status form
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            setTimeout(() => window.location.reload(), 1400);
        }
    }

    // ── Toast helper ──────────────────────────────────────────────────────
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('visible'));
        setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => toast.remove(), 400);
        }, 4500);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
