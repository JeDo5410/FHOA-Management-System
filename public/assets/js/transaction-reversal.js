// Transaction reversal and edit functionality
document.addEventListener('DOMContentLoaded', function() {
    setupInvoiceLookup();
});

function setupInvoiceLookup() {
    const arrearsInvoiceField = document.getElementById('arrears_serviceInvoiceNo');
    const accountInvoiceField = document.getElementById('serviceInvoiceNo');
    
    if (arrearsInvoiceField) {
        arrearsInvoiceField.addEventListener('blur', function() {
            // Get the raw value and process it for SIN lookup
            const rawValue = this.value.trim();
            
            // If this is a masked input, extract numeric value for lookup
            if (this.classList.contains('sin-masked-input')) {
                const numericValue = rawValue.replace(/\D/g, '');
                if (numericValue && numericValue !== '0' && numericValue !== '00000') {
                    // Convert to integer to remove leading zeros for lookup
                    const invoiceNumber = parseInt(numericValue, 10);
                    if (invoiceNumber > 0) {
                        lookupTransaction(invoiceNumber.toString(), 'arrears');
                    }
                }
            } else if (rawValue && rawValue !== '0') {
                lookupTransaction(rawValue, 'arrears');
            }
        });
        
        arrearsInvoiceField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Get the raw value and process it for SIN lookup
                const rawValue = this.value.trim();
                
                // If this is a masked input, extract numeric value for lookup
                if (this.classList.contains('sin-masked-input')) {
                    const numericValue = rawValue.replace(/\D/g, '');
                    if (numericValue && numericValue !== '0' && numericValue !== '00000') {
                        // Convert to integer to remove leading zeros for lookup
                        const invoiceNumber = parseInt(numericValue, 10);
                        if (invoiceNumber > 0) {
                            lookupTransaction(invoiceNumber.toString(), 'arrears');
                        }
                    }
                } else if (rawValue && rawValue !== '0') {
                    lookupTransaction(rawValue, 'arrears');
                }
            }
        });
    }
    
    if (accountInvoiceField) {
        accountInvoiceField.addEventListener('blur', function() {
            // Get the raw value and process it for SIN lookup
            const rawValue = this.value.trim();
            
            // If this is a masked input, extract numeric value for lookup
            if (this.classList.contains('sin-masked-input')) {
                const numericValue = rawValue.replace(/\D/g, '');
                if (numericValue && numericValue !== '0' && numericValue !== '00000') {
                    // Convert to integer to remove leading zeros for lookup
                    const invoiceNumber = parseInt(numericValue, 10);
                    if (invoiceNumber > 0) {
                        lookupTransaction(invoiceNumber.toString(), 'account');
                    }
                }
            } else if (rawValue && rawValue !== '0') {
                lookupTransaction(rawValue, 'account');
            }
        });
        
        accountInvoiceField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Get the raw value and process it for SIN lookup
                const rawValue = this.value.trim();
                
                // If this is a masked input, extract numeric value for lookup
                if (this.classList.contains('sin-masked-input')) {
                    const numericValue = rawValue.replace(/\D/g, '');
                    if (numericValue && numericValue !== '0' && numericValue !== '00000') {
                        // Convert to integer to remove leading zeros for lookup
                        const invoiceNumber = parseInt(numericValue, 10);
                        if (invoiceNumber > 0) {
                            lookupTransaction(invoiceNumber.toString(), 'account');
                        }
                    }
                } else if (rawValue && rawValue !== '0') {
                    lookupTransaction(rawValue, 'account');
                }
            }
        });
    }
}

// ========== MAIN TRANSACTION LOOKUP ==========
function lookupTransaction(invoiceNumber, formType) {
    // Prevent lookup if we're in edit mode
    if (window.isEditMode) {
        return;
    }
    
    showToast('info', `Checking if SIN #${invoiceNumber} exists...`);
    
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
            const transactionTabType = data.tab_type;
            
            if (transactionTabType !== formType) {
                showToast('error', `This SIN (#${invoiceNumber}) belongs to the ${data.tab_type === 'arrears' ? 'HOA Monthly Dues' : 'Account Receivable'} tab. Please switch tabs to modify it.`);
                return;
            }
            
            // Show choice modal instead of going directly to reversal
            showTransactionChoiceModal(data.transaction, formType, data.line_items);
        }
    })
    .catch(error => {
        showToast('error', 'Error checking SIN: ' + error.message);
    });
}

