export class FormValidator {
    constructor(formId, config = {}) {
        this.form = document.getElementById(formId);
        this.submitBtn = this.form?.querySelector(config.submitBtnSelector || '[type="submit"]');
        this.validators = new Map();
        this.isSubmitting = false;
        this.config = {
            submitTimeout: config.submitTimeout || 30000,
            loadingText: config.loadingText || 'Submitting...',
            ...config
        };
    }

    /**
     * Add field validator with rules
     */
    addField(fieldId, rules = {}) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const validator = {
            field,
            rules,
            errorElement: rules.errorElement ? document.getElementById(rules.errorElement) : null,
            isValid: false
        };

        this.validators.set(fieldId, validator);

        // Auto-attach event listeners
        const events = rules.events || ['input', 'blur'];
        events.forEach(event => {
            field.addEventListener(event, () => this.validateField(fieldId));
        });

        return this;
    }

    /**
     * Validate single field
     */
    async validateField(fieldId) {
        const validator = this.validators.get(fieldId);
        if (!validator) return false;

        const { field, rules, errorElement } = validator;
        const value = field.value;

        try {
            let isValid = true;
            let errorMsg = '';

            // Required check
            if (rules.required && !value.trim()) {
                isValid = false;
                errorMsg = rules.requiredMessage || 'This field is required';
            }

            // Min length
            if (isValid && rules.minLength && value.length < rules.minLength) {
                isValid = false;
                errorMsg = rules.minLengthMessage || `Minimum ${rules.minLength} characters`;
            }

            // Min value (for numbers)
            if (isValid && rules.min !== undefined) {
                const numValue = parseFloat(value);
                if (numValue < rules.min) {
                    isValid = false;
                    errorMsg = rules.minMessage || `Minimum value is ${rules.min}`;
                }
            }

            // Max value (for numbers)
            if (isValid && rules.max !== undefined) {
                const numValue = parseFloat(value);
                if (numValue > rules.max) {
                    isValid = false;
                    errorMsg = rules.maxMessage || `Maximum value is ${rules.max}`;
                }
            }

            // Custom validator function
            if (isValid && rules.validator) {
                const result = await rules.validator(value, field);
                if (typeof result === 'boolean') {
                    isValid = result;
                } else if (result && typeof result === 'object') {
                    isValid = result.isValid;
                    errorMsg = result.message || errorMsg;
                }
            }

            // Update validator state
            validator.isValid = isValid;

            // Show/hide error
            if (errorElement) {
                if (isValid) {
                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                } else {
                    errorElement.textContent = errorMsg;
                    errorElement.classList.remove('hidden');
                }
            }

            // Trigger callback
            if (rules.onChange) {
                rules.onChange(isValid, value);
            }

        } catch (error) {
            validator.isValid = false;
            if (errorElement) {
                errorElement.textContent = error.message;
                errorElement.classList.remove('hidden');
            }
        }

        this.updateSubmitButton();
        return validator.isValid;
    }

    /**
     * Add external validator (e.g., EmployeeValidator)
     */
    addExternalValidator(name, validatorInstance) {
        this.validators.set(name, {
            isValid: false,
            external: true,
            instance: validatorInstance
        });

        // Listen to validation changes
        if (validatorInstance.onValidationChange) {
            const originalCallback = validatorInstance.onValidationChange;
            validatorInstance.onValidationChange = (isValid) => {
                this.validators.get(name).isValid = isValid;
                this.updateSubmitButton();
                if (originalCallback) originalCallback(isValid);
            };
        }

        return this;
    }

    /**
     * Check if form is valid
     */
    isFormValid() {
        return Array.from(this.validators.values()).every(v => v.isValid);
    }

    /**
     * Update submit button state
     */
    updateSubmitButton() {
        if (this.submitBtn) {
            this.submitBtn.disabled = !this.isFormValid() || this.isSubmitting;
        }
    }

    /**
     * Prevent double submit
     */
    preventDoubleSubmit() {
        if (!this.form) return;

        this.form.addEventListener('submit', (e) => {
            if (!this.isFormValid() || this.isSubmitting) {
                e.preventDefault();
                return;
            }

            this.isSubmitting = true;
            if (this.submitBtn) {
                this.submitBtn.disabled = true;
                this.submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${this.config.loadingText}
                `;
            }

            // Auto-recover timeout
            setTimeout(() => {
                if (this.isSubmitting) {
                    this.isSubmitting = false;
                    this.updateSubmitButton();
                    
                    if (this.submitBtn && this.config.originalSubmitHtml) {
                        this.submitBtn.innerHTML = this.config.originalSubmitHtml;
                    }
                    
                    alert('Submission taking too long. Please try again.');
                }
            }, this.config.submitTimeout);
        });
    }

    /**
     * Initialize form validation
     */
    init() {
        // Store original submit button HTML
        if (this.submitBtn) {
            this.config.originalSubmitHtml = this.submitBtn.innerHTML;
        }

        // Validate all fields on page load
        this.validators.forEach((validator, fieldId) => {
            if (!validator.external && validator.field?.value) {
                this.validateField(fieldId);
            }
        });

        this.preventDoubleSubmit();
        this.updateSubmitButton();

        console.log(`FormValidator initialized for #${this.form?.id}`);
    }
}