import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// ========================================
// Accessibility Utilities - Phase 5
// ========================================

/**
 * Announce message to screen readers via ARIA live region
 * @param {string} message - The message to announce
 * @param {string} priority - 'polite' (default) or 'assertive'
 */
window.announceToScreenReader = function(message, priority = 'polite') {
    const announcer = document.getElementById('sr-announcements');
    if (!announcer) return;

    // Clear previous announcement
    announcer.textContent = '';
    announcer.setAttribute('aria-live', priority);

    // Add new announcement after brief delay to ensure it's read
    setTimeout(() => {
        announcer.textContent = message;
    }, 100);

    // Clear after 5 seconds to prevent clutter
    setTimeout(() => {
        announcer.textContent = '';
    }, 5000);
};

/**
 * Trap focus within a modal or dialog
 * @param {HTMLElement} element - The container element
 */
window.trapFocus = function(element) {
    const focusableElements = element.querySelectorAll(
        'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    element.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab') return;

        if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
        }
    });
};

/**
 * Initialize accessibility features on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Announce success/error messages to screen readers
    const flashMessages = document.querySelectorAll('.badge-success, .badge-danger, .badge-warning, .badge-info');
    flashMessages.forEach(msg => {
        if (msg.textContent.trim()) {
            announceToScreenReader(msg.textContent.trim());
        }
    });

    // Add focus management for modals
    const modals = document.querySelectorAll('[role="dialog"], [role="alertdialog"]');
    modals.forEach(modal => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.style.display !== 'none' && mutation.target.style.display !== '') {
                    // Modal is shown
                    const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    if (firstFocusable) {
                        setTimeout(() => firstFocusable.focus(), 100);
                    }
                    trapFocus(modal);
                }
            });
        });

        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    });

    // Enhance form validation announcements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const invalidFields = form.querySelectorAll('[aria-invalid="true"]');
            if (invalidFields.length > 0) {
                announceToScreenReader(`Form has ${invalidFields.length} error${invalidFields.length > 1 ? 's' : ''}. Please review and correct.`, 'assertive');
            }
        });
    });
});

// ========================================
// Performance Utilities - Phase 6
// ========================================

/**
 * Lazy load images when they enter viewport
 */
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                    img.removeAttribute('data-srcset');
                }
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    // Observe all lazy images
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    });
}

/**
 * Debounce function for performance optimization
 */
window.debounce = function(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Throttle function for scroll/resize events
 */
window.throttle = function(func, limit = 100) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

/**
 * Performance monitoring
 */
if ('performance' in window) {
    window.addEventListener('load', () => {
        // Get performance metrics after page load
        setTimeout(() => {
            const perfData = window.performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            const connectTime = perfData.responseEnd - perfData.requestStart;
            const renderTime = perfData.domComplete - perfData.domLoading;

            // Log performance metrics (remove in production or send to analytics)
            if (process.env.NODE_ENV === 'development') {
                console.log('Performance Metrics:', {
                    pageLoadTime: `${pageLoadTime}ms`,
                    connectTime: `${connectTime}ms`,
                    renderTime: `${renderTime}ms`
                });
            }

            // Send to analytics if available
            if (typeof gtag === 'function') {
                gtag('event', 'timing_complete', {
                    name: 'load',
                    value: pageLoadTime,
                    event_category: 'Performance'
                });
            }
        }, 0);
    });
}

/**
 * Prefetch links on hover for faster navigation
 */
document.addEventListener('DOMContentLoaded', () => {
    const prefetchedLinks = new Set();

    const prefetchLink = (url) => {
        if (prefetchedLinks.has(url)) return;

        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
        prefetchedLinks.add(url);
    };

    // Prefetch on hover
    document.addEventListener('mouseover', throttle((e) => {
        const link = e.target.closest('a[href^="/"]');
        if (link && !link.hasAttribute('data-no-prefetch')) {
            prefetchLink(link.href);
        }
    }, 500));
});
