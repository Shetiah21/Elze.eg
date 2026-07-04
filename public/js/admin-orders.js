/**
 * Elze.eg Admin Order Management — Accept / Reject with AJAX
 */
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.getElementById('admin-csrf-token')?.value;
    const basePath = document.getElementById('admin-base-path')?.value || '';
    const modal = document.getElementById('order-action-modal');
    const modalMessage = document.getElementById('order-modal-message');
    const modalTitle = document.getElementById('order-modal-title');
    const modalConfirm = document.getElementById('order-modal-confirm');
    const modalCancel = document.getElementById('order-modal-cancel');
    const modalClose = document.getElementById('order-modal-close');
    const toastContainer = document.getElementById('admin-toast-container');

    let pendingAction = null;

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

    modalCancel?.addEventListener('click', closeModal);
    modalClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    modalConfirm?.addEventListener('click', async () => {
        if (!pendingAction) return;
        setLoading(true);

        const endpoint = pendingAction.type === 'accept' ? 'accept' : 'reject';
        const url = `${basePath}/admin/orders/${pendingAction.orderId}/${endpoint}`;

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
                updateOrderRow(pendingAction.orderId, data.status);
                updateDetailPage(data.status, pendingAction.orderId);
            } else {
                showToast(data.message || 'Action failed.', 'error');
            }
        } catch {
            showToast('Network error. Please try again.', 'error');
        } finally {
            setLoading(false);
        }
    });

    function openModal(type, orderId, orderNumber, message) {
        pendingAction = { type, orderId, orderNumber };
        modalTitle.textContent = type === 'accept' ? 'Accept Order' : 'Reject Order';
        modalMessage.innerHTML = message;
        modal.hidden = false;
        modalConfirm.className = type === 'accept' ? 'btn-admin-primary btn-admin-success' : 'btn-admin-primary btn-admin-danger';
        modalConfirm.querySelector('.btn-text').textContent = type === 'accept' ? 'Accept Order' : 'Reject Order';
    }

    function closeModal() {
        modal.hidden = true;
        pendingAction = null;
        setLoading(false);
    }

    function setLoading(loading) {
        const btnText = modalConfirm.querySelector('.btn-text');
        const btnLoading = modalConfirm.querySelector('.btn-loading');
        modalConfirm.disabled = loading;
        btnText.hidden = loading;
        btnLoading.hidden = !loading;
    }

    function updateOrderRow(orderId, newStatus) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) return;

        row.classList.remove('row-pending');
        if (newStatus === 'pending') row.classList.add('row-pending');

        const statusCell = row.querySelector('.order-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="badge badge-${newStatus}">${capitalize(newStatus)}</span>`;
        }

        const actionsCell = row.querySelector('.order-actions-cell');
        if (actionsCell && newStatus !== 'pending') {
            actionsCell.querySelectorAll('.btn-accept-order, .btn-reject-order').forEach(el => el.remove());
        }
    }

    function updateDetailPage(newStatus, orderId) {
        const badge = document.getElementById('detail-status-badge');
        if (badge) {
            badge.className = `badge badge-${newStatus}`;
            badge.textContent = capitalize(newStatus);
        }
        const actionBtns = document.getElementById('detail-action-buttons');
        if (actionBtns && newStatus !== 'pending') {
            actionBtns.innerHTML = '';
        }
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            setTimeout(() => { window.location.reload(); }, 1200);
        }
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('visible'));
        setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
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
