/**
 * ReceivableForm class handles all functionality for the accounts receivable form
 * including line item management, validation, and calculations.
 */
class ReceivableForm {
    // Constants for DOM selectors and configuration
    static SELECTORS = {
        FORM: '#receivableForm',
        LINE_ITEMS_TABLE: '#lineItemsTable',
        ADD_LINE_BTN: '.add-line',
        TOTAL_AMOUNT: '#totalAmount',
        REMARKS: '#remarks',
        CHAR_COUNT: '#charCount',
        REFERENCE: '#reference',
        PAYMENT_MODE: 'input[name="payment_mode"]',
        LINE_ITEM: '.line-item',
        AMOUNT_INPUT: '.amount-input',
        REMOVE_LINE: '.remove-line',
        SUBMIT_BUTTON: 'button[type="submit"]'
    };

    static CONFIG = {
        MAX_CHARS: 45,
    };

    /**
     * Initialize the ReceivableForm instance
     */
    constructor() {
        this.initializeElements();
        if (!this.validateInitialization()) {
            console.error('Failed to initialize ReceivableForm: Missing required elements');
            return;
        }
        
        this.setupEventListeners();
        this.initializeFormState();
        this.isSubmitting = false; // Flag to prevent multiple submissions
    }

    /**
     * Initialize DOM element references
     */
    initializeElements() {
        const s = ReceivableForm.SELECTORS;
        this.form = document.querySelector(s.FORM);
        this.lineItemsTable = document.querySelector(s.LINE_ITEMS_TABLE);
        this.tbody = this.lineItemsTable?.querySelector('tbody');
        this.addLineBtn = document.querySelector(s.ADD_LINE_BTN);
        this.totalAmountInput = document.querySelector(s.TOTAL_AMOUNT);
        this.remarksTextarea = document.querySelector(s.REMARKS);
        this.charCount = document.querySelector(s.CHAR_COUNT);
        this.referenceInput = document.querySelector(s.REFERENCE);
        this.submitButton = document.querySelector(s.SUBMIT_BUTTON);
    }

    /**
     * Validate that all required elements are present
     */
    validateInitialization() {
        return !!(this.form && 
                 this.lineItemsTable && 
                 this.tbody && 
                 this.addLineBtn && 
                 this.totalAmountInput);
    }

    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        // Form submission
        this.form.addEventListener('submit', this.handleFormSubmit.bind(this));

        // Line item management
        this.addLineBtn.addEventListener('click', this.handleAddLineItem.bind(this));
        this.tbody.addEventListener('click', this.handleRemoveLineItem.bind(this));
        
        // Amount calculations
        this.tbody.addEventListener('input', this.handleAmountInput.bind(this));
        
        // Payment mode handling
        const paymentModes = this.form.querySelectorAll(ReceivableForm.SELECTORS.PAYMENT_MODE);
        paymentModes.forEach(radio => {
            radio.addEventListener('change', this.handlePaymentModeChange.bind(this));
        });