// ========== TRANSACTION CHOICE MODAL ==========
function showTransactionChoiceModal(transaction, formType, lineItems) {
    // Populate modal with transaction details
    document.getElementById('modalSinNumber').textContent = transaction.or_number;
    document.getElementById('modalTransactionDate').textContent = new Date(transaction.ar_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    document.getElementById('modalTransactionAmount').textContent = '₱ ' + parseFloat(transaction.ar_amount).toFixed(2);
    document.getElementById('modalPayorName').textContent = transaction.payor_name;
    
    // Store transaction data for later use
    window.currentTransactionData = {
        transaction: transaction,
        formType: formType,
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
            // Remove this event listener to prevent it from running multiple times
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
            
            // Remove any remaining backdrop
            removeAllBackdrops();
            
            // Remove modal-open class from body
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
            // Remove this event listener to prevent it from running multiple times
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
            
            // Remove any remaining backdrop
            removeAllBackdrops();
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            
            // Setup reversal after ensuring modal is completely closed
            setTimeout(() => {
                setupReversal(window.currentTransactionData.transaction, window.currentTransactionData.formType, 
                             window.currentTransactionData.formType === 'arrears', window.currentTransactionData.lineItems);
            }, 100);
        });
    });
}

// Helper function to remove all modal backdrops
function removeAllBackdrops() {
    // Remove all backdrop elements
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // Also remove any inline styles that might have been added
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.removeProperty('display');
        modal.classList.remove('show');
    });
    
    // Reset body classes and styles
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

// Add this cleanup function to be called when needed
function cleanupModalState() {
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    
    // Remove all backdrops
    removeAllBackdrops();
    
    // Ensure all modals are properly hidden
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
}

// Fix for the Exit button and X close button
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal close events for all close triggers
    const transactionChoiceModal = document.getElementById('transactionChoiceModal');
    
    if (transactionChoiceModal) {
        // Handle modal close events (X button, Exit button, clicking outside)
        transactionChoiceModal.addEventListener('hidden.bs.modal', function() {
            // Ensure complete modal cleanup
            removeAllBackdrops();
            
            // Determine which tab is currently active
            const activeTab = document.querySelector('.tab-pane.active');
            const activeTabId = activeTab ? activeTab.getAttribute('id') : null;
            
            // Reset the appropriate form based on active tab
            if (activeTabId === 'arrears') {
                // Reset arrears form
                if (typeof clearArrearsFormFields === 'function') {
                    clearArrearsFormFields();
                }
                // Reset any edit or reversal modes
                if (typeof resetEditMode === 'function') {
                    resetEditMode();
                }
                if (typeof resetReversalMode === 'function') {
                    resetReversalMode();
                }
            } else if (activeTabId === 'account') {
                // Reset account form
                if (typeof clearAccountFormFields === 'function') {
                    clearAccountFormFields();
                }
                // Reset any edit or reversal modes
                if (typeof resetEditMode === 'function') {
                    resetEditMode();
                }
                if (typeof resetReversalMode === 'function') {
                    resetReversalMode();
                }
            }
            
            // Show confirmation message
            showToast('info', 'Form has been reset');
        });
        
        // Additional cleanup for all modal close triggers
        transactionChoiceModal.addEventListener('hide.bs.modal', function() {
            // This fires before the modal starts hiding
            removeAllBackdrops();
        });
        
        // Handle manual close events (X button, Exit button)
        const closeButtons = transactionChoiceModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Force cleanup when manually closing
                setTimeout(() => {
                    removeAllBackdrops();
                }, 150); // Small delay to ensure modal animation completes
            });
        });
        
        // Handle clicking outside the modal (backdrop click)
        transactionChoiceModal.addEventListener('click', function(e) {
            if (e.target === transactionChoiceModal) {
                // Force cleanup for backdrop clicks
                setTimeout(() => {
                    removeAllBackdrops();
                }, 150);
            }
        });
    }
});

// ========== EDIT MODE FUNCTIONALITY ==========
function setupEditMode(transactionData) {
    const { transaction, formType, lineItems } = transactionData;
    
    if (formType === 'arrears') {
        setupArrearsEditMode(transaction);
    } else {
        // For future account receivable implementation
        setupAccountEditMode(transaction, lineItems);
    }
    
    changeToEditMode();
    showToast('info', 'Edit mode enabled. Modify the transaction and click "Update SIN"');
}

