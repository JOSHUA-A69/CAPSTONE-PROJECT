/**
 * Enhanced Form Animations
 * Smooth transitions for form interactions and validation states
 */

document.addEventListener('DOMContentLoaded', function() {
    // Enhanced input focus effects
    const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
    
    inputs.forEach(input => {
        // Add smooth focus transition (subtle scale effect)
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.25s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Labels remain static - no floating animation
        // Labels are styled with .form-label class and stay above inputs
    });
    
    // Enhanced form submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                // Add loading state with smooth transition
                submitButton.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                submitButton.style.opacity = '0.6';
                submitButton.style.transform = 'scale(0.95)';
                
                // Add spinner if not present
                if (!submitButton.querySelector('.spinner')) {
                    const spinner = document.createElement('span');
                    spinner.className = 'spinner inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full ml-2';
                    spinner.style.animation = 'spin 1s linear infinite';
                    submitButton.appendChild(spinner);
                }
            }
        });
    });
    
    // Smooth validation messages
    const validationMessages = document.querySelectorAll('.error-message, .invalid-feedback, [class*="error"]');
    validationMessages.forEach((msg, index) => {
        msg.style.opacity = '0';
        msg.style.transform = 'translateX(-10px)';
        setTimeout(() => {
            msg.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            msg.style.opacity = '1';
            msg.style.transform = 'translateX(0)';
        }, index * 50);
    });
    
    // Password visibility toggle animation
    const passwordToggles = document.querySelectorAll('[data-password-toggle]');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = document.querySelector(this.dataset.passwordToggle);
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                
                // Animate icon
                this.style.transform = 'rotate(180deg)';
                this.style.transition = 'transform 0.3s ease';
                setTimeout(() => {
                    this.style.transform = 'rotate(0deg)';
                }, 300);
            }
        });
    });
    
    // Multi-step form animations
    const stepIndicators = document.querySelectorAll('[data-step]');
    const formSteps = document.querySelectorAll('[data-form-step]');
    
    function showStep(stepNumber) {
        formSteps.forEach((step, index) => {
            if (index + 1 === stepNumber) {
                step.style.display = 'block';
                step.style.animation = 'fadeInUp 0.4s ease-out';
            } else {
                step.style.display = 'none';
            }
        });
        
        // Update step indicators
        stepIndicators.forEach((indicator, index) => {
            if (index + 1 <= stepNumber) {
                indicator.classList.add('active');
                indicator.style.animation = 'pulse 0.6s ease-in-out';
            } else {
                indicator.classList.remove('active');
            }
        });
    }
    
    // Next/Previous buttons for multi-step forms
    document.querySelectorAll('[data-next-step]').forEach(btn => {
        btn.addEventListener('click', function() {
            const currentStep = parseInt(this.dataset.nextStep);
            showStep(currentStep);
        });
    });
    
    document.querySelectorAll('[data-prev-step]').forEach(btn => {
        btn.addEventListener('click', function() {
            const currentStep = parseInt(this.dataset.prevStep);
            showStep(currentStep);
        });
    });
});

// Real-time validation with smooth feedback
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let message = '';
    
    if (field.required && !value) {
        isValid = false;
        message = 'This field is required';
    } else if (type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        message = 'Please enter a valid email address';
    } else if (type === 'tel' && value && !isValidPhone(value)) {
        isValid = false;
        message = 'Please enter a valid phone number';
    }
    
    // Show validation feedback with animation
    if (!isValid) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        showValidationMessage(field, message, 'error');
    } else if (value) {
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
        hideValidationMessage(field);
    } else {
        field.classList.remove('is-valid', 'is-invalid');
        hideValidationMessage(field);
    }
}

function showValidationMessage(field, message, type) {
    let msgElement = field.parentElement.querySelector('.validation-message');
    
    if (!msgElement) {
        msgElement = document.createElement('div');
        msgElement.className = 'validation-message text-sm mt-1';
        field.parentElement.appendChild(msgElement);
    }
    
    msgElement.textContent = message;
    msgElement.className = `validation-message text-sm mt-1 ${type === 'error' ? 'text-red-600' : 'text-green-600'}`;
    
    // Animate in
    msgElement.style.opacity = '0';
    msgElement.style.transform = 'translateY(-5px)';
    requestAnimationFrame(() => {
        msgElement.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        msgElement.style.opacity = '1';
        msgElement.style.transform = 'translateY(0)';
    });
}

function hideValidationMessage(field) {
    const msgElement = field.parentElement.querySelector('.validation-message');
    if (msgElement) {
        msgElement.style.opacity = '0';
        msgElement.style.transform = 'translateY(-5px)';
        setTimeout(() => msgElement.remove(), 300);
    }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    return /^[\d\s\-\+\(\)]+$/.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

// Export for global use
window.formAnimations = {
    validateField,
    showValidationMessage,
    hideValidationMessage
};

console.log('✨ Form animations loaded');
