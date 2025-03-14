// receivable-address-lookup.js
// Address lookup functionality for HOA Monthly Dues tab in Account Receivable

class ArrearsAddressLookup {
    constructor() {
        // Initialize element references
        this.addressInput = document.getElementById('arrears_addressId');
        this.memberNameField = document.getElementById('memberName');
        this.memberAddressField = document.getElementById('memberAddress');
        this.arrearsAmountField = document.getElementById('arrears_amount'); // Updated ID to match HTML
        this.lastPaydateField = document.getElementById('lastPaydate');
        this.lastPaymentField = document.getElementById('lastPayment');
        this.lookupStatusElem = document.getElementById('lookupStatus');

        // Initialize state variables
        this.debounceTimer = null;
        this.isLoading = false;
        this.dropdownContainer = null;

        // Setup functionality if the address input exists
        if (this.addressInput) {
            this.setupDropdownContainer();
            this.setupEventListeners();
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
            this.hideDropdown();
            this.showLookupSuccess();
            this.showToastNotification('success', 'Member details loaded successfully');
        } catch (error) {
            console.error('Error fetching member details:', error);
            this.showError('Error loading member details');
            this.showToastNotification('error', 'Failed to load member details');
        }
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
        if (this.arrearsAmountField) {
            // Log what we're getting for arrear
            console.log('Arrear value:', memberSum.arrear);
            
            // Format the arrears amount as currency
            const arrearValue = memberSum.arrear !== undefined ? memberSum.arrear : 0;
            
            // Use a simple toString approach first to ensure we see something
            this.arrearsAmountField.value = '₱ ' + parseFloat(arrearValue).toFixed(2);
            
            // Also try alternative formatting
            /* 
            this.arrearsAmountField.value = parseFloat(arrearValue).toLocaleString('en-US', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            });
            */
        }
        
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
            
            // Format the last payment amount as currency
            const lastPayment = memberSum.last_payamount !== undefined ? memberSum.last_payamount : 0;
            
            // Use simple formatting first
            this.lastPaymentField.value = '₱ ' + parseFloat(lastPayment).toFixed(2);
            
            /*
            this.lastPaymentField.value = parseFloat(lastPayment).toLocaleString('en-US', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            });
            */
        }
        
        // Also update the arrears_receivedFrom field to match the member name
        const receivedFromField = document.getElementById('arrears_receivedFrom');
        if (receivedFromField && memberData.mem_name) {
            receivedFromField.value = memberData.mem_name;
        }
        
        // Trigger change events for any dependent logic
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        });
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
        const arrearsAddressLookup = new ArrearsAddressLookup();
        
        // Add tab change listener to re-focus the address ID input when the arrears tab is shown
        arrearsTab.addEventListener('shown.bs.tab', function() {
            const addressIdField = document.getElementById('arrears_addressId');
            if (addressIdField) {
                addressIdField.focus();
            }
        });
    }
});