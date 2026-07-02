// Elze.eg E-Commerce Global Scripting
// Generated: 2026-07-02

document.addEventListener('DOMContentLoaded', () => {
    // 1. Log Initialization
    console.log('Elze.eg client-side script loaded successfully.');

    // 2. Alert Dismissal Animations
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Automatically fade out notifications after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // 3. Dropdown Menu Behaviors for Mobile Devices
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

    // Close menus if clicking outside
    document.addEventListener('click', () => {
        const contents = document.querySelectorAll('.dropdown-content');
        contents.forEach(content => {
            content.style.display = 'none';
        });
    });
});
