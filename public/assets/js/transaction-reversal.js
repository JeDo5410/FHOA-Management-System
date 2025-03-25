// Transaction reversal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Setup invoice lookup for both tabs
    setupInvoiceLookup();
});

function setupInvoiceLookup() {
    // Get references to the service invoice number fields in both tabs
    const arrearsInvoiceField = document.getElementById('arrears_serviceInvoiceNo');
    const accountInvoiceField = document.getElementById('serviceInvoiceNo');
    
    // Setup blur event for arrears tab
    if (arrearsInvoiceField) {
        arrearsInvoiceField.addEventListener('blur', function() {
            const invoiceNumber = this.value.trim();
            if (invoiceNumber) {
                lookupTransaction(invoiceNumber, 'arrears');
            }
        });
        
        // Add Enter key press event for arrears tab
        arrearsInvoiceField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                const invoiceNumber = this.value.trim();
                if (invoiceNumber) {
                    lookupTransaction(invoiceNumber, 'arrears');
                }
            }
        });
    }
    
    // Setup blur event for account tab
    if (accountInvoiceField) {
        accountInvoiceField.addEventListener('blur', function() {
            const invoiceNumber = this.value.trim();
            if (invoiceNumber) {
                lookupTransaction(invoiceNumber, 'account');
            }
        });
        
        // Add Enter key press event for account tab as well for consistency
        accountInvoiceField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                const invoiceNumber = this.value.trim();
                if (invoiceNumber) {
                    lookupTransaction(invoiceNumber, 'account');
                }
            }
        });
    }
}

function lookupTransaction(invoiceNumber, formType) {
    // Show loading indicator
    showToast('info', `Checking if invoice #${invoiceNumber} exists...`);
    
    // Make AJAX request to check if invoice exists
    fetch(`/accounts/receivables/check-invoice/${invoiceNumber}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.exists) {
            // Check if the transaction type matches the current tab
            const transactionTabType = data.tab_type; // 'arrears' or 'account'
            
            if (transactionTabType !== formType) {
                // Wrong tab for this transaction
                showToast('error', `This invoice (#${invoiceNumber}) belongs to the ${data.tab_type === 'arrears' ? 'HOA Monthly Dues' : 'Account Receivable'} tab. Please switch tabs to reverse it.`);
                return;
            }
            
            // Invoice exists and is in the correct tab, fetch details and setup reversal
            showToast('info', 'Transaction found. Setting up reversal...');
            setupReversal(data.transaction, formType, data.is_arrears, data.line_items);
        } else {
            // Invoice doesn't exist, proceed as normal new transaction
            console.log('Invoice not found, proceeding as new transaction');
        }
    })
    .catch(error => {
        console.error('Error checking invoice:', error);
        showToast('error', 'Error checking invoice: ' + error.message);
    });
}

function setupReversal(transaction, formType, isArrears, lineItems) {
    if (formType === 'arrears') {
        setupArrearsReversal(transaction);
    } else {
        setupAccountReversal(transaction, lineItems);
    }
    
    // Change save button to reverse transaction button
    changeToReversalMode();
}

