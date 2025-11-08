/**
 * Professional Page Transitions & Animations
 * Smooth, 60fps animations for modern web experience
 */

// Page load animation
document.addEventListener('DOMContentLoaded', function() {
    const mode = window.ANIMATIONS_MODE || 'full';
    // Always ensure body is visible quickly to avoid perceived blank pages
    document.body.style.opacity = '1';
    document.body.style.transition = 'none';

    if (mode === 'none') {
        // Hard disable: no transformations applied
        return;
    }
    if (mode === 'scroll-only') {
        return; // Only scroll observer below will handle reveals
    }
    // Optimized full mode: subtle initial fade of key content without hiding whole page
    requestAnimationFrame(() => {
        const rootContent = document.querySelector('main') || document.body;
        rootContent.style.opacity = '0';
        rootContent.style.willChange = 'opacity';
        rootContent.style.transition = 'opacity 0.15s ease-out';
        requestAnimationFrame(() => {
            rootContent.style.opacity = '1';
            setTimeout(() => { rootContent.style.willChange = 'auto'; }, 250);
        });
    });
});

// Smooth page navigation transitions
document.addEventListener('click', function(e) {
    const mode = window.ANIMATIONS_MODE || 'full';
    if (mode !== 'full') return; // Only apply navigation fades in full mode
    const link = e.target.closest('a[href]');
    if (link && link.href && !link.target && !link.download &&
        link.href.startsWith(window.location.origin) &&
        !link.href.includes('#') &&
        !e.ctrlKey && !e.metaKey) {
        if (link.href.includes('logout') || link.hasAttribute('data-no-transition')) {
            return;
        }
        e.preventDefault();
        const main = document.querySelector('main') || document.body;
        main.style.opacity = '1'; // ensure starting state
        main.style.transition = 'opacity 0.12s ease-out';
        requestAnimationFrame(() => {
            main.style.opacity = '0';
        });
        setTimeout(() => { window.location.href = link.href; }, 130);
    }
});

// Ensure restored pages from BFCache / back navigation are visible
window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
        document.body.style.opacity = '1';
        const main = document.querySelector('main');
        if (main) main.style.opacity = '1';
    }
});

// Enhanced button interactions
document.addEventListener('click', function(e) {
    const mode = window.ANIMATIONS_MODE || 'full';
    if (mode !== 'full') return; // Disable ripple when animations are off
    const button = e.target.closest('button, .btn, [role="button"]');
    if (button && !button.disabled) {
        // Add ripple effect
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            pointer-events: none;
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
        `;
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
});

// Add ripple animation keyframes
if (!document.getElementById('ripple-styles')) {
    const style = document.createElement('style');
    style.id = 'ripple-styles';
    style.textContent = `
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Smooth scroll to anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const mode = window.ANIMATIONS_MODE || 'full';
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            if (mode === 'full') {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                target.scrollIntoView({ behavior: 'auto', block: 'start' });
            }
        }
    });
});

// Intersection Observer for fade-in on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    const mode = window.ANIMATIONS_MODE || 'full';
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // If element or any ancestor opts out of animations, just show it
            if (entry.target.closest('.no-animations')) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'none';
                observer.unobserve(entry.target);
                return;
            }
            if (mode === 'none') {
                // Instantly show without animation
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'none';
            } else {
                entry.target.classList.add('animate-on-scroll');
            }
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe elements that should animate on scroll
setTimeout(() => {
    const mode = window.ANIMATIONS_MODE || 'full';
    const elementsToAnimate = document.querySelectorAll('.card, .bg-white, section, .grid > div');
    elementsToAnimate.forEach(el => {
        if (!el.classList.contains('page-content')) {
            // Skip elements inside .no-animations containers entirely
            if (el.closest('.no-animations')) {
                el.style.opacity = '1';
                el.style.transform = 'none';
                return;
            }
            if (mode === 'none') {
                el.style.opacity = '1';
                el.style.transform = 'none';
            } else {
                el.style.opacity = '0';
                el.style.transform = 'translateY(16px)';
                // Faster & lighter transitions
                const baseDuration = mode === 'scroll-only' ? '0.18s' : '0.22s';
                el.style.transition = `opacity ${baseDuration} ease-out, transform ${baseDuration} ease-out`;
                observer.observe(el);
            }
        }
    });
}, 60);

// When element is observed and animating
document.addEventListener('animationstart', function(e) {
    if (e.target.classList.contains('animate-on-scroll')) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
    }
});

// Form validation animations
document.addEventListener('invalid', function(e) {
    e.preventDefault();
    const input = e.target;
    
    if (input.classList.contains('is-invalid')) return;
    
    input.classList.add('is-invalid');
    input.style.animation = 'shake 0.4s ease-in-out';
    
    setTimeout(() => {
        input.style.animation = '';
    }, 400);
}, true);

// Success animation for valid inputs
document.addEventListener('input', function(e) {
    const input = e.target;
    if (input.validity.valid && input.classList.contains('is-invalid')) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.style.animation = 'pulse 0.6s ease-in-out';
        
        setTimeout(() => {
            input.style.animation = '';
            input.classList.remove('is-valid');
        }, 600);
    }
});

// Smooth modal transitions
document.addEventListener('show.bs.modal', function(e) {
    const modal = e.target;
    modal.style.animation = 'modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
});

// Enhanced dropdown animations
document.addEventListener('click', function(e) {
    const trigger = e.target.closest('[data-dropdown-trigger]');
    if (trigger) {
        const dropdown = document.querySelector(trigger.getAttribute('data-dropdown-trigger'));
        if (dropdown) {
            dropdown.style.animation = 'dropdownSlideDown 0.25s ease-out';
        }
    }
});

// Loading state animations
function showLoadingState(element) {
    element.classList.add('loading-pulse');
    element.style.pointerEvents = 'none';
}

function hideLoadingState(element) {
    element.classList.remove('loading-pulse');
    element.style.pointerEvents = '';
}

// Export for use in other scripts
window.animations = {
    showLoadingState,
    hideLoadingState
};

// Smooth transitions for dynamically added content
if ((window.ANIMATIONS_MODE || 'full') === 'full') {
    const mutationObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    node.style.opacity = '0';
                    node.style.transform = 'translateY(10px)';
                    requestAnimationFrame(() => {
                        node.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        node.style.opacity = '1';
                        node.style.transform = 'translateY(0)';
                    });
                }
            });
        });
    });
    mutationObserver.observe(document.body, { childList: true, subtree: true });
}

// Optimize animations for performance
let ticking = false;

function optimizeAnimations() {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            // Remove will-change after animations complete
            const animatedElements = document.querySelectorAll('[style*="will-change"]');
            animatedElements.forEach(el => {
                if (!el.matches(':hover, :focus, :active')) {
                    el.style.willChange = 'auto';
                }
            });
            ticking = false;
        });
        ticking = true;
    }
}

// Run optimization periodically
setInterval(optimizeAnimations, 2000);

// Handle prefers-reduced-motion
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.style.setProperty('--animation-duration', '0.01ms');
    document.documentElement.style.setProperty('--transition-duration', '0.01ms');
}

console.log('✨ Professional animations loaded');