        // Character count for remarks
        if (this.remarksTextarea) {
            this.remarksTextarea.addEventListener('input', this.updateCharCount.bind(this));
        }
    }

    /**
     * Initialize the initial form state
     */
    initializeFormState() {
        this.updateRemoveButtons();
        this.updateTotalAmount();
        this.updateCharCount();
    }

    /**
     * Handle adding a new line item
     */
    handleAddLineItem() {
        const template = this.tbody.querySelector(ReceivableForm.SELECTORS.LINE_ITEM);
        const newRow = template.cloneNode(true);
        const rowCount = this.tbody.querySelectorAll(ReceivableForm.SELECTORS.LINE_ITEM).length;
    
        // Update input names and clear values
        newRow.querySelectorAll('input, select').forEach(input => {
            const name = input.name;
            // Only update indices if the name has a pattern [number]
            if (name && name.match(/\[\d+\]/)) {
                input.name = name.replace(/\[\d+\]/, `[${rowCount}]`);
            }
            input.value = '';
            this.clearError(input);
        });
    
        this.tbody.appendChild(newRow);
        this.updateFormState();
    }

    /**
     * Handle removing a line item
     */
    handleRemoveLineItem(event) {
        const removeButton = event.target.closest(ReceivableForm.SELECTORS.REMOVE_LINE);
        if (!removeButton) return;
    
        const row = removeButton.closest(ReceivableForm.SELECTORS.LINE_ITEM);
        if (this.tbody.querySelectorAll(ReceivableForm.SELECTORS.LINE_ITEM).length > 1) {
            row.remove();
            this.reindexRows();
            this.updateFormState();
        }
    }

    /**
     * Handle amount input changes
     */
    handleAmountInput(event) {
        if (event.target.classList.contains('amount-input')) {
            this.updateTotalAmount();
        }
    }

    /**
     * Handle payment mode changes
     */
    handlePaymentModeChange(event) {
        const isReferenceRequired = ['GCASH', 'CHECK', 'BANK_TRANSFER'].includes(event.target.value);
        
        if (isReferenceRequired) {
            this.referenceInput.setAttribute('required', '');
        } else {
            this.referenceInput.removeAttribute('required');
            this.clearError(this.referenceInput);
        }
    }

    /**
     * Handle form submission
     */
    handleFormSubmit(event) {
        // Validate form first
        if (!this.validateForm()) {
            event.preventDefault();
            this.showValidationError();
            return;
        }

        // Prevent multiple submissions
        if (this.isSubmitting) {
            event.preventDefault();
            return;
        }

        // Set submitting flag and disable the submit button
        this.isSubmitting = true;
        if (this.submitButton) {
            this.submitButton.disabled = true;
            this.submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        }

        // Show processing message
        showToast('info', 'Processing your request...');

        // Allow form submission to continue
    }

    /**
     * Update the form state after changes
     */
    updateFormState() {
        this.updateRemoveButtons();
        this.updateTotalAmount();
    }

    /**
     * Reindex row input names after removal
     */
    reindexRows() {
        this.tbody.querySelectorAll(ReceivableForm.SELECTORS.LINE_ITEM).forEach((row, index) => {
            row.querySelectorAll('input, select').forEach(input => {
                const name = input.name;
                // Only update indices if the name has a pattern [number]
                if (name && name.match(/\[\d+\]/)) {
                    input.name = name.replace(/\[\d+\]/, `[${index}]`);
                }
            });
        });
    }

    /**
     * Update remove buttons visibility
     */
    updateRemoveButtons() {
        const removeButtons = this.tbody.querySelectorAll(ReceivableForm.SELECTORS.REMOVE_LINE);
        const showButtons = this.tbody.querySelectorAll(ReceivableForm.SELECTORS.LINE_ITEM).length > 1;
        removeButtons.forEach(btn => btn.style.display = showButtons ? 'block' : 'none');
    }

    /**
     * Update the total amount
     */
    updateTotalAmount() {
        const amounts = [...this.tbody.querySelectorAll(ReceivableForm.SELECTORS.AMOUNT_INPUT)]
            .map(input => this.parseAmount(input.value));
        const total = amounts.reduce((sum, amount) => sum + amount, 0);
        this.totalAmountInput.value = this.formatAmount(total);
    }

    /**
     * Update the character count for remarks
     */
    updateCharCount() {
        if (this.remarksTextarea && this.charCount) {
            const length = this.remarksTextarea.value.length;
            this.charCount.textContent = `${length}/${ReceivableForm.CONFIG.MAX_CHARS}`;
        }
    }

    /**
     * Parse amount string to number
     */
    parseAmount(value) {
        if (!value) return 0;
        const parsed = parseFloat(value.replace(/[^\d.-]/g, ''));
        return isNaN(parsed) ? 0 : parsed;
    }

    /**
     * Format amount number to string
     */
    formatAmount(amount) {
        return amount.toFixed(2);
    }

    /**
     * Validate the entire form
     */
    validateForm() {
        let isValid = true;

        // Validate required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                this.showError(field);
            } else {
                this.clearError(field);
            }
        });

        // Validate payment mode reference
        const paymentMode = this.form.querySelector(`${ReceivableForm.SELECTORS.PAYMENT_MODE}:checked`)?.value;
        if (['GCASH', 'CHECK', 'BANK_TRANSFER'].includes(paymentMode) && !this.referenceInput.value.trim()) {
            isValid = false;
            this.showError(this.referenceInput);
            this.addReferenceFeedback(paymentMode);
        }

        // Validate line items
        const hasValidLineItem = [...this.tbody.querySelectorAll(ReceivableForm.SELECTORS.LINE_ITEM)].some(row => {
            const inputs = row.querySelectorAll('input, select');
            return [...inputs].every(input => input.value.trim());
        });

        if (!hasValidLineItem) {
            isValid = false;
            this.showError(this.tbody.querySelector(ReceivableForm.SELECTORS.LINE_ITEM));
        }

        return isValid;
    }

    /**
     * Show validation error message
     */
    showValidationError() {
        const paymentMode = this.form.querySelector(`${ReceivableForm.SELECTORS.PAYMENT_MODE}:checked`)?.value;
        const errorMessage = ['GCASH', 'CHECK', 'BANK_TRANSFER'].includes(paymentMode)
            ? 'Please fill in all required fields including reference number'
            : 'Please fill in all required fields';

        // Using SweetAlert if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Validation Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        } else {
            // Fallback to browser alert
            alert(errorMessage);
        }
    }

    /**
     * Add reference feedback message
     */
    addReferenceFeedback(paymentMode) {
        if (!this.referenceInput.nextElementSibling?.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            feedback.textContent = `Reference number is required for ${paymentMode.toLowerCase()}`;
            this.referenceInput.parentNode.appendChild(feedback);
        }
    }

    /**
     * Show error state for an element
     */
    showError(element) {
        element.classList.add('is-invalid');
        if (element.tagName === 'SELECT') {
            element.parentElement.classList.add('has-error');
        }
    }

    /**
     * Clear error state for an element
     */
    clearError(element) {
        element.classList.remove('is-invalid');
        if (element.tagName === 'SELECT') {
            element.parentElement.classList.remove('has-error');
        }
        
        // Remove any feedback message
        const feedback = element.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
}

