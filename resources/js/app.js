/**
 * MessV2 - Main JavaScript Entry Point
 */

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    console.log('MessV2 initialized');

    // Re-initialize Lucide icons after dynamic content
    if (window.lucide) {
        lucide.createIcons();
    }

    // Initialize components
    initTabs();
    initToggles();
    initMobileNav();
});

/**
 * Initialize tab components
 */
function initTabs() {
    document.querySelectorAll('.tabs').forEach(tabGroup => {
        const tabs = tabGroup.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    });
}

/**
 * Initialize toggle switches
 */
function initToggles() {
    document.querySelectorAll('.toggle input').forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            const isOn = e.target.checked;
            console.log('Toggle changed:', isOn);
        });
    });
}

/**
 * Mobile navigation toggle
 */
function initMobileNav() {
    // Add mobile menu button if needed
    const mobileBreakpoint = 768;

    if (window.innerWidth <= mobileBreakpoint) {
        // Mobile specific initialization
    }
}

/**
 * API Client
 */
const API = {
    baseUrl: '/messv2/api',

    async get(endpoint) {
        const response = await fetch(this.baseUrl + endpoint);
        return response.json();
    },

    async post(endpoint, data) {
        const response = await fetch(this.baseUrl + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        return response.json();
    }
};

/**
 * Notification helper
 */
function showNotification(message, type = 'info') {
    // TODO: Implement notification toast
    console.log(`${type}: ${message}`);
}

// Export for modules
window.MessV2 = {
    API,
    showNotification
};