// Function to fetch the address ID using member ID
async function fetchAddressIdByMemberId(memberId) {
    try {
        // Make a request to get the member details which includes the address ID
        const response = await fetch(`/residents/get-member-details/${memberId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch member details');
        }

        const data = await response.json();
        
        // Extract the address ID from the member data
        if (data && data.memberSum && data.memberSum.mem_add_id) {
            return data.memberSum.mem_add_id;
        } else {
            console.error('No address ID found in member data', data);
            return null;
        }
    } catch (error) {
        console.error('Error fetching address ID by member ID:', error);
        throw error;
    }
}

function setupArrearsReversal(transaction) {
    // Store original values for restoration after member lookup
    const originalPayorName = transaction.payor_name;
    const originalAddress = transaction.payor_address;
    const memberId = transaction.mem_id;
    
    // Populate form fields with transaction data
    document.getElementById('arrears_receivedFrom').value = originalPayorName;
    const originalDate = new Date(transaction.ar_date);
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    document.getElementById('arrears_date').value = originalDate.toLocaleDateString('en-CA', options);
        
    // Set the address ID - we'll fetch the actual address ID using the mem_id
    const addressIdField = document.getElementById('arrears_addressId');
    if (addressIdField) {
        // Temporarily set the field to show we're working on it
        addressIdField.value = "Loading...";
        
        // Add a flag to indicate we're in reversal mode
        window.isReversalMode = true;
        
        // First, fetch the correct address ID using the mem_id
        fetchAddressIdByMemberId(memberId)
            .then(addressId => {
                if (addressId) {
                    console.log('Found address ID:', addressId, 'for member ID:', memberId);
                    
                    // Set the address ID field with the correct value
                    addressIdField.value = addressId;
                    
                    // IMPROVED APPROACH: Directly call the lookup function rather than relying on events
                    if (window.arrearsAddressLookup && typeof window.arrearsAddressLookup.selectAddressById === 'function') {
                        // Call the lookup function directly if available
                        window.arrearsAddressLookup.selectAddressById(addressId, memberId);
                    } else {
                        // Fallback: Try to trigger the blur event
                        const event = new Event('blur', { bubbles: true });
                        addressIdField.dispatchEvent(event);
                        
                        // Additional fallback: Try with input event
                        const inputEvent = new Event('input', { bubbles: true });
                        addressIdField.dispatchEvent(inputEvent);
                    }
                    
                    // Restore the original payor name after a delay
                    setTimeout(() => {
                        document.getElementById('arrears_receivedFrom').value = originalPayorName;
                    }, 800);
                } else {
                    // If we couldn't find the address ID, fallback to original address
                    addressIdField.value = originalAddress;
                    showToast('warning', 'Could not find exact address ID for this member. Using original address.');
                }
            })
            .catch(error => {
                console.error('Error fetching address ID:', error);
                addressIdField.value = originalAddress;
                showToast('error', 'Error loading address ID: ' + error.message);
            });
    }

    
    // Set payment mode
    const paymentModeRadio = document.querySelector(`input[name="arrears_payment_mode"][value="${transaction.payment_type}"]`);
    if (paymentModeRadio) {
        paymentModeRadio.checked = true;
        // Trigger change event to update UI for reference field
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.payment_Ref) {
        document.getElementById('arrears_reference').value = transaction.payment_Ref;
    }
    
    // Set line item (for arrears there's typically just one)
    // First find the account type dropdown and set its value
    const accountTypeSelect = document.querySelector('select[name="arrears_items[0][coa]"]');
    if (accountTypeSelect) {
        accountTypeSelect.value = transaction.acct_type_id;
    }
    
    // Set amount (negative for reversal)
    const amountInput = document.querySelector('.arrears-amount-input');
    if (amountInput) {
        // Make amount negative for reversal
        amountInput.value = -Math.abs(parseFloat(transaction.ar_amount));
    }
    
    // Set remarks - prepend "CANCELLED OR: "
    const remarksField = document.getElementById('arrears_remarks');
    if (remarksField) {
        remarksField.value = `CANCELLED OR: ${transaction.or_number}`;
        
        // Append original remarks if they exist
        if (transaction.ar_remarks) {
            remarksField.value += ` - ${transaction.ar_remarks}`;
        }
    }
    
    // Disable fields that shouldn't be changed during reversal
    disableArrearsFormFields();
}

function prepareFormForSubmission(form) {
    // Find all disabled fields that need to be temporarily enabled
    const disabledFields = form.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
    
    // Store original disabled state and enable fields
    disabledFields.forEach(field => {
        field.dataset.wasDisabled = 'true';
        field.disabled = false;
    });
    
    // Return a function to restore the state if needed
    return function() {
        disabledFields.forEach(field => {
            if (field.dataset.wasDisabled === 'true') {
                field.disabled = true;
                delete field.dataset.wasDisabled;
            }
        });
    };
}

function setupAccountReversal(transaction, lineItems) {
    // Populate the main form fields
    document.getElementById('address').value = transaction.payor_address;
    document.getElementById('receivedFrom').value = transaction.payor_name;
    document.getElementById('date').value = transaction.ar_date.split('T')[0]; // Format date
    
    // Set payment mode
    const paymentModeRadio = document.querySelector(`input[name="payment_mode"][value="${transaction.payment_type}"]`);
    if (paymentModeRadio) {
        paymentModeRadio.checked = true;
        // Trigger change event to update UI for reference field
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.payment_Ref) {
        document.getElementById('reference').value = transaction.payment_Ref;
    }
    
    // Handle line items from separate lineItems parameter
    if (lineItems && lineItems.length > 0) {
        console.log('Found line items:', lineItems.length);
        
        // Get the tbody reference
        const tbody = document.querySelector('#lineItemsTable tbody');
        if (!tbody) {
            console.error('Could not find table body');
            return;
        }
        
        // Clear existing line items - leave only the first row
        while (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
        
        // Create all required rows first
        for (let i = 1; i < lineItems.length; i++) {
            // Create a new row manually instead of relying on the click event
            const newRow = document.createElement('tr');
            newRow.className = 'line-item';
            
            newRow.innerHTML = `
                <td>
                    <select class="form-select form-select-sm enhanced" name="items[${i}][coa]" required>
                        <option value="">Select Account Type</option>
                        ${createAccountTypeOptions(lineItems[i].acct_type_id, lineItems[i].acct_description)}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm amount-input" name="items[${i}][amount]" step="0.01" min="0.01" required>
                </td>
                <td>
                    <button type="button" class="btn btn-link text-danger remove-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
        }
        
        // Now that all rows exist, populate them
        lineItems.forEach((item, index) => {
            // Make sure the row exists
            if (index < tbody.children.length) {
                const row = tbody.children[index];
                const select = row.querySelector('select[name^="items["]');
                const amountInput = row.querySelector('.amount-input');
                
                if (select) select.value = item.acct_type_id;
                if (amountInput) amountInput.value = -Math.abs(parseFloat(item.ar_amount));
                
                console.log(`Set line item ${index}:`, {
                    element: row,
                    acct_type_id: item.acct_type_id,
                    amount: -Math.abs(parseFloat(item.ar_amount))
                });
            } else {
                console.error(`Row at index ${index} does not exist`);
            }
        });
        
        // Calculate total manually
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        document.getElementById('totalAmount').value = total.toFixed(2);
        
        // Re-attach event listeners for the new rows
        if (typeof attachRemoveLineListeners === 'function') {
            attachRemoveLineListeners();
        }
    } else {
        console.warn('No line items found for this transaction');
    }
    
    // Set remarks - prepend "CANCELLED OR: "
    const remarksField = document.getElementById('remarks');
    if (remarksField) {
        remarksField.value = `CANCELLED OR: ${transaction.or_number}`;
        
        // Append original remarks if they exist
        if (transaction.ar_remarks) {
            remarksField.value += ` - ${transaction.ar_remarks}`;
        }
    }
    
    // Disable fields that shouldn't be changed during reversal
    disableAccountFormFields();
}

