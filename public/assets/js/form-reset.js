// form-reset.js
// This script handles clearing form fields when switching between tabs

document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for tab changes
    setupTabChangeListeners();
});

function setupTabChangeListeners() {
    // Get references to the tab buttons
    const arrearsTab = document.getElementById('arrears-tab');
    const accountTab = document.getElementById('account-tab');
    
    if (arrearsTab && accountTab) {
        // Listen for when the "HOA Monthly Dues" tab is shown
        arrearsTab.addEventListener('shown.bs.tab', function() {
            // Clear the Account Receivable tab fields
            clearAccountFormFields();
        });
        
        // Listen for when the "Account Receivable" tab is shown
        accountTab.addEventListener('shown.bs.tab', function() {
            // Clear the HOA Monthly Dues tab fields
            clearArrearsFormFields();
        });
    }
}

function clearArrearsFormFields() {
    // Get the form
    const form = document.getElementById('arrearsReceivableForm');
    if (!form) return;
    
    // Clear input fields
    const textInputs = form.querySelectorAll('input[type="text"], input[type="number"]');
    textInputs.forEach(input => {
        // Don't clear hidden fields or the arrears_received_by field which keeps the current user
        if (input.type !== 'hidden' && input.id !== 'arrears_receivedBy') {
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
    const dateField = document.getElementById('arrears_date');
    if (dateField) {
        // Get current date in YYYY-MM-DD format
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        dateField.value = `${year}-${month}-${day}`;
    }
    
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
        if (input.type !== 'hidden' && input.id !== 'receivedBy') {
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
    const dateField = document.getElementById('date');
    if (dateField) {
        // Get current date in YYYY-MM-DD format
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        dateField.value = `${year}-${month}-${day}`;
    }
    
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