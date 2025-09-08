// form-reset.js
// This script handles clearing form fields when switching between tabs
// with confirmation dialog to prevent accidental data loss

document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for tab changes
    setupTabChangeListeners();
});

function setupTabChangeListeners() {
    // Get references to the tab buttons
    const arrearsTab = document.getElementById('arrears-tab');
    const accountTab = document.getElementById('account-tab');
    
    if (arrearsTab && accountTab) {
        // Listen for when the user tries to switch to the "HOA Monthly Dues" tab
        arrearsTab.addEventListener('show.bs.tab', function(event) {
            // If we're switching from Account tab and it has data, show confirmation
            if (document.querySelector('.tab-pane.active').id === 'account' && hasFormData('accountReceivableForm')) {
                if (!confirm("You have unsaved data in the Account Receivable form. Switching tabs will clear this data. Continue?")) {
                    // Prevent the tab switch if user cancels
                    event.preventDefault();
                    return;
                }
            }
            
            // If confirmed or no data to clear, proceed with clearing the other form
            clearAccountFormFields();
        });
        
        // Listen for when the user tries to switch to the "Account Receivable" tab
        accountTab.addEventListener('show.bs.tab', function(event) {
            // If we're switching from Arrears tab and it has data, show confirmation
            if (document.querySelector('.tab-pane.active').id === 'arrears' && hasFormData('arrearsReceivableForm')) {
                if (!confirm("You have unsaved data in the HOA Monthly Dues form. Switching tabs will clear this data. Continue?")) {
                    // Prevent the tab switch if user cancels
                    event.preventDefault();
                    return;
                }
            }
            
            // If confirmed or no data to clear, proceed with clearing the other form
            clearArrearsFormFields();
        });
    }
}

/**
 * Check if the form has any user-entered data
 * @param {string} formId - The ID of the form to check
 * @returns {boolean} - True if the form has data, false otherwise
 */
function hasFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    // Fields to exclude for both forms
    const commonExcludedFields = [
        'date', 
        'receivedBy', 
        'arrears_date', 
        'arrears_receivedBy',
        'arrears_serviceInvoiceNo',
        'serviceInvoiceNo'
    ];
    
    // Form-specific excluded fields
    const arrearsExcludedFields = [
        'arrears_addressId'
    ];
    
    const accountExcludedFields = [
        'totalAmount'  // Total is calculated automatically
    ];
    
    // Get all input elements that are not in the excluded lists
    const textInputs = form.querySelectorAll('input[type="text"], input[type="number"]');
    for (const input of textInputs) {
        // Skip checking hidden fields and excluded fields
        if (input.type === 'hidden' || 
            commonExcludedFields.includes(input.id) || 
            (formId === 'arrearsReceivableForm' && arrearsExcludedFields.includes(input.id)) ||
            (formId === 'accountReceivableForm' && accountExcludedFields.includes(input.id))) {
            continue;
        }
        
        // Skip reference fields if they're hidden (payment mode is CASH)
        if ((input.id === 'reference' || input.id === 'arrears_reference') && 
            (input.closest('.col-md-4')?.style.display === 'none')) {
            continue;
        }
        
        // If the input has a value, form has data
        if (input.value && input.value.trim() !== '') {
            console.log('Form has data in field:', input.id);
            return true;
        }
    }
    
    // Check textarea fields (remarks)
    const textareas = form.querySelectorAll('textarea');
    for (const textarea of textareas) {
        if (textarea.value && textarea.value.trim() !== '') {
            console.log('Form has data in textarea:', textarea.id);
            return true;
        }
    }
    
    // Check select fields - but we'll handle them differently based on the form
    const selects = form.querySelectorAll('select');
    for (const select of selects) {
        // For arrears form, we should only consider a selection if it's different from "Association Dues"
        if (formId === 'arrearsReceivableForm') {
            // Check if the selected option is something other than Association Dues
            let isDefaultSelection = false;
            if (select.selectedIndex >= 0 && select.options[select.selectedIndex]) {
                isDefaultSelection = select.options[select.selectedIndex].text.includes("Association Dues");
            }
            
            if (!isDefaultSelection && select.selectedIndex > 0) {
                console.log('Form has non-default selection in select:', select.name);
                return true;
            }
        } else {
            // For account form, any selected option other than the first is considered data
            if (select.selectedIndex > 0) {
                console.log('Form has selection in select:', select.name);
                return true;
            }
        }
    }
    
    // Special check for amount fields
    const amountInputClass = formId === 'arrearsReceivableForm' ? '.arrears-amount-input' : '.amount-input';
    const amountInputs = form.querySelectorAll(amountInputClass);
    for (const input of amountInputs) {
        if (input.value && input.value.trim() !== '') {
            console.log('Form has data in amount field:', input.name);
            return true;
        }
    }
    
    // Double-check that there's more than one line item for account form
    // If there's only one empty line item, it's the default state
    if (formId === 'accountReceivableForm') {
        const lineItems = form.querySelectorAll('#lineItemsTable tbody tr.line-item');
        if (lineItems.length > 1) {
            // Check if additional rows contain any data
            for (let i = 1; i < lineItems.length; i++) {
                const row = lineItems[i];
                const select = row.querySelector('select');
                const amountInput = row.querySelector('.amount-input');
                
                // If either field has data, count it as user input
                if ((select && select.selectedIndex > 0) || 
                    (amountInput && amountInput.value && amountInput.value.trim() !== '')) {
                    console.log('Form has extra line items with data');
                    return true;
                }
            }
        }
    }
    
    // If we got here, no meaningful data found
    console.log('No meaningful data found in form:', formId);
    return false;
}