// Helper function to create option elements for account types
function createAccountTypeOptions(selectedId, description) {
    // Use the actual description from the line item data
    const displayText = description || `Account Type ${selectedId}`;
    return `<option value="${selectedId}" selected>${displayText}</option>`;
}

function disableArrearsFormFields() {
    // Disable most fields in the arrears form
    const fieldsToDisable = [
        'arrears_addressId',
        'arrears_serviceInvoiceNo',
        'arrears_receivedFrom',
        'arrears_date'
    ];
    
    // Disable input fields
    fieldsToDisable.forEach(id => {
        const field = document.getElementById(id);
        if (field) field.setAttribute('disabled', 'disabled');
    });
    
    // Disable select fields and amount inputs
    document.querySelectorAll('#arrearsReceivableForm select, #arrearsReceivableForm .arrears-amount-input').forEach(element => {
        element.setAttribute('disabled', 'disabled');
    });
    
    // Disable payment mode radio buttons
    document.querySelectorAll('input[name="arrears_payment_mode"]').forEach(radio => {
        radio.setAttribute('disabled', 'disabled');
    });
    
    // Disable reference field
    document.getElementById('arrears_reference').setAttribute('disabled', 'disabled');
    
    // Only remarks should remain enabled
}

function disableAccountFormFields() {
    // Disable most fields in the account form
    const fieldsToDisable = [
        'address',
        'receivedFrom',
        'serviceInvoiceNo',
        'date'
    ];
    
    // Disable input fields
    fieldsToDisable.forEach(id => {
        const field = document.getElementById(id);
        if (field) field.setAttribute('disabled', 'disabled');
    });
    
    // Disable select fields and amount inputs
    document.querySelectorAll('#accountReceivableForm select, #accountReceivableForm .amount-input').forEach(element => {
        element.setAttribute('disabled', 'disabled');
    });
    
    // Disable payment mode radio buttons
    document.querySelectorAll('input[name="payment_mode"]').forEach(radio => {
        radio.setAttribute('disabled', 'disabled');
    });
    
    // Disable reference field
    document.getElementById('reference').setAttribute('disabled', 'disabled');
    
    // Disable add/remove line buttons
    document.querySelectorAll('.add-line, .remove-line').forEach(button => {
        button.setAttribute('disabled', 'disabled');
        button.classList.add('disabled');
    });
    
    // Only remarks should remain enabled
}

