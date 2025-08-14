// Payable transaction management and edit/cancel functionality
document.addEventListener('DOMContentLoaded', function() {
    setupVoucherLookup();
});

function setupVoucherLookup() {
    const voucherField = document.getElementById('voucherNo');
    
    if (voucherField) {
        voucherField.addEventListener('blur', function() {
            const voucherNumber = this.value.trim();
            if (voucherNumber) {
                lookupTransaction(voucherNumber);
            }
        });
        
        voucherField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const voucherNumber = this.value.trim();
                if (voucherNumber) {
                    lookupTransaction(voucherNumber);
                }
            }
        });
    }
}

// ========== MAIN TRANSACTION LOOKUP ==========
function lookupTransaction(voucherNumber) {
    showToast('info', `Checking if Voucher #${voucherNumber} exists...`);
    
    fetch(`/accounts/payables/check-voucher/${voucherNumber}`, {
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
            // Show choice modal instead of going directly to reversal
            showTransactionChoiceModal(data.transaction, data.line_items);
        }
    })
    .catch(error => {
        showToast('error', 'Error checking Voucher: ' + error.message);
    });
}

// ========== TRANSACTION CHOICE MODAL ==========
function showTransactionChoiceModal(transaction, lineItems) {
    // Populate modal with transaction details
    document.getElementById('modalVoucherNumber').textContent = transaction.ap_voucherno;
    document.getElementById('modalTransactionDate').textContent = new Date(transaction.ap_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    document.getElementById('modalTransactionAmount').textContent = '₱ ' + parseFloat(transaction.ap_total).toFixed(2);
    document.getElementById('modalPayeeName').textContent = transaction.ap_payee;
    
    // Store transaction data for later use
    window.currentTransactionData = {
        transaction: transaction,
        lineItems: lineItems
    };
    
    // Setup modal button events
    setupChoiceModalEvents();
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('transactionChoiceModal'));
    modal.show();
}

