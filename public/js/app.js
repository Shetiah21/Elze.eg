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

            const submitBtn = form.querySelector('.btn-submit');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.textContent = 'Processing...';
                submitBtn.classList.add('is-loading');
            }
        });
    });
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

    if (!password.value || password.value.length < 6) {
        showFieldError(form, 'password', 'Password must be at least 6 characters.');
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
