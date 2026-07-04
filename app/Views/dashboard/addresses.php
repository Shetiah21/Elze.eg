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
                <a href="#" class="sidebar-link">Order History</a>
                <a href="<?= $base ?>/dashboard/addresses" class="sidebar-link <?= ($active_tab === 'addresses') ? 'active' : '' ?>">Saved Addresses</a>
                <a href="<?= $base ?>/logout" class="sidebar-link" style="color: var(--color-danger); border-top: 1px solid var(--color-grey-border); margin-top: 16px; padding-top: 16px;">Logout</a>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="dashboard-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <h3 class="dashboard-section-title" style="margin-bottom: 0;">Saved Addresses</h3>
                <button type="button" class="btn btn-primary" onclick="openAddModal()" style="background-color: var(--color-brand-blue); color: var(--color-white); padding: 8px 16px; font-size: 13px;">
                    + Add New Address
                </button>
            </div>
            
            <?php if (empty($addresses)): ?>
                <div style="background-color: var(--color-white); padding: 48px; border-radius: var(--border-radius-md); border: 1px solid var(--color-grey-border); text-align: center;">
                    <div style="color: var(--color-charcoal-light); margin-bottom: 16px;">
                        <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" style="margin: 0 auto;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h4 style="font-family: var(--font-headers); font-size: 18px; margin-bottom: 8px; color: var(--color-brand-blue);">No Saved Addresses</h4>
                    <p style="font-size: 13px; color: var(--color-charcoal-light); margin-bottom: 20px;">You haven't saved any shipping addresses yet. Add one to speed up checkout.</p>
                    <button type="button" class="btn btn-outline" onclick="openAddModal()" style="border-color: var(--color-brand-blue); color: var(--color-brand-blue); padding: 8px 16px;">Add First Address</button>
                </div>
            <?php else: ?>
                <div class="address-grid">
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-card <?= $address['is_default'] ? 'default-address' : '' ?>" id="address-card-<?= $address['id'] ?>">
                            <?php if ($address['is_default']): ?>
                                <span class="address-default-badge">Default Shipping</span>
                            <?php endif; ?>

                            <div class="address-details">
                                <h4 class="address-recipient"><?= htmlspecialchars($address['recipient_name']) ?></h4>
                                <p class="address-phone">📞 <?= htmlspecialchars($address['phone_number']) ?></p>
                                
                                <p class="address-location">
                                    <strong><?= htmlspecialchars($address['street_address']) ?></strong><br>
                                    <?php if (!empty($address['building_details'])): ?>
                                        <?= htmlspecialchars($address['building_details']) ?><br>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['governorate']) ?><br>
                                    Egypt
                                </p>
                            </div>

                            <div class="address-actions">
                                <span class="address-action-link" onclick="openEditModal(<?= htmlspecialchars(json_encode($address)) ?>)">Edit</span>
                                
                                <?php if (!$address['is_default']): ?>
                                    <form action="<?= $base ?>/dashboard/addresses/make-default/<?= $address['id'] ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <button type="submit" class="address-action-link" style="background:none; border:none; padding:0; font-family:inherit; font-size:inherit; font-weight:inherit; text-decoration:none;">Set Default</button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?= $base ?>/dashboard/addresses/delete/<?= $address['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');" style="display:inline; margin-left: auto;">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <button type="submit" class="address-action-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- ── Add Address Modal ── -->