function clearArrearsFormFields() {
    // Get the form
    const form = document.getElementById('arrearsReceivableForm');
    if (!form) return;
    
    // Clear input fields
    const textInputs = form.querySelectorAll('input[type="text"], input[type="number"]');
    textInputs.forEach(input => {
        // Don't clear hidden fields or the arrears_received_by field which keeps the current user
        if (input.type !== 'hidden' && input.id !== 'arrears_receivedBy' && input.id !== 'arrears_serviceInvoiceNo') {
            input.value = '';
        }
    });
    
    // Clear textarea fields
    const textareas = form.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.value = '';
        
        // Update character count if applicable
        const charCountId = textarea.id === 'arrears_remarks' ? 'arrearsCharCount' : null;
        if (charCountId) {
            const charCount = document.getElementById(charCountId);
            if (charCount) {
                charCount.textContent = '0/45';
            }
        }
    });
    
    // Reset select fields to default option
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        if (select.options.length > 0) {
            // If we have an "Association Dues" option, select that by default
            let defaultIndex = 0;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].text.includes("Association Dues")) {
                    defaultIndex = i;
                    break;
                }
            }
            select.selectedIndex = defaultIndex;
        }
    });
    
    // Reset amount field
    const amountInputs = form.querySelectorAll('.arrears-amount-input');
    amountInputs.forEach(input => {
        input.value = '';
    });
    
    // Clear member data fields
    const memberFields = ['memberName', 'memberAddress', 'total_arrears', 'lastPaydate', 'lastPayment', 'lastOR', 'arrears_amount'];
    memberFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = '';
        }
    });
    
    // Reset to CASH payment option
    const cashRadio = document.getElementById('arrears_cash');
    if (cashRadio && !cashRadio.checked) {
        cashRadio.checked = true;
        cashRadio.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Hide reference field
    const referenceContainer = document.getElementById('arrears_reference')?.closest('.col-md-4');
    if (referenceContainer) {
        referenceContainer.style.display = 'none';
    }
    
    // Clear reference field
    const referenceField = document.getElementById('arrears_reference');
    if (referenceField) {
        referenceField.value = '';
    }
    
    // Disable the payment history button
    const viewPaymentHistoryBtn = document.getElementById('viewPaymentHistory');
    if (viewPaymentHistoryBtn) {
        viewPaymentHistoryBtn.disabled = true;
    }
    
    // Clear lookup status
    const lookupStatusElem = document.getElementById('lookupStatus');
    if (lookupStatusElem) {
        lookupStatusElem.classList.add('d-none');
        lookupStatusElem.innerHTML = '';
    }
    
    // Set today's date in the date field
    // const dateField = document.getElementById('arrears_date');
    // if (dateField) {
    //     // Get current date in YYYY-MM-DD format
    //     const now = new Date();
    //     const year = now.getFullYear();
    //     const month = String(now.getMonth() + 1).padStart(2, '0');
    //     const day = String(now.getDate()).padStart(2, '0');
    //     dateField.value = `${year}-${month}-${day}`;
    // }
    
    // Focus on the address ID field
    const addressIdField = document.getElementById('arrears_addressId');
    if (addressIdField) {
        setTimeout(() => {
            addressIdField.focus();
        }, 100);
    }
    
    // Reset any reversal mode
    if (typeof resetReversalMode === 'function') {
        resetReversalMode();
    }
}

