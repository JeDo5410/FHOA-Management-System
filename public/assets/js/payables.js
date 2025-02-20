/**
 * PayableForm class handles all functionality for the accounts payable form
 * including line item management, validation, and calculations.
 */
class PayableForm {
    // Constants for DOM selectors and configuration
    static SELECTORS = {
        FORM: '#payableForm',
        LINE_ITEMS_TABLE: '#lineItemsTable',
        ADD_LINE_BTN: '.add-line',
        TOTAL_AMOUNT: '#totalAmount',
        REMARKS: '#remarks',
        CHAR_COUNT: '#charCount',
        REFERENCE: '#reference',
        PAYMENT_MODE: 'input[name="payment_mode"]',
        LINE_ITEM: '.line-item',
        AMOUNT_INPUT: '.amount-input',
        REMOVE_LINE: '.remove-line'
    };

    static CONFIG = {
        MAX_CHARS: 45,
        SELECT2_OPTIONS: {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search account type...',
            allowClear: true
        }
    };

    /**
     * Initialize the PayableForm instance
     */
    constructor() {
        this.initializeElements();
        if (!this.validateInitialization()) {
            console.error('Failed to initialize PayableForm: Missing required elements');
            return;
        }
        
        this.setupEventListeners();
        this.initializeFormState();
    }

    /**
     * Initialize DOM element references
     */
    initializeElements() {
        const s = PayableForm.SELECTORS;
        this.form = document.querySelector(s.FORM);
        this.lineItemsTable = document.querySelector(s.LINE_ITEMS_TABLE);
        this.tbody = this.lineItemsTable?.querySelector('tbody');
        this.addLineBtn = document.querySelector(s.ADD_LINE_BTN);
        this.totalAmountInput = document.querySelector(s.TOTAL_AMOUNT);
        this.remarksTextarea = document.querySelector(s.REMARKS);
        this.charCount = document.querySelector(s.CHAR_COUNT);
        this.referenceInput = document.querySelector(s.REFERENCE);
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
        const paymentModes = this.form.querySelectorAll(PayableForm.SELECTORS.PAYMENT_MODE);
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
        this.initializeSelect2();
        this.updateRemoveButtons();
        this.updateTotalAmount();
        this.updateCharCount();
    }

    /**
     * Initialize Select2 for the first row
     */
    initializeSelect2() {
        const firstSelect = this.tbody.querySelector(`${PayableForm.SELECTORS.LINE_ITEM} select`);
        if (firstSelect) {
            this.initializeRowSelect(firstSelect);
        }
    }

    /**
     * Initialize Select2 for a specific select element
     */
    initializeRowSelect(selectElement) {
        $(selectElement).select2(PayableForm.CONFIG.SELECT2_OPTIONS);
    }

    /**
     * Handle adding a new line item
     */
    handleAddLineItem() {
        const template = this.tbody.querySelector(PayableForm.SELECTORS.LINE_ITEM);
        const newRow = template.cloneNode(true);
        const rowCount = this.tbody.querySelectorAll(PayableForm.SELECTORS.LINE_ITEM).length;
    
        // Update input names and clear values
        newRow.querySelectorAll('input, select').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${rowCount}]`);
            input.value = '';
            this.clearError(input);
        });
    
        this.tbody.appendChild(newRow);
        this.initializeRowSelect(newRow.querySelector('select')); // Initialize Select2 here
        this.updateFormState();
    }

    /**
     * Handle removing a line item
     */
    handleRemoveLineItem(event) {
        const removeButton = event.target.closest(PayableForm.SELECTORS.REMOVE_LINE);
        if (!removeButton) return;
    
        const row = removeButton.closest(PayableForm.SELECTORS.LINE_ITEM);
        if (this.tbody.querySelectorAll(PayableForm.SELECTORS.LINE_ITEM).length > 1) {
            const select = row.querySelector('select');
            
            // Check if jQuery is loaded
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(select).select2('destroy');
            }
            
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
        const isReferenceRequired = ['GCASH', 'CHECK'].includes(event.target.value);
        
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
        if (!this.validateForm()) {
            event.preventDefault();
            this.showValidationError();
        }
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
        this.tbody.querySelectorAll(PayableForm.SELECTORS.LINE_ITEM).forEach((row, index) => {
            row.querySelectorAll('input, select').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            });
        });
    }

    /**
     * Update remove buttons visibility
     */
    updateRemoveButtons() {
        const removeButtons = this.tbody.querySelectorAll(PayableForm.SELECTORS.REMOVE_LINE);
        const showButtons = this.tbody.querySelectorAll(PayableForm.SELECTORS.LINE_ITEM).length > 1;
        removeButtons.forEach(btn => btn.style.display = showButtons ? 'block' : 'none');
    }

    /**
     * Update the total amount
     */
    updateTotalAmount() {
        const amounts = [...this.tbody.querySelectorAll(PayableForm.SELECTORS.AMOUNT_INPUT)]
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
            this.charCount.textContent = `${length}/${PayableForm.CONFIG.MAX_CHARS}`;
        }
    }

    /**
     * Parse amount string to number
     */
    parseAmount(value) {
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
        const paymentMode = this.form.querySelector(`${PayableForm.SELECTORS.PAYMENT_MODE}:checked`)?.value;
        if (['GCASH', 'CHECK'].includes(paymentMode) && !this.referenceInput.value.trim()) {
            isValid = false;
            this.showError(this.referenceInput);
            this.addReferenceFeedback(paymentMode);
        }

        // Validate line items
        const hasValidLineItem = [...this.tbody.querySelectorAll(PayableForm.SELECTORS.LINE_ITEM)].some(row => {
            const inputs = row.querySelectorAll('input, select');
            return [...inputs].every(input => input.value.trim());
        });

        if (!hasValidLineItem) {
            isValid = false;
            this.showError(this.tbody.querySelector(PayableForm.SELECTORS.LINE_ITEM));
        }

        return isValid;
    }

    /**
     * Show validation error message
     */
    showValidationError() {
        const paymentMode = this.form.querySelector(`${PayableForm.SELECTORS.PAYMENT_MODE}:checked`)?.value;
        const errorMessage = ['GCASH', 'CHECK'].includes(paymentMode)
            ? 'Please fill in all required fields including reference number'
            : 'Please fill in all required fields';

        Swal.fire({
            title: 'Validation Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: 'Ok'
        });
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
    }
}

// Initialize the form when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new PayableForm();
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
        
        const bsToast = new bootstrap.Toast(toastElement, {
            animation: true,
            autohide: true,
            delay: 4000
        });
        
        bsToast.show();
    }
}

// Make showToast function available globally
window.showToast = showToast;