// receivable-address-lookup.js
// Address lookup functionality for HOA Monthly Dues tab in Account Receivable

// Utility function to format numbers with commas
function formatNumberWithCommas(number) {
    if (number === null || number === undefined || isNaN(number)) {
        return '0.00';
    }
    const num = parseFloat(number);
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

class ArrearsAddressLookup {
    constructor() {
        // Initialize element references
        this.addressInput = document.getElementById('arrears_addressId');
        this.memberNameField = document.getElementById('memberName');
        this.memberAddressField = document.getElementById('memberAddress');
        this.arrearsAmountField = document.getElementById('arrears_amount');
        this.lastPaydateField = document.getElementById('lastPaydate');
        this.lastPaymentField = document.getElementById('lastPayment');
        this.lastORField = document.getElementById('lastOR');
        this.lookupStatusElem = document.getElementById('lookupStatus');

        // Initialize state variables
        this.debounceTimer = null;
        this.isLoading = false;
        this.currentArrears = 0; // Store the current member's arrears principal
        this.currentInterest = 0; // Store the current member's interest
        this.currentTotalArrears = 0; // Store the current member's total arrears
        this.dropdownContainer = null;

        // Setup functionality if the address input exists
        if (this.addressInput) {
            this.setupDropdownContainer();
            this.setupEventListeners();
            this.ensureAddressValidation();
            this.setupPaymentHistoryButton();
        }
    }

    setupDropdownContainer() {
        // Create the dropdown container
        const container = document.createElement('div');
        container.className = 'address-dropdown';
        this.addressInput.parentNode.style.position = 'relative';
        this.addressInput.parentNode.appendChild(container);
        this.dropdownContainer = container;
    }

    // Add this new method for address validation
    ensureAddressValidation() {
        if (this.addressInput) {
            // Remove any attributes that might restrict input
            this.addressInput.removeAttribute('pattern');
            this.addressInput.removeAttribute('inputmode');
            this.addressInput.setAttribute('type', 'text');
            this.addressInput.setAttribute('autocomplete', 'off');
            
            // Add validation for first character to be only 1 or 2
            this.addressInput.addEventListener('keypress', (e) => {
                // Get current input value and cursor position
                const value = e.target.value;
                const position = e.target.selectionStart;
                
                // If typing at the first position, only allow 1 or 2
                if (position === 0) {
                    return e.key === '1' || e.key === '2';
                }
                
                // Allow other characters for other positions
                return true;
            });
            
            // Additional validation on input event
            this.addressInput.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Limit to 5 characters
                if (value.length > 5) {
                    e.target.value = value.slice(0, 5);
                }
                
                // First character validation
                if (value.length > 0 && value[0] !== '1' && value[0] !== '2') {
                    // Remove invalid first character
                    e.target.value = value.substring(1);
                }
            }, true); // Using capture phase to run before other handlers
        }
    }

    setupEventListeners() {
        // Address input handlers
        this.addressInput.addEventListener('input', (e) => {
            this.handleAddressInput(e);
        });

        this.addressInput.addEventListener('keydown', (e) => {
            this.handleKeyboardNavigation(e);
        });

        // Handle clicks outside the dropdown to close it
        document.addEventListener('click', (e) => {
            if (!this.addressInput.contains(e.target) && !this.dropdownContainer.contains(e.target)) {
                this.hideDropdown();
            }
        });
    }

    handleAddressInput(e) {
        const value = e.target.value;
        
        // If address is exactly 5 characters, try to format it and search directly
        if (value.length === 5) {
            const formattedAddress = this.translateAddressId(value);
            this.memberAddressField.value = formattedAddress;
            
            // Try to get member data directly
            this.handleDirectAddressLookup(value);
        } else {
            // Clear member address field if not 5 chars
            this.memberAddressField.value = '';
        }

        // For every input, debounce the dropdown search
        clearTimeout(this.debounceTimer);
        if (value.length >= 2) {
            this.showLoading();
            this.debounceTimer = setTimeout(() => this.searchAddress(value), 300);
        } else {
            this.hideDropdown();
        }
    }

    handleKeyboardNavigation(e) {
        if (!this.dropdownContainer || this.dropdownContainer.style.display === 'none') return;
    
        const items = this.dropdownContainer.querySelectorAll('li');
        const currentIndex = Array.from(items).findIndex(item => item.classList.contains('active'));
    
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.navigateList(currentIndex, 1, items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.navigateList(currentIndex, -1, items);
                break;
            case 'Enter':
                e.preventDefault();
                const activeItem = this.dropdownContainer.querySelector('li.active');
                const inputValue = this.addressInput.value;
                
                if (activeItem) {
                    activeItem.click();
                } else if (inputValue.length === 5) {
                    this.handleDirectAddressLookup(inputValue);
                }
                break;
            case 'Escape':
                e.preventDefault();
                this.hideDropdown();
                break;
        }
    }

    async handleDirectAddressLookup(addressId) {
        try {
            // First check local results
            const localMatch = Array.from(this.dropdownContainer.querySelectorAll('li'))
                .find(li => li.querySelector('.address-id').textContent === addressId);
    
            if (localMatch) {
                localMatch.click();
                return;
            }
    
            // If not found locally, make API call
            const response = await fetch(`/residents/validate-address/${addressId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Address not found');
            const addressData = await response.json();
    
            this.selectAddress({
                mem_add_id: addressId,
                mem_id: addressData.mem_id
            });
            this.showToastNotification('success', 'Address found and loaded successfully');
        } catch (error) {
            console.error('Direct lookup error:', error);
            this.showError('Address ID not found');
            this.clearFormFields();
            this.showToastNotification('error', 'Address ID not found');
        }
    }

    translateAddressId(addressId) {
        try {
            // Make sure we have a 5-character string
            if (!addressId || addressId.length !== 5) {
                return '';
            }
            
            // Extract parts: first digit is phase, next 2 chars are block, last 2 chars are lot
            const phase = addressId.substring(0, 1);
            const block = addressId.substring(1, 3);
            const lot = addressId.substring(3, 5);
            
            // Format the address with proper labels
            return `Ph. ${phase} Blk. ${block} Lot ${lot}`;
        } catch (error) {
            console.error('Error translating address ID:', error);
            return addressId; // Return the original ID if translation fails
        }
    }
    
    navigateList(currentIndex, direction, items) {
        if (!items || !items.length) return;
        
        items.forEach(item => item.classList.remove('active'));
        
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = items.length - 1;
        if (newIndex >= items.length) newIndex = 0;
        
        items[newIndex].classList.add('active');
        items[newIndex].scrollIntoView({ block: 'nearest' });
    }

    showLoading() {
        if (!this.dropdownContainer) return;
        
        this.dropdownContainer.innerHTML = `
            <div class="dropdown-loading">
                <div class="loading-spinner"></div>
                <span>Searching addresses...</span>
            </div>
        `;
        this.dropdownContainer.style.display = 'block';
        this.isLoading = true;
    }

    hideDropdown() {
        if (this.dropdownContainer) {
            this.dropdownContainer.style.display = 'none';
        }
        this.isLoading = false;
    }

    showLookupSuccess() {
        if (!this.lookupStatusElem) return;
        
        this.lookupStatusElem.innerHTML = `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Member data loaded</span>`;
        this.lookupStatusElem.classList.remove('d-none');
        this.lookupStatusElem.classList.add('fade-in');
        
        // Hide after 3 seconds
        setTimeout(() => {
            this.lookupStatusElem.classList.add('d-none');
        }, 3000);
    }
    clearFormFields() {
        // Clear all form fields in the Member Arrears Information section
        if (this.memberNameField) this.memberNameField.value = '';
        if (this.memberAddressField) this.memberAddressField.value = '';
        if (this.arrearsAmountField) this.arrearsAmountField.value = '';
        if (this.lastPaydateField) this.lastPaydateField.value = '';
        if (this.lastPaymentField) this.lastPaymentField.value = '';
        if (this.lastORField) this.lastORField.value = '';
        
        // Disable the payment history button when clearing form
        const viewPaymentHistoryBtn = document.getElementById('viewPaymentHistory');
        if (viewPaymentHistoryBtn) {
            viewPaymentHistoryBtn.disabled = true;
        }
    }

    async searchAddress(query) {
        try {
            const response = await fetch(`/residents/search-address?query=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const addresses = await response.json();
            this.displayResults(addresses);
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Error searching addresses');
            this.showToastNotification('error', 'Failed to search addresses');
        }
    }

    displayResults(addresses) {
        if (!addresses || !addresses.length) {
            this.showError('No addresses found');
            return;
        }

        if (!this.dropdownContainer) return;
        
        const ul = document.createElement('ul');
        ul.className = 'address-list';

        addresses.forEach(address => {
            const li = document.createElement('li');
            const formattedAddress = this.translateAddressId(address.mem_add_id);
            li.innerHTML = `
                <div class="d-flex">
                    <span class="address-id">${address.mem_add_id}</span>
                    <span class="member-name">${address.mem_name || 'Unnamed'}</span>
                </div>
                <span class="address-formatted">${formattedAddress}</span>
            `;
            li.addEventListener('click', () => {
                this.selectAddress(address);
                this.hideDropdown();
            });
            li.addEventListener('mouseenter', () => {
                ul.querySelectorAll('li').forEach(item => item.classList.remove('active'));
                li.classList.add('active');
            });
            ul.appendChild(li);
        });
    
        // Clear previous contents and append the new list
        this.dropdownContainer.innerHTML = '';
        this.dropdownContainer.appendChild(ul);
        this.dropdownContainer.style.display = 'block';
        
        // Add active class to first item for keyboard navigation
        const firstItem = ul.querySelector('li');
        if (firstItem) {
            firstItem.classList.add('active');
        }
    }

    async selectAddress(address) {
        try {
            if (!address) return;
            
            // Store original values if in reversal mode
            let originalPayorName = null;
            let originalAddressId = null;
            
            if (window.isReversalMode) {
                originalPayorName = document.getElementById('arrears_receivedFrom').value;
                originalAddressId = document.getElementById('arrears_addressId').value;
            }
            
            // Format the address ID immediately upon selection
            const formattedAddress = this.translateAddressId(address.mem_add_id);
            this.addressInput.value = address.mem_add_id;
            this.memberAddressField.value = formattedAddress;
    
            // Fetch member details
            const response = await fetch(`/residents/get-member-details/${address.mem_id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            this.populateForm(data);
            
            // Restore original values if in reversal mode
            if (window.isReversalMode) {
                document.getElementById('arrears_receivedFrom').value = originalPayorName;
                
                // In reversal mode, we want to keep the addressId from the member lookup
                // because it's the correct one from the database, not from the transaction
                // Only restore if original was different from loaded one and not "Loading..."
                if (originalAddressId && originalAddressId !== "Loading..." && 
                    originalAddressId !== address.mem_add_id) {
                    console.log('Address ID from member lookup differs from original, keeping the lookup value');
                }
                
                window.isReversalMode = false; // Reset the flag
            }
            
            this.hideDropdown();
            this.showLookupSuccess();
            this.showToastNotification('success', 'Member details loaded successfully');
        } catch (error) {
            console.error('Error fetching member details:', error);
            this.showError('Error loading member details');
            this.showToastNotification('error', 'Failed to load member details');
        }
    }

    selectAddressById(addressId, memberId) {
        console.log('Direct address selection by ID:', addressId, 'Member ID:', memberId);
        
        if (!addressId) {
            console.error('No address ID provided for selection');
            return;
        }
        
        // Format the address for display
        const formattedAddress = this.translateAddressId(addressId);
        this.addressInput.value = addressId;
        this.memberAddressField.value = formattedAddress;
        
        // Get the member details directly using the member ID
        this.fetchMemberDetails(memberId)
            .then(data => {
                console.log('Member details fetched successfully for reversal');
                this.populateForm(data);
                
                // If in reversal mode, restore the payor name (this will be handled by the calling function)
                this.hideDropdown();
                this.showLookupSuccess();
            })
            .catch(error => {
                console.error('Error fetching member details for reversal:', error);
                this.showToastNotification('error', 'Failed to load member details');
            });
    }
    
    // Add this helper method as well
    async fetchMemberDetails(memberId) {
        const response = await fetch(`/residents/get-member-details/${memberId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    
        if (!response.ok) throw new Error('Failed to fetch member details');
        return await response.json();
    }    

    setupPaymentHistoryButton() {
        const paymentHistoryBtn = document.getElementById('viewPaymentHistory');
        
        if (paymentHistoryBtn) {
            paymentHistoryBtn.addEventListener('click', () => {
                this.openPaymentHistoryModal();
            });
        }
    }
    
    openPaymentHistoryModal() {
        // Get the current member ID from our lookup
        const addressId = this.addressInput?.value;

        // If no address is selected, show an error and return
        if (!addressId) {
            showToast('error', 'Please select a member address first');
            return;
        }

        // Populate the modal with member info - use stored values for consistency
        document.getElementById('modalMemberName').textContent = this.memberNameField?.value || '-';
        document.getElementById('modalMemberAddress').textContent = this.memberAddressField?.value || '-';
        document.getElementById('modalCurrentArrears').textContent = '₱ ' + formatNumberWithCommas(this.currentArrears);
        document.getElementById('modalInterest').textContent = '₱ ' + formatNumberWithCommas(this.currentInterest);
        document.getElementById('modalTotalArrears').textContent = '₱ ' + formatNumberWithCommas(this.currentTotalArrears);
        
        // Show loading state
        document.getElementById('paymentHistoryLoading').classList.remove('d-none');
        document.getElementById('paymentHistoryTableContainer').classList.add('d-none');
        document.getElementById('paymentHistoryError').classList.add('d-none');
        document.getElementById('noPaymentHistory').classList.add('d-none');
        
        // Create and show the modal
        const modal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
        modal.show();
        
        // Get the member ID from our last REST call, stored in a data attribute
        // We need to first find the member ID using the address ID
        this.validateAddress(addressId)
            .then(memberId => {
                // Now fetch the payment history
                return this.fetchPaymentHistory(memberId);
            })
            .then(paymentHistory => {
                // Populate the table with the payment history
                this.populatePaymentHistoryTable(paymentHistory);
            })
            .catch(error => {
                console.error('Error fetching payment history:', error);
                document.getElementById('paymentHistoryLoading').classList.add('d-none');
                document.getElementById('paymentHistoryError').classList.remove('d-none');
            });
    }
    
    async validateAddress(addressId) {
        try {
            const response = await fetch(`/residents/validate-address/${addressId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Address not found');
            const addressData = await response.json();
            
            return addressData.mem_id;
        } catch (error) {
            console.error('Error validating address:', error);
            throw error;
        }
    }
    
    // Add this method to the ArrearsAddressLookup class
    async fetchPaymentHistory(memberId) {
        try {
            const response = await fetch(`/accounts/receivables/payment-history/${memberId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Failed to fetch payment history');
            const result = await response.json();
            
            // Sort the payment history by date in descending order (newest first)
            if (result.data && Array.isArray(result.data)) {
                result.data.sort((a, b) => {
                    const dateA = new Date(a.ar_date);
                    const dateB = new Date(b.ar_date);
                    // If dates are equal, use transaction number as secondary sort
                    if (dateB.getTime() === dateA.getTime()) {
                        return parseInt(b.ar_transno) - parseInt(a.ar_transno);
                    }
                    return dateB.getTime() - dateA.getTime();
                });
            }
            
            return result.data;
        } catch (error) {
            console.error('Error fetching payment history:', error);
            throw error;
        }
    }
    
    populatePaymentHistoryTable(paymentHistory) {
        // Hide loading, show table
        document.getElementById('paymentHistoryLoading').classList.add('d-none');
        
        // If no records, show no records message
        if (!paymentHistory || paymentHistory.length === 0) {
            document.getElementById('noPaymentHistory').classList.remove('d-none');
            return;
        }
        
        // Get the table body
        const tableBody = document.querySelector('#paymentHistoryTable tbody');
        tableBody.innerHTML = '';
        
        // Populate the table
        paymentHistory.forEach(payment => {
            const row = document.createElement('tr');
            
            // Format the date
            const paymentDate = new Date(payment.ar_date);
            const formattedDate = paymentDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Format the amounts with commas - handle edge cases to prevent ₱NaN
            let formattedAmount = 'N/A';
            let formattedBalance = 'N/A';

            // Safely format amount with commas
            if (payment.ar_amount !== null && payment.ar_amount !== undefined) {
                const amountValue = parseFloat(payment.ar_amount);
                if (!isNaN(amountValue)) {
                    formattedAmount = '₱ ' + formatNumberWithCommas(amountValue);
                }
            }

            // Safely format balance with commas
            if (payment.arrear_bal !== null && payment.arrear_bal !== undefined) {
                const balanceValue = parseFloat(payment.arrear_bal);
                if (!isNaN(balanceValue)) {
                    formattedBalance = '₱ ' + formatNumberWithCommas(balanceValue);
                }
            }
            
            row.innerHTML = `
                <td class="text-start">${formattedDate}</td>
                <td class="text-start">${payment.or_number}</td>
                <td class="text-start">${payment.payor_name || '-'}</td>
                <td class="text-start">${payment.acct_description || 'N/A'}</td>
                <td class="text-start">${formattedAmount}</td>
                <td class="text-start">${formattedBalance}</td>
                <td class="text-start">${payment.ar_remarks || '-'}</td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // Show the table container
        document.getElementById('paymentHistoryTableContainer').classList.remove('d-none');
    }        
    
    populateForm(data) {
        if (!data) {
            console.error('No data provided to populate form');
            return;
        }
        
        const { memberSum, memberData } = data;
    
        if (!memberSum || !memberData) {
            console.error('Invalid member data received');
            return;
        }

        // Debug to see what data we're getting
        console.log('Member Sum Data:', memberSum);
        console.log('Member Data:', memberData);
    
        // Populate Member Name
        if (this.memberNameField) {
            this.memberNameField.value = memberData.mem_name || '';
        }
        
        // Populate Member Address (already set in selectAddress, but just to be sure)
        if (this.memberAddressField) {
            const formattedAddress = this.translateAddressId(memberSum.mem_add_id);
            this.memberAddressField.value = formattedAddress;
        }
        
        // Populate Arrears Amount - Accessing arrear (singular) from memberSum
        // Store the arrears value for use in payment history modal
        this.currentArrears = memberSum.arrear !== undefined ? memberSum.arrear : 0;
        console.log('Arrear value:', this.currentArrears);

        // Also populate the field if it exists (even if hidden, we might use it elsewhere)
        if (this.arrearsAmountField) {
            this.arrearsAmountField.value = formatNumberWithCommas(this.currentArrears);
        }

        // Store the total arrears value for use in payment history modal
        this.currentTotalArrears = memberSum.arrear_total !== undefined ? memberSum.arrear_total : 0;
        console.log('Total arrears value:', this.currentTotalArrears);

        const totalArrearsField = document.getElementById('total_arrears');
        if (totalArrearsField) {
            // Use formatting with commas
            totalArrearsField.value = formatNumberWithCommas(this.currentTotalArrears);
        }

        // Store the interest value for use in payment history modal
        this.currentInterest = memberSum.arrear_interest !== undefined ? memberSum.arrear_interest : 0;
        console.log('Interest value:', this.currentInterest);
        
        // Populate Last Payment Date
        if (this.lastPaydateField) {
            // Log what we're getting for last_paydate
            console.log('Last paydate value:', memberSum.last_paydate);
            
            let lastPaydate = memberSum.last_paydate || '';
            // Format the date if it exists
            if (lastPaydate) {
                try {
                    const dateObj = new Date(lastPaydate);
                    lastPaydate = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch (e) {
                    console.error('Error formatting date:', e);
                    // Fallback to the original value
                    lastPaydate = memberSum.last_paydate;
                }
            }
            this.lastPaydateField.value = lastPaydate;
        }
        
        // Populate Last Payment Amount
        if (this.lastPaymentField) {
            // Log what we're getting for last_payamount
            console.log('Last payment amount value:', memberSum.last_payamount);

            // Format the last payment amount as currency with commas
            const lastPayment = memberSum.last_payamount !== undefined ? memberSum.last_payamount : 0;

            // Use formatting with commas
            this.lastPaymentField.value = '₱ ' + formatNumberWithCommas(lastPayment);
        }
        
        if (this.lastORField) {
            // Log what we're getting for last OR
            console.log('Last OR value:', memberSum.last_salesinvoice);
            
            // Set the value of the Last OR field
            this.lastORField.value = memberSum.last_or || 'N/A';
        }
        // Also update the arrears_receivedFrom field to match the member name
        const receivedFromField = document.getElementById('arrears_receivedFrom');
        if (receivedFromField && memberData.mem_name && !window.isReversalMode) {
            receivedFromField.value = memberData.mem_name;
        }

        // Enable the payment history button when we have a valid member
        const viewPaymentHistoryBtn = document.getElementById('viewPaymentHistory');
        if (viewPaymentHistoryBtn) {
            viewPaymentHistoryBtn.disabled = false;
        }

        // Trigger change events for any dependent logic
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        // Make sure CASH is selected and trigger its change event specifically
        const cashRadio = document.getElementById('arrears_cash');
        if (cashRadio) {
            cashRadio.checked = true;
            cashRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Hide reference field explicitly
        const referenceContainer = document.getElementById('arrears_reference')?.closest('.col-md-4');
        if (referenceContainer) {
            referenceContainer.style.display = 'none';
        }
        
        // redirect focus after address lookup
        setTimeout(() => {
            // Focus on SIN field and handle masked input properly
            const sinField = document.getElementById('arrears_serviceInvoiceNo');
            if (sinField) {
                sinField.focus();
                // If it's a masked input, select all for easy replacement
                if (sinField.classList.contains('sin-masked-input')) {
                    sinField.select();
                }
            }
        }, 100);
    }
    
    showToastNotification(type, message) {
        // Check if showToast function exists in global scope
        if (typeof window.showToast === 'function') {
            window.showToast(type, message);
        } else if (typeof showToast === 'function') {
            showToast(type, message);
        } else {
            // Fallback: try to find the toast elements and show them directly
            const toastElement = document.getElementById(type + 'Toast');
            const messageElement = document.getElementById(type + 'Message');
            
            if (toastElement && messageElement && typeof bootstrap !== 'undefined') {
                messageElement.textContent = message;
                const bsToast = new bootstrap.Toast(toastElement, {
                    animation: true,
                    autohide: true,
                    delay: 4000
                });
                bsToast.show();
            } else {
                // Last resort: console message
                console.log(`${type.toUpperCase()} NOTIFICATION: ${message}`);
            }
        }
    }

    showError(message) {
        if (!this.dropdownContainer) return;
        
        this.dropdownContainer.innerHTML = `
            <div class="dropdown-error">
                <span class="error-icon">⚠️</span>
                <span>${message}</span>
            </div>
        `;
        this.dropdownContainer.style.display = 'block';
        setTimeout(() => this.hideDropdown(), 3000);
    }
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    // Make sure we're on a page with the arrears tab before initializing
    const arrearsTab = document.getElementById('arrears-tab');
    
    if (arrearsTab) {
        // Create the instance
        const arrearsAddressLookup = new ArrearsAddressLookup();
        
        // Expose the instance globally for other scripts to use
        window.arrearsAddressLookup = arrearsAddressLookup;
        
        // Add tab change listener to re-focus the address ID input when the arrears tab is shown
        arrearsTab.addEventListener('shown.bs.tab', function() {
            const addressIdField = document.getElementById('arrears_addressId');
            if (addressIdField) {
                addressIdField.focus();
            }
        });
    }
});