function setupArrearsEditMode(transaction) {
    const originalPayorName = transaction.payor_name;
    const originalAddress = transaction.payor_address;
    const memberId = transaction.mem_id;
    
    // IMPORTANT: Disable the OR number field first to prevent re-triggering lookup
    const orNumberField = document.getElementById('arrears_serviceInvoiceNo');
    if (orNumberField) {
        orNumberField.value = transaction.or_number;
        orNumberField.setAttribute('disabled', 'disabled');
        orNumberField.classList.add('bg-light'); // Visual indication it's disabled
    }
    
    // Populate form fields with transaction data (similar to reversal but positive amounts)
    document.getElementById('arrears_receivedFrom').value = originalPayorName;
    const originalDate = new Date(transaction.ar_date);
    document.getElementById('arrears_date').value = originalDate.toLocaleDateString('en-CA');
    
    // Set the address ID and trigger lookup
    const addressIdField = document.getElementById('arrears_addressId');
    if (addressIdField) {
        addressIdField.value = "Loading...";
        window.isEditMode = true;
        
        fetchAddressIdByMemberId(memberId)
            .then(addressId => {
                if (addressId) {
                    addressIdField.value = addressId;
                    
                    if (window.arrearsAddressLookup && typeof window.arrearsAddressLookup.selectAddressById === 'function') {
                        window.arrearsAddressLookup.selectAddressById(addressId, memberId);
                    } else {
                        const event = new Event('blur', { bubbles: true });
                        addressIdField.dispatchEvent(event);
                    }
                    
                    // Restore original payor name after lookup
                    setTimeout(() => {
                        document.getElementById('arrears_receivedFrom').value = originalPayorName;
                    }, 800);
                } else {
                    addressIdField.value = originalAddress;
                    showToast('warning', 'Could not find exact address ID. Using original address.');
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
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.payment_Ref) {
        document.getElementById('arrears_reference').value = transaction.payment_Ref;
    }
    
    // Set account type and amount (positive for edit)
    const accountTypeSelect = document.querySelector('select[name="arrears_items[0][coa]"]');
    if (accountTypeSelect) {
        accountTypeSelect.value = transaction.acct_type_id;
    }
    
    const amountInput = document.querySelector('.arrears-amount-input');
    if (amountInput) {
        // Use positive amount for edit (user can modify)
        amountInput.value = Math.abs(parseFloat(transaction.ar_amount));
    }
    
    // Set remarks - prepend "EDITED FROM SIN: "
    const remarksField = document.getElementById('arrears_remarks');
    if (remarksField) {
        remarksField.value = `EDITED FROM SIN: ${transaction.or_number}`;
        if (transaction.ar_remarks && !transaction.ar_remarks.includes('CANCELLED SIN')) {
            remarksField.value += ` - ${transaction.ar_remarks}`;
        }
    }
}

function setupAccountEditMode(transaction, lineItems) {
    // Add this line at the beginning to prevent lookup during edit mode
    window.isEditMode = true;
    
    // IMPORTANT: Disable the OR number field first to prevent re-triggering lookup
    const orNumberField = document.getElementById('serviceInvoiceNo');
    if (orNumberField) {
        orNumberField.setAttribute('disabled', 'disabled');
    }
    
    // Populate the main form fields
    document.getElementById('serviceInvoiceNo').value = transaction.or_number;
    document.getElementById('address').value = transaction.payor_address;
    document.getElementById('receivedFrom').value = transaction.payor_name;
    document.getElementById('date').value = transaction.ar_date.split('T')[0];
    
    // Set payment mode
    const paymentModeRadio = document.querySelector(`input[name="payment_mode"][value="${transaction.payment_type}"]`);
    if (paymentModeRadio) {
        paymentModeRadio.checked = true;
        paymentModeRadio.dispatchEvent(new Event('change'));
    }
    
    // Set reference number if it exists
    if (transaction.payment_Ref) {
        document.getElementById('reference').value = transaction.payment_Ref;
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
        
        // Populate all rows with positive amounts for editing
        lineItems.forEach((item, index) => {
            if (index < tbody.children.length) {
                const row = tbody.children[index];
                const select = row.querySelector('select[name^="items["]');
                const amountInput = row.querySelector('.amount-input');
                
                if (select) select.value = item.acct_type_id;
                if (amountInput) amountInput.value = Math.abs(parseFloat(item.ar_amount)); // Positive amount for edit
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
    
    // Set remarks (no "EDITED FROM SIN" prefix for account receivables)
    const remarksField = document.getElementById('remarks');
    if (remarksField) {
        remarksField.value = transaction.ar_remarks || '';
    }
}

function changeToEditMode() {
    // Change the save button text and styling for edit mode
    const saveButton = document.getElementById('accountSaveBtn');
    if (saveButton) {
        saveButton.textContent = 'Update SIN';
        saveButton.classList.remove('btn-primary', 'btn-danger');
        saveButton.classList.add('btn-info');
        saveButton.setAttribute('data-edit-mode', 'true');
        
        // Clone button to remove existing handlers (EXACTLY like reversal mode)
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
    const formContainer = document.querySelector('.card.shadow-sm.border-success');
    if (formContainer) {
        formContainer.classList.remove('border-success', 'border-danger');
        formContainer.classList.add('border-info');
    }
    
    // NO BADGE ADDED - Just like reversal mode
}

function handleEditSubmission() {
    const activeTab = document.querySelector('.tab-pane.active');
    const activeTabId = activeTab.getAttribute('id');
    
    if (activeTabId === 'arrears') {
        const form = document.getElementById('arrearsReceivableForm');
        if (validateArrearsEditForm(form)) {
            showEditConfirmation();
        }
    } else if (activeTabId === 'account') {
        const form = document.getElementById('accountReceivableForm');
        if (validateAccountEditForm(form)) {
            // Direct submission for account receivables (no confirmation modal needed)
            prepareFormForSubmission(form);
            form.submit();
        }
    }
}

function validateArrearsEditForm(form) {
    // Basic HTML5 validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    // Check amount is positive for edit
    const amountInput = document.querySelector('.arrears-amount-input');
    const value = parseFloat(amountInput.value);
    
    if (!value || value <= 0) {
        showToast('error', 'Please enter a valid amount greater than zero');
        amountInput.focus();
        return false;
    }
    
    // Check payment mode and reference number
    const paymentMode = form.querySelector('input[name="arrears_payment_mode"]:checked')?.value;
    const referenceNo = form.querySelector('#arrears_reference').value;
    
    if (paymentMode && paymentMode !== 'CASH' && !referenceNo) {
        showToast('error', 'Reference number is required for ' + paymentMode + ' payments');
        form.querySelector('#arrears_reference').focus();
        return false;
    }
    
    return true;
}

function validateAccountEditForm(form) {
    // Basic HTML5 validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    // Validate line items using correct class name
    const lineItemRows = form.querySelectorAll('.line-item');
    if (lineItemRows.length === 0) {
        showToast('error', 'Please add at least one line item');
        return false;
    }
    
    let hasValidItem = false;
    for (let row of lineItemRows) {
        const coaSelect = row.querySelector('select'); // Just 'select', no specific class
        const amountInput = row.querySelector('.amount-input');
        
        if (coaSelect && amountInput) {
            const coaValue = coaSelect.value;
            const amountValue = parseFloat(amountInput.value);
            
            if (coaValue && amountValue && amountValue > 0) {
                hasValidItem = true;
                break;
            }
        }
    }
    
    if (!hasValidItem) {
        showToast('error', 'Please add at least one valid line item with chart of account and amount');
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
    
    // Check payment mode and reference number
    const paymentMode = form.querySelector('input[name="payment_mode"]:checked')?.value;
    const referenceNo = form.querySelector('#referenceNo')?.value;
    
    if (paymentMode && paymentMode !== 'CASH' && !referenceNo) {
        showToast('error', 'Reference number is required for ' + paymentMode + ' payments');
        const refField = form.querySelector('#referenceNo');
        if (refField) {
            refField.focus();
        }
        return false;
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
                            <h5 class="modal-title" id="editConfirmationModalLabel">Confirm SIN Update</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>Confirm Update:</strong> This will create a new entry and cancel the original transaction.
                            </div>
                            <p>Are you sure you want to update this SIN?</p>
                            
                            <div class="mb-3">
                                <label for="editReason" class="form-label">Reason for Edit:</label>
                                <input type="text" class="form-control" id="editReason" 
                                    placeholder="Please provide a reason for editing" required>
                                <div class="form-text text-muted">This reason will be recorded in the system.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" id="confirmEditBtn">Update SIN</button>
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
            const remarksField = document.getElementById('arrears_remarks');
            if (remarksField) {
                remarksField.value = remarksField.value + ` - Edit Reason: ${editReason}`;
            }
            
            // Submit the form
            const form = document.getElementById('arrearsReceivableForm');
            if (form) {
                // Add edit flag
                const hiddenFlag = document.createElement('input');
                hiddenFlag.type = 'hidden';
                hiddenFlag.name = 'sin_edit';
                hiddenFlag.value = 'true';
                form.appendChild(hiddenFlag);
                
                showToast('info', 'Processing update...');
                
                // ADD THIS LINE - Re-enable disabled fields for form submission (same as reversal mode)
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

// ========== EXISTING REVERSAL FUNCTIONALITY (Unchanged) ==========
function setupReversal(transaction, formType, isArrears, lineItems) {
    if (formType === 'arrears') {
        setupArrearsReversal(transaction);
    } else {
        setupAccountReversal(transaction, lineItems);
    }
    changeToReversalMode();
}

// [Keep all existing reversal functions unchanged - setupArrearsReversal, setupAccountReversal, etc.]

// ========== UTILITY FUNCTIONS ==========
function resetEditMode() {
    // Change button back to normal (EXACTLY like resetReversalMode)
    const saveButton = document.getElementById('accountSaveBtn');
    if (saveButton) {
        saveButton.textContent = 'Save';
        saveButton.classList.remove('btn-danger', 'btn-info');
        saveButton.classList.add('btn-primary');
        saveButton.removeAttribute('data-edit-mode');
        
        // Reset onclick to null first (like reversal reset)
        saveButton.onclick = null;
        
        // Optional: Clone to ensure all listeners are removed
        const newButton = saveButton.cloneNode(true);
        saveButton.parentNode.replaceChild(newButton, saveButton);
    }
    
    // Reset visual styling
    const formContainer = document.querySelector('.card.shadow-sm.border-info, .card.shadow-sm.border-danger');
    if (formContainer) {
        formContainer.classList.remove('border-danger', 'border-info');
        formContainer.classList.add('border-success');
    }
    
    // Clear edit mode flag
    window.isEditMode = false;
    
    // Enable all fields INCLUDING the OR number fields
    document.querySelectorAll('#arrearsReceivableForm input, #arrearsReceivableForm select, #arrearsReceivableForm textarea').forEach(element => {
        if (element.id !== 'arrears_active_tab' && element.name !== 'form_type') {
            element.removeAttribute('disabled');
            element.classList.remove('bg-light');
        }
    });
    
    document.querySelectorAll('#accountReceivableForm input, #accountReceivableForm select, #accountReceivableForm textarea').forEach(element => {
        if (element.id !== 'account_active_tab' && element.name !== 'form_type') {
            element.removeAttribute('disabled');
            element.classList.remove('bg-light');
        }
    });
}

// ========== KEEP ALL EXISTING REVERSAL FUNCTIONS BELOW ==========

async function fetchAddressIdByMemberId(memberId) {
    try {
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
    
    // CRITICAL: Set the SIN number field FIRST before anything else
    const orNumberField = document.getElementById('arrears_serviceInvoiceNo');
    if (orNumberField) {
        orNumberField.value = transaction.or_number;
    }
    
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
        remarksField.value = `CANCELLED SIN: ${transaction.or_number}`;
        
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
    // Prevents SINLookup loop
    const orNumberField = document.getElementById('serviceInvoiceNo');
    if (orNumberField) {
        orNumberField.setAttribute('disabled', 'disabled');
    }
    // Populate the main form fields
    document.getElementById('serviceInvoiceNo').value = transaction.or_number;
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
        remarksField.value = `CANCELLED SIN: ${transaction.or_number}`;
        
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
        saveButton.textContent = 'Cancel SIN';
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
                            <h5 class="modal-title" id="reversalConfirmationModalLabel">Confirm SIN Cancellation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Warning:</strong> You are about to cancel this SIN. This action cannot be undone.
                            </div>
                            <p>Are you sure you want to cancel this SIN?</p>
                            
                            <!-- Reason input field -->
                            <div class="mb-3">
                                <label for="cancellationReason" class="form-label">Reason for Cancellation:</label>
                                <input type="text" class="form-control" id="cancellationReason" 
                                    placeholder="Please provide a reason" required>
                                <div class="form-text text-muted">This reason will be recorded in the system.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep SIN</button>
                            <button type="button" class="btn btn-danger" id="confirmReversalBtn">Yes, Cancel SIN</button>
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
                // Get the cancellation reason
                const reasonField = document.getElementById('cancellationReason');
                const cancellationReason = reasonField ? reasonField.value.trim() : '';
                
                // Validate that reason is provided
                if (!cancellationReason) {
                    // Show error and prevent submission
                    reasonField.classList.add('is-invalid');
                    const invalidFeedback = document.createElement('div');
                    invalidFeedback.className = 'invalid-feedback';
                    invalidFeedback.textContent = 'Please provide a reason for cancellation';
                    
                    // Only append if it doesn't exist already
                    if (!reasonField.nextElementSibling || !reasonField.nextElementSibling.classList.contains('invalid-feedback')) {
                        reasonField.parentNode.insertBefore(invalidFeedback, reasonField.nextElementSibling);
                    }
                    
                    return; // Stop execution if no reason provided
                }
                
                showToast('info', 'Processing cancellation...');
                
                // Determine which form to submit
                const activeTab = document.querySelector('.tab-pane.active');
                console.log('Active tab for reversal:', activeTab.id);
                
                let form;
                let remarksField;
                
                if (activeTab.id === 'arrears') {
                    form = document.getElementById('arrearsReceivableForm');
                    remarksField = document.getElementById('arrears_remarks');
                    console.log('Submitting arrears form for reversal');
                } else {
                    form = document.getElementById('accountReceivableForm');
                    remarksField = document.getElementById('remarks');
                    console.log('Submitting account form for reversal');
                }
                
                if (!form) {
                    throw new Error('Form not found');
                }
                
                // Append the reason to the existing remarks
                if (remarksField) {
                    // Add the reason to the remarks field
                    remarksField.value = remarksField.value + ` - Reason: ${cancellationReason}`;
                }
                
                // Re-enable ALL form elements for submission, not just disabled ones
                console.log('Enabling all form fields for submission...');
                form.querySelectorAll('input, select, textarea, button').forEach(field => {
                    field.disabled = false;
                });
                
                // CRITICAL: Ensure the SIN number field has the correct value and is enabled
                if (activeTab.id === 'arrears') {
                    const sinField = document.getElementById('arrears_serviceInvoiceNo');
                    if (sinField && window.currentTransactionData && window.currentTransactionData.transaction) {
                        sinField.value = window.currentTransactionData.transaction.or_number;
                        sinField.disabled = false;
                        console.log('Set SIN field value to:', sinField.value);
                        console.log('SIN field name attribute:', sinField.name);
                        console.log('SIN field form will submit:', sinField.name + '=' + sinField.value);
                    } else {
                        console.error('SIN field or transaction data not found:', {
                            sinField: !!sinField,
                            currentTransactionData: !!window.currentTransactionData,
                            transaction: window.currentTransactionData?.transaction
                        });
                    }
                }
                
                // Debug: Log all form data that will be submitted
                console.log('=== FORM DATA DEBUG ===');
                const formData = new FormData(form);
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
                console.log('=== END FORM DATA DEBUG ===');
                
                // This is the critical part - bypass any event listeners by directly submitting
                // a copy of the form with all values
                console.log('Form is about to submit:', form.id);
                
                // Add a submission flag to ensure we know the form is actually submitting
                const hiddenFlag = document.createElement('input');
                hiddenFlag.type = 'hidden';
                hiddenFlag.name = 'sin_cancellation';
                hiddenFlag.value = 'true';
                form.appendChild(hiddenFlag);
                
                // Close modal before submitting to prevent interference
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
                
                // Force the form to submit after a brief delay to ensure modal is fully closed
                setTimeout(function() {
                    console.log('Submitting form now...');
                    form.submit();
                }, 100);
                
            } catch (error) {
                console.error('Error during reversal confirmation:', error);
                showToast('error', 'Error processing reversal: ' + error.message);
            }
        });
    } else {
        console.error('Confirm button not found');
        showToast('error', 'UI error: Confirmation button not found');
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