<div class="address-modal-backdrop" id="add-address-modal" onclick="closeModalOutside(event, 'add-address-modal')">
    <div class="address-modal-box">
        <div class="address-modal-header">
            <h3>Add Saved Address</h3>
            <button class="address-modal-close" onclick="closeModal('add-address-modal')">✕</button>
        </div>
        <form class="address-form" action="<?= $base ?>/dashboard/addresses/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label for="add-recipient_name">Recipient Full Name *</label>
                <input type="text" id="add-recipient_name" name="recipient_name" class="form-control" placeholder="e.g. Aly Maher" required>
            </div>

            <div class="form-group-row">
                <div class="form-group">
                    <label for="add-phone_number">Phone Number *</label>
                    <input type="tel" id="add-phone_number" name="phone_number" class="form-control" placeholder="e.g. 01001234567" required pattern="^01[0125][0-9]{8}$" title="Egyptian mobile format: 11 digits starting with 010, 011, 012, or 015">
                </div>

                <div class="form-group">
                    <label for="add-governorate">Governorate *</label>
                    <select id="add-governorate" name="governorate" class="form-control" required>
                        <option value="" disabled selected>Select Governorate</option>
                        <?php foreach ($governorates as $gov): ?>
                            <option value="<?= htmlspecialchars($gov) ?>"><?= htmlspecialchars($gov) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="add-city">City *</label>
                <input type="text" id="add-city" name="city" class="form-control" placeholder="e.g. Maadi or Nasr City" required>
            </div>

            <div class="form-group">
                <label for="add-street_address">Street Address *</label>
                <input type="text" id="add-street_address" name="street_address" class="form-control" placeholder="e.g. 15 Road 9" required>
            </div>

            <div class="form-group">
                <label for="add-building_details">Building, Floor, Flat Details (Optional)</label>
                <input type="text" id="add-building_details" name="building_details" class="form-control" placeholder="e.g. Building 4, 3rd Floor, Apt 7">
            </div>

            <div class="form-group">
                <label class="form-checkbox-label">
                    <input type="checkbox" name="is_default" value="1">
                    <span>Set as Default Shipping Address</span>
                </label>
            </div>

            <div class="address-form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-address-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background-color: var(--color-brand-blue); color: var(--color-white);">Save Address</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit Address Modal ── -->
<div class="address-modal-backdrop" id="edit-address-modal" onclick="closeModalOutside(event, 'edit-address-modal')">
    <div class="address-modal-box">
        <div class="address-modal-header">
            <h3>Edit Address Details</h3>
            <button class="address-modal-close" onclick="closeModal('edit-address-modal')">✕</button>
        </div>
        <form class="address-form" id="edit-address-form" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label for="edit-recipient_name">Recipient Full Name *</label>
                <input type="text" id="edit-recipient_name" name="recipient_name" class="form-control" required>
            </div>

            <div class="form-group-row">
                <div class="form-group">
                    <label for="edit-phone_number">Phone Number *</label>
                    <input type="tel" id="edit-phone_number" name="phone_number" class="form-control" required pattern="^01[0125][0-9]{8}$" title="Egyptian mobile format: 11 digits starting with 010, 011, 012, or 015">
                </div>

                <div class="form-group">
                    <label for="edit-governorate">Governorate *</label>
                    <select id="edit-governorate" name="governorate" class="form-control" required>
                        <?php foreach ($governorates as $gov): ?>
                            <option value="<?= htmlspecialchars($gov) ?>"><?= htmlspecialchars($gov) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="edit-city">City *</label>
                <input type="text" id="edit-city" name="city" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-street_address">Street Address *</label>
                <input type="text" id="edit-street_address" name="street_address" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-building_details">Building, Floor, Flat Details (Optional)</label>
                <input type="text" id="edit-building_details" name="building_details" class="form-control">
            </div>

            <div class="form-group" id="edit-default-group">
                <label class="form-checkbox-label">
                    <input type="checkbox" id="edit-is_default" name="is_default" value="1">
                    <span>Set as Default Shipping Address</span>
                </label>
            </div>

            <div class="address-form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('edit-address-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background-color: var(--color-brand-blue); color: var(--color-white);">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('add-address-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditModal(address) {
    // Populate form fields
    document.getElementById('edit-recipient_name').value = address.recipient_name;
    document.getElementById('edit-phone_number').value = address.phone_number;
    document.getElementById('edit-governorate').value = address.governorate;
    document.getElementById('edit-city').value = address.city;
    document.getElementById('edit-street_address').value = address.street_address;
    document.getElementById('edit-building_details').value = address.building_details || '';
    
    // Checkbox mapping
    const checkbox = document.getElementById('edit-is_default');
    checkbox.checked = address.is_default == 1;

    // Set action URL
    document.getElementById('edit-address-form').action = '<?= $base ?>/dashboard/addresses/update/' + address.id;

    // Open modal
    document.getElementById('edit-address-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOutside(e, id) {
    const box = document.querySelector('#' + id + ' .address-modal-box');
    if (e.target === document.getElementById(id)) {
        closeModal(id);
    }
}
</script>
