// Elze.eg E-Commerce Global Scripting
// Generated: 2026-07-02

document.addEventListener('DOMContentLoaded', () => {
    console.log('Elze.eg client-side script loaded successfully.');

    // Alert Dismissal Animations
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Dropdown Menu Behaviors for Mobile Devices
    const dropdownTrigger = document.querySelector('.dropdown-trigger');
    if (dropdownTrigger) {
        dropdownTrigger.addEventListener('click', (e) => {
            const content = dropdownTrigger.nextElementSibling;
            if (content) {
                const isVisible = window.getComputedStyle(content).display === 'block';
                content.style.display = isVisible ? 'none' : 'block';
                e.stopPropagation();
            }
        });
    }

    document.addEventListener('click', () => {
        const contents = document.querySelectorAll('.dropdown-content');
        contents.forEach(content => {
            content.style.display = 'none';
        });
    });

    // Auth form loading states
    document.querySelectorAll('.auth-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (form.id === 'register-form' && !validateRegisterForm(form)) {
                e.preventDefault();
                return;
            }
            if (form.id === 'reset-password-form' && !validateResetPasswordForm(form)) {
                e.preventDefault();
                return;
            }

            const submitBtn = form.querySelector('.btn-submit');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.textContent = 'Processing...';
                submitBtn.classList.add('is-loading');
            }
        });
    });

    // ── Real-time field-level validation (input & blur) ──
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        const fields = ['name', 'email', 'password', 'confirm_password'];
        fields.forEach(fieldId => {
            const input = registerForm.querySelector('#' + fieldId);
            if (input) {
                input.addEventListener('blur', () => validateRegisterField(registerForm, fieldId));
                input.addEventListener('input', () => {
                    // Clear error on typing so it doesn't feel nagging
                    const errorEl = registerForm.querySelector(`.field-error[data-for="${fieldId}"]`);
                    if (errorEl && errorEl.textContent) {
                        // Re-validate silently after a short delay
                        clearTimeout(input._validationTimer);
                        input._validationTimer = setTimeout(() => validateRegisterField(registerForm, fieldId), 400);
                    }
                });
            }
        });
    }

    const resetForm = document.getElementById('reset-password-form');
    if (resetForm) {
        const fields = ['otp', 'password', 'confirm_password'];
        fields.forEach(fieldId => {
            const input = resetForm.querySelector('#' + fieldId);
            if (input) {
                input.addEventListener('blur', () => validateResetField(resetForm, fieldId));
                input.addEventListener('input', () => {
                    const errorEl = resetForm.querySelector(`.field-error[data-for="${fieldId}"]`);
                    if (errorEl && errorEl.textContent) {
                        clearTimeout(input._validationTimer);
                        input._validationTimer = setTimeout(() => validateResetField(resetForm, fieldId), 400);
                    }
                });
            }
        });
    }
});