function setupChoiceModalEvents() {
    // Remove existing event listeners to prevent duplicates
    const editBtn = document.getElementById('editTransactionBtn');
    const cancelBtn = document.getElementById('cancelTransactionBtn');
    
    // Clone buttons to remove all event listeners
    const newEditBtn = editBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    editBtn.parentNode.replaceChild(newEditBtn, editBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    
    // Add fresh event listeners
    newEditBtn.addEventListener('click', function() {
        const modalElement = document.getElementById('transactionChoiceModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        
        // Properly hide the modal and remove backdrop
        modal.hide();
        
        // Ensure backdrop is removed after modal animation completes
        modalElement.addEventListener('hidden.bs.modal', function onHidden() {
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
            removeAllBackdrops();
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            
            // Setup edit mode after ensuring modal is completely closed
            setTimeout(() => {
                setupEditMode(window.currentTransactionData);
            }, 100);
        });
    });
    
    newCancelBtn.addEventListener('click', function() {
        const modalElement = document.getElementById('transactionChoiceModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        
        // Properly hide the modal and remove backdrop
        modal.hide();
        
        // Ensure backdrop is removed after modal animation completes
        modalElement.addEventListener('hidden.bs.modal', function onHidden() {
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
            removeAllBackdrops();
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            
            // Setup reversal after ensuring modal is completely closed
            setTimeout(() => {
                setupReversal(window.currentTransactionData.transaction, window.currentTransactionData.lineItems);
            }, 100);
        });
    });
}

// Helper function to remove all modal backdrops
function removeAllBackdrops() {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.removeProperty('display');
        modal.classList.remove('show');
    });
    
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

// ========== EDIT MODE FUNCTIONALITY ==========
function setupEditMode(transactionData) {
    const { transaction, lineItems } = transactionData;
    
    // IMPORTANT: Disable the voucher number field first to prevent re-triggering lookup
    const voucherNumberField = document.getElementById('voucherNo');
    if (voucherNumberField) {
        voucherNumberField.value = transaction.ap_voucherno;
        voucherNumberField.setAttribute('disabled', 'disabled');
        voucherNumberField.classList.add('bg-light');
    }
    
    // Populate form fields with transaction data
    document.getElementById('payee').value = transaction.ap_payee;
    document.getElementById('date').value = transaction.ap_date.split('T')[0]; // Format date
    
    // Set payment mode
    const paymentModeRadio = document.querySelector(`input[name="payment_mode"][value="${transaction.ap_paytype}"]`);
    if (paymentModeRadio) {
        paymentModeRadio.checked = true;
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.paytype_reference) {
        document.getElementById('reference').value = transaction.paytype_reference;
    }
    
    // Handle line items
    if (lineItems && lineItems.length > 0) {
        const tbody = document.querySelector('#lineItemsTable tbody');
        if (!tbody) return;
        
        // Clear existing line items - leave only the first row
        while (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
        
        // Create all required rows first
        for (let i = 1; i < lineItems.length; i++) {
            const newRow = document.createElement('tr');
            newRow.className = 'line-item';
            
            newRow.innerHTML = `
                <td>
                    <input type="text" 
                        class="form-control form-control-sm" 
                        name="items[${i}][particular]"
                        autocomplete="off" 
                        required>
                </td>
                <td>
                    <input type="number" 
                        class="form-control form-control-sm amount-input" 
                        name="items[${i}][amount]"
                        required>
                </td>
                <td>
                    <select class="form-select form-select-sm enhanced" 
                        name="items[${i}][account_type]" 
                        required>
                        <option value="">Select Account Type</option>
                        ${createAccountTypeOptions(lineItems[i].acct_type_id, lineItems[i].acct_description)}
                    </select>
                </td>
                <td>
                    <button type="button" 
                    class="btn btn-link text-danger remove-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
        }
        
        // Populate all rows with positive amounts for editing
        lineItems.forEach((item, index) => {
            if (index < tbody.children.length) {
                const row = tbody.children[index];
                const particularInput = row.querySelector('input[name^="items["]');
                const amountInput = row.querySelector('.amount-input');
                const select = row.querySelector('select[name^="items["]');
                
                if (particularInput) particularInput.value = item.ap_particular;
                if (amountInput) amountInput.value = Math.abs(parseFloat(item.ap_amount)); // Positive amount for edit
                if (select) select.value = item.acct_type_id;
            }
        });
        
        // Calculate total
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        document.getElementById('totalAmount').value = total.toFixed(2);
        
        // Re-attach event listeners
        if (typeof attachRemoveLineListeners === 'function') {
            attachRemoveLineListeners();
        }
    }
    
    // Set remarks - prepend "EDITED FROM VOUCHER: "
    const remarksField = document.getElementById('remarks');
    if (remarksField) {
        remarksField.value = `EDITED FROM VOUCHER: ${transaction.ap_voucherno}`;
        if (transaction.remarks && !transaction.remarks.includes('CANCELLED VOUCHER')) {
            remarksField.value += ` - ${transaction.remarks}`;
        }
    }
    
    changeToEditMode();
    showToast('info', 'Edit mode enabled. Modify the transaction and click "Update Voucher"');
}

function changeToEditMode() {
    // Change the save button text and styling for edit mode
    const saveButton = document.querySelector('#payableForm button[type="submit"]');
    if (saveButton) {
        saveButton.textContent = 'Update Voucher';
        saveButton.classList.remove('btn-primary', 'btn-danger');
        saveButton.classList.add('btn-info');
        saveButton.setAttribute('data-edit-mode', 'true');
        
        // Clone button to remove existing handlers
        const newButton = saveButton.cloneNode(true);
        saveButton.parentNode.replaceChild(newButton, saveButton);
        
        // Add edit mode click handler
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleEditSubmission();
            return false;
        });
    }
    
    // Visual indication for edit mode - change border color
    const formContainer = document.querySelector('.card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger');
        formContainer.classList.add('border-info');
    }
}

function handleEditSubmission() {
    const form = document.getElementById('payableForm');
    if (validateEditForm(form)) {
        showEditConfirmation();
    }
}

function validateEditForm(form) {
    // Basic HTML5 validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    // Check if at least one line item is added
    const lineItemRows = form.querySelectorAll('.line-item');
    if (lineItemRows.length === 0) {
        showToast('error', 'Please add at least one line item');
        return false;
    }
    
    let hasValidItem = false;
    for (let row of lineItemRows) {
        const particularInput = row.querySelector('input[name^="items["]');
        const amountInput = row.querySelector('.amount-input');
        const select = row.querySelector('select[name^="items["]');
        
        if (particularInput && amountInput && select) {
            const particularValue = particularInput.value.trim();
            const amountValue = parseFloat(amountInput.value);
            const selectValue = select.value;
            
            if (particularValue && amountValue && amountValue > 0 && selectValue) {
                hasValidItem = true;
                break;
            }
        }
    }
    
    if (!hasValidItem) {
        showToast('error', 'Please add at least one valid line item with particular, amount, and account type');
        return false;
    }
    
    // Validate each line item amount
    for (let row of lineItemRows) {
        const amountInput = row.querySelector('.amount-input');
        if (amountInput && amountInput.value) {
            const value = parseFloat(amountInput.value);
            if (!value || value <= 0) {
                showToast('error', 'All amounts must be greater than zero');
                amountInput.focus();
                return false;
            }
        }
    }
    
    return true;
}

function showEditConfirmation() {
    // Create edit confirmation modal if it doesn't exist
    if (!document.getElementById('editConfirmationModal')) {
        const modalHtml = `
            <div class="modal fade" id="editConfirmationModal" tabindex="-1" aria-labelledby="editConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="editConfirmationModalLabel">Confirm Voucher Update</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>Confirm Update:</strong> This will create a new entry and cancel the original transaction.
                            </div>
                            <p>Are you sure you want to update this voucher?</p>
                            
                            <div class="mb-3">
                                <label for="editReason" class="form-label">Reason for Edit:</label>
                                <input type="text" class="form-control" id="editReason" 
                                    placeholder="Please provide a reason for editing" required>
                                <div class="form-text text-muted">This reason will be recorded in the system.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" id="confirmEditBtn">Update Voucher</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
    }
    
    // Setup confirmation button event
    const confirmBtn = document.getElementById('confirmEditBtn');
    if (confirmBtn) {
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        
        newBtn.addEventListener('click', function() {
            const reasonField = document.getElementById('editReason');
            const editReason = reasonField ? reasonField.value.trim() : '';
            
            if (!editReason) {
                reasonField.classList.add('is-invalid');
                return;
            }
            
            // Add edit reason to remarks
            const remarksField = document.getElementById('remarks');
            if (remarksField) {
                remarksField.value = remarksField.value + ` - Edit Reason: ${editReason}`;
            }
            
            // Submit the form
            const form = document.getElementById('payableForm');
            if (form) {
                // Add edit flag
                const hiddenFlag = document.createElement('input');
                hiddenFlag.type = 'hidden';
                hiddenFlag.name = 'voucher_edit';
                hiddenFlag.value = 'true';
                form.appendChild(hiddenFlag);
                
                showToast('info', 'Processing update...');
                
                // Re-enable disabled fields for form submission
                prepareFormForSubmission(form);
                
                // Close modal and submit
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editConfirmationModal'));
                if (modalInstance) modalInstance.hide();
                
                setTimeout(() => form.submit(), 100);
            }
        });
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editConfirmationModal'));
    modal.show();
    
    // Focus on reason field
    document.getElementById('editConfirmationModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('editReason').focus();
    });
}

// ========== REVERSAL MODE FUNCTIONALITY ==========
function setupReversal(transaction, lineItems) {
    // IMPORTANT: Disable the voucher number field first to prevent re-triggering lookup
    const voucherNumberField = document.getElementById('voucherNo');
    if (voucherNumberField) {
        voucherNumberField.value = transaction.ap_voucherno;
        voucherNumberField.setAttribute('disabled', 'disabled');
        voucherNumberField.classList.add('bg-light');
    }
    
    // Populate form fields with transaction data
    document.getElementById('payee').value = transaction.ap_payee;
    document.getElementById('date').value = transaction.ap_date.split('T')[0]; // Format date
    
    // Set payment mode
    const paymentModeRadio = document.querySelector(`input[name="payment_mode"][value="${transaction.ap_paytype}"]`);
    if (paymentModeRadio) {
        paymentModeRadio.checked = true;
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.paytype_reference) {
        document.getElementById('reference').value = transaction.paytype_reference;
    }
    
    // Handle line items from separate lineItems parameter
    if (lineItems && lineItems.length > 0) {
        const tbody = document.querySelector('#lineItemsTable tbody');
        if (!tbody) return;
        
        // Clear existing line items - leave only the first row
        while (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
        
        // Create all required rows first
        for (let i = 1; i < lineItems.length; i++) {
            const newRow = document.createElement('tr');
            newRow.className = 'line-item';
            
            newRow.innerHTML = `
                <td>
                    <input type="text" 
                        class="form-control form-control-sm" 
                        name="items[${i}][particular]"
                        autocomplete="off" 
                        required>
                </td>
                <td>
                    <input type="number" 
                        class="form-control form-control-sm amount-input" 
                        name="items[${i}][amount]"
                        required>
                </td>
                <td>
                    <select class="form-select form-select-sm enhanced" 
                        name="items[${i}][account_type]" 
                        required>
                        <option value="">Select Account Type</option>
                        ${createAccountTypeOptions(lineItems[i].acct_type_id, lineItems[i].acct_description)}
                    </select>
                </td>
                <td>
                    <button type="button" 
                    class="btn btn-link text-danger remove-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
        }
        
        // Now populate all rows with negative amounts
        lineItems.forEach((item, index) => {
            if (index < tbody.children.length) {
                const row = tbody.children[index];
                const particularInput = row.querySelector('input[name^="items["]');
                const amountInput = row.querySelector('.amount-input');
                const select = row.querySelector('select[name^="items["]');
                
                if (particularInput) particularInput.value = item.ap_particular;
                if (amountInput) amountInput.value = -Math.abs(parseFloat(item.ap_amount)); // Negative amount for reversal
                if (select) select.value = item.acct_type_id;
            }
        });
        
        // Calculate total manually
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        document.getElementById('totalAmount').value = total.toFixed(2);
    }
    
    // Set remarks - prepend "CANCELLED VOUCHER: "
    const remarksField = document.getElementById('remarks');
    if (remarksField) {
        remarksField.value = `CANCELLED VOUCHER: ${transaction.ap_voucherno}`;
        
        // Append original remarks if they exist
        if (transaction.remarks) {
            remarksField.value += ` - ${transaction.remarks}`;
        }
    }
    
    // Disable fields that shouldn't be changed during reversal
    disableFormFields();
    changeToReversalMode();
}

function disableFormFields() {
    // Disable most fields in the form
    const fieldsToDisable = [
        'voucherNo',
        'payee', 
        'date'
    ];
    
    // Disable input fields
    fieldsToDisable.forEach(id => {
        const field = document.getElementById(id);
        if (field) field.setAttribute('disabled', 'disabled');
    });
    
    // Disable select fields and amount inputs
    document.querySelectorAll('#payableForm select, #payableForm .amount-input').forEach(element => {
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
    const saveButton = document.querySelector('#payableForm button[type="submit"]');
    if (saveButton) {
        saveButton.textContent = 'Cancel Voucher';
        saveButton.classList.remove('btn-primary');
        saveButton.classList.add('btn-danger');
        saveButton.setAttribute('data-reversal-mode', 'true');
        
        // Clone button to remove all existing handlers
        const newButton = saveButton.cloneNode(true);
        saveButton.parentNode.replaceChild(newButton, saveButton);
        
        // Add NEW click handler to the cloned button
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showReversalConfirmation();
            return false;
        });
    }
    
    // Visual indication that we're in reversal mode
    const formContainer = document.querySelector('.card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger');
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
                            <h5 class="modal-title" id="reversalConfirmationModalLabel">Confirm Voucher Cancellation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Warning:</strong> You are about to cancel this voucher. This action cannot be undone.
                            </div>
                            <p>Are you sure you want to cancel this voucher?</p>
                            
                            <div class="mb-3">
                                <label for="cancellationReason" class="form-label">Reason for Cancellation:</label>
                                <input type="text" class="form-control" id="cancellationReason" 
                                    placeholder="Please provide a reason" required>
                                <div class="form-text text-muted">This reason will be recorded in the system.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Voucher</button>
                            <button type="button" class="btn btn-danger" id="confirmReversalBtn">Yes, Cancel Voucher</button>
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
        
        // Add the event listener
        newBtn.addEventListener('click', function() {
            try {
                // Get the cancellation reason
                const reasonField = document.getElementById('cancellationReason');
                const cancellationReason = reasonField ? reasonField.value.trim() : '';
                
                // Validate that reason is provided
                if (!cancellationReason) {
                    reasonField.classList.add('is-invalid');
                    return;
                }
                
                showToast('info', 'Processing cancellation...');
                
                const form = document.getElementById('payableForm');
                const remarksField = document.getElementById('remarks');
                
                if (!form) {
                    throw new Error('Form not found');
                }
                
                // Append the reason to the existing remarks
                if (remarksField) {
                    remarksField.value = remarksField.value + ` - Reason: ${cancellationReason}`;
                }
                
                // Re-enable ALL form elements for submission
                form.querySelectorAll('input, select, textarea, button').forEach(field => {
                    field.disabled = false;
                });
                
                // Add a submission flag
                const hiddenFlag = document.createElement('input');
                hiddenFlag.type = 'hidden';
                hiddenFlag.name = 'voucher_cancellation';
                hiddenFlag.value = 'true';
                form.appendChild(hiddenFlag);
                
                // Close modal before submitting
                try {
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('reversalConfirmationModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } catch (modalError) {
                    console.error('Error closing modal:', modalError);
                    document.getElementById('reversalConfirmationModal').classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
                
                // Force the form to submit after a brief delay
                setTimeout(function() {
                    form.submit();
                }, 100);
                
            } catch (error) {
                console.error('Error during reversal confirmation:', error);
                showToast('error', 'Error processing reversal: ' + error.message);
            }
        });
    }
    
    // Focus on the reason field when the modal opens
    document.getElementById('reversalConfirmationModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('cancellationReason').focus();
    });
    
    // Show the modal
    try {
        const modal = new bootstrap.Modal(document.getElementById('reversalConfirmationModal'));
        modal.show();
    } catch (error) {
        console.error('Error showing modal:', error);
        showToast('error', 'Could not display confirmation dialog: ' + error.message);
    }
}

// ========== UTILITY FUNCTIONS ==========
function createAccountTypeOptions(selectedId, description) {
    const displayText = description || `Account Type ${selectedId}`;
    return `<option value="${selectedId}" selected>${displayText}</option>`;
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

function resetEditMode() {
    // Change button back to normal
    const saveButton = document.querySelector('#payableForm button[type="submit"]');
    if (saveButton) {
        saveButton.textContent = 'Save';
        saveButton.classList.remove('btn-danger', 'btn-info');
        saveButton.classList.add('btn-primary');
        saveButton.removeAttribute('data-edit-mode');
        saveButton.onclick = null;
        
        // Optional: Clone to ensure all listeners are removed
        const newButton = saveButton.cloneNode(true);
        saveButton.parentNode.replaceChild(newButton, saveButton);
    }
    
    // Reset visual styling
    const formContainer = document.querySelector('.card.shadow-sm.border-info, .card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger', 'border-info');
        formContainer.classList.add('border-danger');
    }
    
    // Enable all fields
    document.querySelectorAll('#payableForm input, #payableForm select, #payableForm textarea').forEach(element => {
        element.removeAttribute('disabled');
        element.classList.remove('bg-light');
    });
    
    // Re-enable add/remove line buttons
    document.querySelectorAll('.add-line, .remove-line').forEach(button => {
        button.removeAttribute('disabled');
        button.classList.remove('disabled');
    });
}

function resetReversalMode() {
    // Change the button back to normal
    const saveButton = document.querySelector('#payableForm button[type="submit"]');
    if (saveButton) {
        saveButton.textContent = 'Save';
        saveButton.classList.remove('btn-danger');
        saveButton.classList.add('btn-primary');
        saveButton.removeAttribute('data-reversal-mode');
        saveButton.onclick = null;
    }
    
    // Reset visual styling
    const formContainer = document.querySelector('.card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger');
        formContainer.classList.add('border-danger');
    }
    
    // Enable all fields
    document.querySelectorAll('#payableForm input, #payableForm select, #payableForm textarea').forEach(element => {
        element.removeAttribute('disabled');
    });
    
    // Re-enable add/remove line buttons
    document.querySelectorAll('.add-line, .remove-line').forEach(button => {
        button.removeAttribute('disabled');
        button.classList.remove('disabled');
    });
}