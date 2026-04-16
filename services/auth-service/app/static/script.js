/**
 * Theme Management System
 * Handles theme switching and persistence
 */

class ThemeManager {
    constructor() {
        this.html = document.documentElement;
        this.themeToggle = document.getElementById('theme-toggle');
        this.init();
    }

    init() {
        // Load saved theme or default to dark
        const savedTheme = localStorage.getItem('theme') || 'dark';
        this.setTheme(savedTheme);

        // Setup toggle button if it exists
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }

    setTheme(theme) {
        this.html.setAttribute('data-theme', theme);
        if (this.themeToggle) {
            this.themeToggle.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
        localStorage.setItem('theme', theme);
    }

    toggleTheme() {
        const currentTheme = this.html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    getCurrentTheme() {
        return this.html.getAttribute('data-theme');
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

/**
 * Utility Functions
 */

// Show loading state
function showLoading(element, text = 'Loading...') {
    if (element) {
        element.innerHTML = `<div class="loading">${text}</div>`;
        element.classList.add('loading-state');
    }
}

// Hide loading state
function hideLoading(element) {
    if (element) {
        element.classList.remove('loading-state');
    }
}

// Format relative time
function formatRelativeTime(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show feedback
        const notification = document.createElement('div');
        notification.className = 'copy-notification';
        notification.textContent = 'Copied to clipboard!';
        document.body.appendChild(notification);

        setTimeout(() => {
            document.body.removeChild(notification);
        }, 2000);
    });
}

// Add copy functionality to elements with data-copy attribute
document.addEventListener('click', (e) => {
    if (e.target.hasAttribute('data-copy')) {
        const text = e.target.getAttribute('data-copy');
        copyToClipboard(text);
    }
});

/**
 * Dashboard Specific Functions
 */

// Auto-refresh dashboard data (optional)
function setupAutoRefresh(interval = 30000) {
    setInterval(() => {
        // Only refresh if page is visible
        if (!document.hidden) {
            refreshDashboard();
        }
    }, interval);
}

function refreshDashboard() {
    // Implement dashboard refresh logic here
    console.log('Refreshing dashboard...');
    // This would typically make an AJAX call to get updated data
}

/**
 * Form Enhancement
 */

// Auto-focus first input
document.addEventListener('DOMContentLoaded', () => {
    const firstInput = document.querySelector('input:not([type="hidden"])');
    if (firstInput) {
        firstInput.focus();
    }
});

/**
 * Error Handling
 */

// Global error handler
window.addEventListener('error', (e) => {
    console.error('JavaScript Error:', e.error);
    // Could send to error reporting service
});

// Unhandled promise rejections
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled Promise Rejection:', e.reason);
    // Could send to error reporting service
});

/**
 * Performance Monitoring
 */

// Basic performance tracking
window.addEventListener('load', () => {
    if ('performance' in window) {
        const perfData = performance.getEntriesByType('navigation')[0];
        console.log('Page load time:', perfData.loadEventEnd - perfData.fetchStart, 'ms');
    }
});