function validateRegisterForm(form) {
    clearFieldErrors(form);

    const name = form.querySelector('#name');
    const email = form.querySelector('#email');
    const password = form.querySelector('#password');
    const confirmPassword = form.querySelector('#confirm_password');
    let isValid = true;

    if (!name.value.trim() || name.value.trim().length < 2) {
        showFieldError(form, 'name', 'Please enter your full name.');
        isValid = false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim() || !emailPattern.test(email.value.trim())) {
        showFieldError(form, 'email', 'Please enter a valid email address.');
        isValid = false;
    }

    const hasUpperCase = /[A-Z]/.test(password.value);
    const hasLowerCase = /[a-z]/.test(password.value);
    const hasNumbers = /\d/.test(password.value);
    const hasNonalphas = /[^A-Za-z0-9]/.test(password.value);

    if (!password.value || password.value.length < 8 || !hasUpperCase || !hasLowerCase || !hasNumbers || !hasNonalphas) {
        showFieldError(form, 'password', 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.');
        isValid = false;
    }

    if (password.value !== confirmPassword.value) {
        showFieldError(form, 'confirm_password', 'Passwords do not match.');
        isValid = false;
    }

    return isValid;
}

function showFieldError(form, fieldName, message) {
    const errorEl = form.querySelector(`.field-error[data-for="${fieldName}"]`);
    const inputEl = form.querySelector(`#${fieldName}`);
    if (errorEl) {
        errorEl.textContent = message;
    }
    if (inputEl) {
        inputEl.classList.add('input-error');
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(el => {
        el.textContent = '';
    });
    form.querySelectorAll('.auth-input.input-error').forEach(el => {
        el.classList.remove('input-error');
    });
}

function validateResetPasswordForm(form) {
    clearFieldErrors(form);

    const otp = form.querySelector('#otp');
    const password = form.querySelector('#password');
    const confirmPassword = form.querySelector('#confirm_password');
    let isValid = true;

    if (otp && (!otp.value.trim() || !/^\d{6}$/.test(otp.value.trim()))) {
        showFieldError(form, 'otp', 'Please enter a valid 6-digit recovery code.');
        isValid = false;
    }

    const hasUpperCase = /[A-Z]/.test(password.value);
    const hasLowerCase = /[a-z]/.test(password.value);
    const hasNumbers = /\d/.test(password.value);
    const hasNonalphas = /[^A-Za-z0-9]/.test(password.value);

    if (!password.value || password.value.length < 8 || !hasUpperCase || !hasLowerCase || !hasNumbers || !hasNonalphas) {
        showFieldError(form, 'password', 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.');
        isValid = false;
    }

    if (password.value !== confirmPassword.value) {
        showFieldError(form, 'confirm_password', 'Passwords do not match.');
        isValid = false;
    }

    return isValid;
}

/**
 * Validate a single field in the register form (for real-time feedback)
 */
function validateRegisterField(form, fieldId) {
    const errorEl = form.querySelector(`.field-error[data-for="${fieldId}"]`);
    const inputEl = form.querySelector('#' + fieldId);
    if (!errorEl || !inputEl) return;

    // Clear previous state
    errorEl.textContent = '';
    inputEl.classList.remove('input-error');

    const val = inputEl.value;

    switch (fieldId) {
        case 'name':
            if (val.trim().length > 0 && val.trim().length < 2) {
                showFieldError(form, 'name', 'Name must be at least 2 characters.');
            }
            break;

        case 'email':
            if (val.trim().length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim())) {
                showFieldError(form, 'email', 'Please enter a valid email address.');
            }
            break;

        case 'password':
            if (val.length > 0) {
                const missing = [];
                if (val.length < 8) missing.push('8+ characters');
                if (!/[A-Z]/.test(val)) missing.push('uppercase letter');
                if (!/[a-z]/.test(val)) missing.push('lowercase letter');
                if (!/\d/.test(val)) missing.push('number');
                if (!/[^A-Za-z0-9]/.test(val)) missing.push('special character');
                if (missing.length > 0) {
                    showFieldError(form, 'password', 'Missing: ' + missing.join(', ') + '.');
                }
            }
            // Also re-validate confirm_password if it has a value
            const confirmEl = form.querySelector('#confirm_password');
            if (confirmEl && confirmEl.value.length > 0 && confirmEl.value !== val) {
                showFieldError(form, 'confirm_password', 'Passwords do not match.');
            } else if (confirmEl && confirmEl.value.length > 0) {
                const cErr = form.querySelector('.field-error[data-for="confirm_password"]');
                if (cErr) cErr.textContent = '';
                confirmEl.classList.remove('input-error');
            }
            break;

        case 'confirm_password':
            const pwEl = form.querySelector('#password');
            if (val.length > 0 && pwEl && val !== pwEl.value) {
                showFieldError(form, 'confirm_password', 'Passwords do not match.');
            }
            break;
    }
}

/**
 * Validate a single field in the reset-password form (for real-time feedback)
 */
function validateResetField(form, fieldId) {
    const errorEl = form.querySelector(`.field-error[data-for="${fieldId}"]`);
    const inputEl = form.querySelector('#' + fieldId);
    if (!errorEl || !inputEl) return;

    errorEl.textContent = '';
    inputEl.classList.remove('input-error');

    const val = inputEl.value;

    switch (fieldId) {
        case 'otp':
            if (val.trim().length > 0 && !/^\d{6}$/.test(val.trim())) {
                showFieldError(form, 'otp', 'Must be exactly 6 digits.');
            }
            break;

        case 'password':
            if (val.length > 0) {
                const missing = [];
                if (val.length < 8) missing.push('8+ characters');
                if (!/[A-Z]/.test(val)) missing.push('uppercase letter');
                if (!/[a-z]/.test(val)) missing.push('lowercase letter');
                if (!/\d/.test(val)) missing.push('number');
                if (!/[^A-Za-z0-9]/.test(val)) missing.push('special character');
                if (missing.length > 0) {
                    showFieldError(form, 'password', 'Missing: ' + missing.join(', ') + '.');
                }
            }
            const confirmEl = form.querySelector('#confirm_password');
            if (confirmEl && confirmEl.value.length > 0 && confirmEl.value !== val) {
                showFieldError(form, 'confirm_password', 'Passwords do not match.');
            } else if (confirmEl && confirmEl.value.length > 0) {
                const cErr = form.querySelector('.field-error[data-for="confirm_password"]');
                if (cErr) cErr.textContent = '';
                confirmEl.classList.remove('input-error');
            }
            break;

        case 'confirm_password':
            const pwEl = form.querySelector('#password');
            if (val.length > 0 && pwEl && val !== pwEl.value) {
                showFieldError(form, 'confirm_password', 'Passwords do not match.');
            }
            break;
    }
}