// Initialize the form when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ReceivableForm();
    
    // Add custom filter function for standard select elements
    document.querySelectorAll('.form-select').forEach(select => {
        // Add event listener for input in the select field
        select.addEventListener('keydown', function(e) {
            // Only apply for non-control keys
            if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                const searchText = e.key === 'Backspace' || e.key === 'Delete' ? '' : e.key.toLowerCase();
                
                // Get all options
                const options = Array.from(select.options);
                
                // Find first option that starts with the pressed key
                const matchingOption = options.find(option => 
                    option.text.toLowerCase().startsWith(searchText));
                
                // Select the matching option if found
                if (matchingOption) {
                    select.value = matchingOption.value;
                }
            }
        });
    });
});

/**
 * Display a toast notification
 * @param {string} type - The type of toast (success, error, info)
 * @param {string} message - The message to display
 */
function showToast(type, message) {
    const toastElement = document.getElementById(type + 'Toast');
    const messageElement = document.getElementById(type + 'Message');
    
    if (toastElement && messageElement) {
        messageElement.textContent = message;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toastElement, {
                animation: true,
                autohide: true,
                delay: 4000
            });
            
            bsToast.show();
        } else {
            // Fallback for when Bootstrap isn't loaded
            toastElement.style.display = 'block';
            setTimeout(() => {
                toastElement.style.display = 'none';
            }, 4000);
        }
    } else {
        // Fallback to alert if toast elements aren't found
        alert(message);
    }
}

// Make showToast function available globally
window.showToast = showToast;