function changeToReversalMode() {
    // Change the save button text and styling
    const saveButton = document.getElementById('accountSaveBtn');
    if (saveButton) {
        saveButton.textContent = 'Reverse Transaction';
        saveButton.classList.remove('btn-primary');
        saveButton.classList.add('btn-danger');
        saveButton.setAttribute('data-reversal-mode', 'true');
        
        // CRITICAL FIX: Clone button to remove all existing handlers
        const newButton = saveButton.cloneNode(true);
        saveButton.parentNode.replaceChild(newButton, saveButton);
        
        // Add NEW click handler to the cloned button
        newButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default form submission
            e.stopPropagation(); // Stop event bubbling
            console.log("Reversal button clicked, showing confirmation");
            showReversalConfirmation();
            return false; // Extra prevention measure
        });
    }
    
    // Visual indication that we're in reversal mode
    const formContainer = document.querySelector('.card.shadow-sm.border-success');
    if (formContainer) {
        formContainer.classList.remove('border-success');
        formContainer.classList.add('border-danger');
    }
}

function showReversalConfirmation() {
    // Create modal if it doesn't exist
    if (!document.getElementById('reversalConfirmationModal')) {
        const modalHtml = `
            <div class="modal fade" id="reversalConfirmationModal" tabindex="-1" aria-labelledby="reversalConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="reversalConfirmationModalLabel">Confirm Transaction Reversal</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Warning:</strong> You are about to reverse this transaction. This action cannot be undone.
                            </div>
                            <p>Are you sure you want to reverse this transaction?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmReversalBtn">Confirm Reversal</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
    }
    
    // Always (re)add the event listener to ensure it's attached
    const confirmBtn = document.getElementById('confirmReversalBtn');
    if (confirmBtn) {
        // Remove any existing listeners first to prevent duplicates
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        
        // Add the event listener with proper error handling
        newBtn.addEventListener('click', function() {
            try {
                showToast('info', 'Processing reversal...');
                
                // Determine which form to submit
                const activeTab = document.querySelector('.tab-pane.active');
                console.log('Active tab for reversal:', activeTab.id);
                
                let form;
                if (activeTab.id === 'arrears') {
                    form = document.getElementById('arrearsReceivableForm');
                    console.log('Submitting arrears form for reversal');
                } else {
                    form = document.getElementById('accountReceivableForm');
                    console.log('Submitting account form for reversal');
                }
                
                if (!form) {
                    throw new Error('Form not found');
                }
                
                // Re-enable disabled fields for submission
                form.querySelectorAll('input:disabled, select:disabled').forEach(field => {
                    // Store the disabled state to restore after form submission
                    field.dataset.wasDisabled = 'true';
                    field.disabled = false;
                });
                
                // Submit the form
                console.log('Form is being submitted:', form.id);
                form.submit();
                
                // Close modal
                try {
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('reversalConfirmationModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } catch (modalError) {
                    console.error('Error closing modal:', modalError);
                    // Fallback close method
                    document.getElementById('reversalConfirmationModal').classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            } catch (error) {
                console.error('Error during reversal confirmation:', error);
                showToast('error', 'Error processing reversal: ' + error.message);
            }
        });
    } else {
        console.error('Confirm button not found');
        showToast('error', 'UI error: Confirmation button not found');
    }
    
    // Show the modal
    try {
        const modal = new bootstrap.Modal(document.getElementById('reversalConfirmationModal'));
        modal.show();
    } catch (error) {
        console.error('Error showing modal:', error);
        showToast('error', 'Could not display confirmation dialog: ' + error.message);
    }
}

// Add a helper function to reset the reversal mode (used when switching tabs or canceling)
function resetReversalMode() {
    // Change the button back to normal
    const saveButton = document.getElementById('accountSaveBtn');
    if (saveButton) {
        saveButton.textContent = 'Save';
        saveButton.classList.remove('btn-danger');
        saveButton.classList.add('btn-primary');
        saveButton.removeAttribute('data-reversal-mode');
        
        // Reset the onclick handler
        saveButton.onclick = null;
    }
    
    // Reset visual styling
    const formContainer = document.querySelector('.card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger');
        formContainer.classList.add('border-success');
    }
    
    // Enable all fields in both forms
    document.querySelectorAll('#arrearsReceivableForm input, #arrearsReceivableForm select, #arrearsReceivableForm textarea').forEach(element => {
        if (element.id !== 'arrears_active_tab' && element.name !== 'form_type' && !element.classList.contains('form-check-input')) {
            element.removeAttribute('disabled');
        }
    });
    
    document.querySelectorAll('#accountReceivableForm input, #accountReceivableForm select, #accountReceivableForm textarea').forEach(element => {
        if (element.id !== 'account_active_tab' && element.name !== 'form_type' && !element.classList.contains('form-check-input')) {
            element.removeAttribute('disabled');
        }
    });
    
    // Re-enable add/remove line buttons
    document.querySelectorAll('.add-line, .remove-line').forEach(button => {
        button.removeAttribute('disabled');
        button.classList.remove('disabled');
    });
}