function clearAccountFormFields() {
    // Get the form
    const form = document.getElementById('accountReceivableForm');
    if (!form) return;
    
    // Clear input fields
    const textInputs = form.querySelectorAll('input[type="text"], input[type="number"]');
    textInputs.forEach(input => {
        // Don't clear hidden fields or the receivedBy field which keeps the current user
        if (input.type !== 'hidden' && input.id !== 'receivedBy' && input.id !== 'serviceInvoiceNo') {
            input.value = '';
        }
    });
    
    // Clear textarea fields
    const textareas = form.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.value = '';
        
        // Update character count if applicable
        const charCountId = textarea.id === 'remarks' ? 'charCount' : null;
        if (charCountId) {
            const charCount = document.getElementById(charCountId);
            if (charCount) {
                charCount.textContent = '0/45';
            }
        }
    });
    
    // Reset select fields to default option
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        if (select.options.length > 0) {
            select.selectedIndex = 0;
        }
    });
    
    // Keep only one line item row and clear its values
    const tbody = document.querySelector('#lineItemsTable tbody');
    if (tbody) {
        // Remove all rows except the first one
        while (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
        
        // Clear the first row's values
        const firstRow = tbody.children[0];
        if (firstRow) {
            const select = firstRow.querySelector('select');
            const amountInput = firstRow.querySelector('.amount-input');
            
            if (select) select.selectedIndex = 0;
            if (amountInput) amountInput.value = '';
        }
        
        // Reset the total amount
        const totalAmount = document.getElementById('totalAmount');
        if (totalAmount) {
            totalAmount.value = '0.00';
        }
    }
    
    // Reset to CASH payment option
    const cashRadio = document.getElementById('cash');
    if (cashRadio && !cashRadio.checked) {
        cashRadio.checked = true;
        cashRadio.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Hide reference field
    const referenceContainer = document.getElementById('reference')?.closest('.col-md-4');
    if (referenceContainer) {
        referenceContainer.style.display = 'none';
    }
    
    // Clear reference field
    const referenceField = document.getElementById('reference');
    if (referenceField) {
        referenceField.value = '';
    }
    
    // Set today's date in the date field
    // const dateField = document.getElementById('date');
    // if (dateField) {
    //     // Get current date in YYYY-MM-DD format
    //     const now = new Date();
    //     const year = now.getFullYear();
    //     const month = String(now.getMonth() + 1).padStart(2, '0');
    //     const day = String(now.getDate()).padStart(2, '0');
    //     dateField.value = `${year}-${month}-${day}`;
    // }
    
    // Focus on the service invoice number field
    const serviceInvoiceField = document.getElementById('serviceInvoiceNo');
    if (serviceInvoiceField) {
        setTimeout(() => {
            serviceInvoiceField.focus();
        }, 100);
    }
    
    // Reset any reversal mode
    if (typeof resetReversalMode === 'function') {
        resetReversalMode();
    }
}