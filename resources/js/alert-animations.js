/**
 * Alert & Notification Animations
 * Smooth transitions for success/error messages with auto-dismiss
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all alert/success messages
    const alerts = document.querySelectorAll(
        '[class*="bg-green-"], [class*="bg-blue-"], [class*="bg-yellow-"], [class*="bg-red-"], .alert, .notification'
    );
    
    alerts.forEach((alert, index) => {
        // Skip if it's part of a form or permanent element
        if (alert.closest('form') || alert.hasAttribute('data-permanent')) {
            return;
        }
        
        // Check if it's likely a status message
        const isStatusMessage = alert.textContent.includes('success') || 
                               alert.textContent.includes('created') ||
                               alert.textContent.includes('updated') ||
                               alert.textContent.includes('deleted') ||
                               alert.classList.contains('alert');
        
        if (!isStatusMessage) return;
        
        // Animate in with stagger
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            alert.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(0)';
        }, index * 100);
        
        // Add close button if not present
        if (!alert.querySelector('[data-dismiss]')) {
            const closeButton = document.createElement('button');
            closeButton.setAttribute('data-dismiss', 'alert');
            closeButton.setAttribute('aria-label', 'Close');
            closeButton.className = 'ml-auto flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity';
            closeButton.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
            
            // If alert has a flex container, append to it
            const flexContainer = alert.querySelector('.flex');
            if (flexContainer) {
                flexContainer.appendChild(closeButton);
            } else {
                // Make alert flex and add close button
                if (!alert.classList.contains('flex')) {
                    const content = alert.innerHTML;
                    alert.innerHTML = '';
                    alert.classList.add('flex', 'items-start', 'gap-3');
                    
                    const contentDiv = document.createElement('div');
                    contentDiv.className = 'flex-1';
                    contentDiv.innerHTML = content;
                    
                    alert.appendChild(contentDiv);
                    alert.appendChild(closeButton);
                }
            }
            
            // Close button click handler
            closeButton.addEventListener('click', function() {
                dismissAlert(alert);
            });
        }
        
        // Auto-dismiss after 5 seconds (only for success messages)
        if (alert.classList.contains('bg-green-100') || 
            alert.classList.contains('bg-green-50') ||
            alert.textContent.toLowerCase().includes('success')) {
            
            setTimeout(() => {
                dismissAlert(alert);
            }, 5000);
        }
    });
    
    // Handle manual dismiss buttons
    document.addEventListener('click', function(e) {
        const dismissBtn = e.target.closest('[data-dismiss="alert"]');
        if (dismissBtn) {
            e.preventDefault();
            const alert = dismissBtn.closest('[class*="bg-"], .alert, .notification');
            if (alert) {
                dismissAlert(alert);
            }
        }
    });
});

function dismissAlert(alert) {
    // Smooth fade out and slide out animation
    alert.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    alert.style.opacity = '0';
    alert.style.transform = 'translateX(100%)';
    alert.style.maxHeight = alert.offsetHeight + 'px';
    
    setTimeout(() => {
        alert.style.maxHeight = '0';
        alert.style.marginBottom = '0';
        alert.style.paddingTop = '0';
        alert.style.paddingBottom = '0';
    }, 300);
    
    setTimeout(() => {
        alert.remove();
    }, 600);
}

// Toast notifications (if you want to add programmatic notifications)
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 max-w-md p-4 rounded-lg shadow-2xl flex items-start gap-3 ${getToastClasses(type)}`;
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-100%)';
    
    const icon = getToastIcon(type);
    const content = `
        <div class="flex-shrink-0">${icon}</div>
        <div class="flex-1">${message}</div>
        <button onclick="this.parentElement.remove()" class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    toast.innerHTML = content;
    document.body.appendChild(toast);
    
    // Animate in
    requestAnimationFrame(() => {
        toast.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });
    
    // Auto dismiss
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-100%)';
        setTimeout(() => toast.remove(), 400);
    }, duration);
    
    return toast;
}

function getToastClasses(type) {
    const classes = {
        'success': 'bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200',
        'error': 'bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200',
        'warning': 'bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 text-yellow-700 dark:text-yellow-200',
        'info': 'bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500 text-blue-700 dark:text-blue-200'
    };
    return classes[type] || classes['info'];
}

function getToastIcon(type) {
    const icons = {
        'success': '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        'error': '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        'warning': '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        'info': '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
    };
    return icons[type] || icons['info'];
}

// Export for global use
window.alertAnimations = {
    dismissAlert,
    showToast
};

console.log('✨ Alert animations loaded');
