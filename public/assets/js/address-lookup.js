// public/js/address-lookup.js

class AddressLookup {
    constructor() {
        this.addressInputs = {
            resident: document.getElementById('resident_addressId'),
            vehicle: document.getElementById('vehicle_addressId')
        };
        this.addressDisplays = {
            resident: document.getElementById('resident_address'),
            vehicle: document.getElementById('vehicle_address')
        };
        this.membersNames = {
            resident: document.getElementById('resident_membersName'),
            vehicle: document.getElementById('vehicle_membersName')
        };
        this.tenantSpas = {
            resident: document.getElementById('resident_tenantSpa'),
            vehicle: document.getElementById('vehicle_tenantSpa')
        };

        this.activeTab = 'resident';
        this.debounceTimer = null;
        this.isLoading = false;
        this.dropdownContainers = {};

        this.setupDropdownContainers();
        this.removeInputRestrictions();
        this.setupEventListeners();
    }



    setupDropdownContainers() {
        for (const tab of ['resident', 'vehicle']) {
            const container = document.createElement('div');
            container.className = 'address-dropdown';
            this.addressInputs[tab].parentNode.style.position = 'relative';
            this.addressInputs[tab].parentNode.appendChild(container);
            this.dropdownContainers[tab] = container;
        }
    }

    // New method to explicitly remove any input restrictions
    removeInputRestrictions() {
        for (const tab of ['resident', 'vehicle']) {
            const input = this.addressInputs[tab];
            if (input) {
                // Remove any attributes that might restrict input
                input.removeAttribute('pattern');
                input.removeAttribute('inputmode');
                
                // Clone and replace to remove any existing event listeners
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                // Update our reference to the new element
                this.addressInputs[tab] = newInput;
            }
        }
    }
    
    setupEventListeners() {
        // Address input handlers for both tabs
        for (const tab of ['resident', 'vehicle']) {
            this.addressInputs[tab].addEventListener('input', (e) => {
                this.activeTab = tab;
                this.handleAddressInput(e);
            });

            this.addressInputs[tab].addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e, tab);
            });
        }

        // Tab change listener
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', (e) => {
                this.activeTab = e.target.getAttribute('aria-controls') === 'vehicle' ? 'vehicle' : 'resident';
            });
        });
        
        this.addressInput.addEventListener('input', () => {
            clearTimeout(this.debounceTimer);
            const query = this.addressInput.value.trim();

            if (query.length < 2) {
                this.hideDropdown();
                return;
            }

            this.showLoading();
            this.debounceTimer = setTimeout(() => this.searchAddress(query), 300);
        });

        this.addressInput.addEventListener('input', (e) => {
            const value = e.target.value;
            
            // Limit to 5 characters but allow any character type
            if (value.length > 5) {
                e.target.value = value.slice(0, 5);
                return;
            }

            // Translate address if 5 characters entered
            if (value.length === 5) {
                const formattedAddress = this.translateAddressId(value);
                this.updateAddressFields(formattedAddress);
            } else {
                this.updateAddressFields('');
            }

            // Continue with existing dropdown logic
            clearTimeout(this.debounceTimer);
            if (value.length >= 2) {
                this.showLoading();
                this.debounceTimer = setTimeout(() => this.searchAddress(value), 300);
            } else {
                this.hideDropdown();
            }
        });


        // Keyboard navigation
        this.addressInput.addEventListener('keydown', (e) => {
            if (!this.dropdownContainer.style.display || this.dropdownContainer.style.display === 'none') {
                return;
            }

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
                    if (activeItem) {
                        activeItem.click();
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.hideDropdown();
                    break;
            }
        });
    }

    handleAddressInput(e) {
        const value = e.target.value;
        
        // Limit to 5 characters but allow any character type
        if (value.length > 5) {
            e.target.value = value.slice(0, 5);
            return;
        }

        // Sync inputs and handle address translation
        this.syncAddressInputs(value);
        
        if (value.length === 5) {
            const formattedAddress = this.translateAddressId(value);
            this.updateAddressFields(formattedAddress);
        } else {
            this.updateAddressFields('');
        }

        // Handle search
        clearTimeout(this.debounceTimer);
        if (value.length >= 2) {
            this.showLoading();
            this.debounceTimer = setTimeout(() => this.searchAddress(value), 300);
        } else {
            this.hideDropdowns();
        }
    }

    handleKeyboardNavigation(e, tab) {
        const container = this.dropdownContainers[tab];
        if (container.style.display === 'none') return;
    
        const items = container.querySelectorAll('li');
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
                const activeItem = container.querySelector('li.active');
                const inputValue = this.addressInputs[tab].value;
                
                if (activeItem) {
                    activeItem.click();
                } else if (inputValue.length === 5) {
                    this.handleDirectAddressLookup(inputValue);
                }
                break;
            case 'Escape':
                e.preventDefault();
                this.hideDropdowns();
                break;
        }
    }

    async handleDirectAddressLookup(addressId) {
        try {
            // First check local results
            const localMatch = Array.from(this.dropdownContainers[this.activeTab].querySelectorAll('li'))
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
            this.updateAddressFields('');
            this.updateMemberInfo({});
            this.showToastNotification('error', 'Address ID not found');
        }
    }

    syncAddressInputs(value) {
        // Update address ID in both tabs
        for (const tab of ['resident', 'vehicle']) {
            if (this.addressInputs[tab].value !== value) {
                this.addressInputs[tab].value = value;
            }
        }
    }

    navigateList(currentIndex, direction, items) {
        items.forEach(item => item.classList.remove('active'));
        
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = items.length - 1;
        if (newIndex >= items.length) newIndex = 0;
        
        items[newIndex].classList.add('active');
        items[newIndex].scrollIntoView({ block: 'nearest' });
    }

    showLoading() {
        const container = this.dropdownContainers[this.activeTab];
        container.innerHTML = `
            <div class="dropdown-loading">
                <div class="loading-spinner"></div>
                <span>Searching addresses...</span>
            </div>
        `;
        container.style.display = 'block';
        this.isLoading = true;
    }

    hideDropdowns() {
        Object.values(this.dropdownContainers).forEach(container => {
            container.style.display = 'none';
        });
        this.isLoading = false;
    }


    translateAddressId(addressId) {
        // Modified to handle alphanumeric address IDs
        try {
            // Check if it follows the standard numeric format (for backward compatibility)
            if (/^\d{5}$/.test(addressId)) {
                const phase = addressId[0];
                const block = addressId.substring(1, 3);
                const lot = addressId.substring(3, 5);
                return `Phase ${phase} Block ${block} Lot ${lot}`;
            } 
            // For alphanumeric IDs, just return the ID as is or apply custom formatting
            // This is a placeholder - adjust based on your specific address format requirements
            return `Address ID: ${addressId}`;
        } catch (error) {
            console.error('Error translating address ID:', error);
            return addressId; // Return the original ID if translation fails
        }
    }

    updateAddressFields(formattedAddress) {
        // Update address fields in both tabs
        for (const tab of ['resident', 'vehicle']) {
            if (this.addressDisplays[tab]) {
                this.addressDisplays[tab].value = formattedAddress;
            }
        }
    }

    updateMemberInfo(memberData) {
        // Update member information in both tabs
        for (const tab of ['resident', 'vehicle']) {
            if (this.membersNames[tab]) {
                this.membersNames[tab].value = memberData?.mem_name || '';
            }
            if (this.tenantSpas[tab]) {
                this.tenantSpas[tab].value = memberData?.mem_SPA_Tenant || '';
            }
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
        if (!addresses.length) {
            this.showError('No addresses found');
            return;
        }

        const container = this.dropdownContainers[this.activeTab];
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
                this.hideDropdowns();
            });
            li.addEventListener('mouseenter', () => {
                ul.querySelectorAll('li').forEach(item => item.classList.remove('active'));
                li.classList.add('active');
            });
            ul.appendChild(li);
        });
    
        // Clear previous contents and append the new list
        container.innerHTML = '';
        container.appendChild(ul);
        container.style.display = 'block';
        
        // Add active class to first item for keyboard navigation
        const firstItem = ul.querySelector('li');
        if (firstItem) {
            firstItem.classList.add('active');
        }
    }


    async selectAddress(address) {
        try {
            // Format the address ID immediately upon selection
            const formattedAddress = this.translateAddressId(address.mem_add_id);
            this.updateAddressFields(formattedAddress);
            this.syncAddressInputs(address.mem_add_id);
    
            // If we already have member data (from direct lookup), use it
            if (address.memberData) {
                this.populateForm(address.memberData);
                this.showToastNotification('success', 'Address data loaded successfully');
                return;
            }
    
            // Otherwise fetch member details
            const response = await fetch(`/residents/get-member-details/${address.mem_id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            this.populateForm(data);
            this.hideDropdowns();
            this.showToastNotification('success', 'Member details loaded successfully');
        } catch (error) {
            console.error('Error fetching member details:', error);
            this.showError('Error loading member details');
            this.showToastNotification('error', 'Failed to load member details');
        }
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
        const container = this.dropdownContainers[this.activeTab];
        container.innerHTML = `
            <div class="dropdown-error">
                <span class="error-icon">⚠️</span>
                <span>${message}</span>
            </div>
        `;
        container.style.display = 'block';
        setTimeout(() => this.hideDropdowns(), 3000);
    }
    

    populateForm(data) {
        const { memberSum, memberData, vehicles } = data;
    
        if (!memberSum || !memberData) {
            console.error('Invalid member data received');
            return;
        }
    
        // Sync address ID and formatted address across tabs
        const formattedAddress = this.translateAddressId(memberSum.mem_add_id);
        this.syncAddressInputs(memberSum.mem_add_id);
        this.updateAddressFields(formattedAddress);
        
        // Update member information in both tabs
        this.updateMemberInfo(memberData);
    
        // Update contact info
        const contactNumber = document.getElementById('contactNumber');
        const email = document.getElementById('email');
        if (contactNumber) contactNumber.value = memberData?.mem_mobile || '';
        if (email) email.value = memberData?.mem_email || '';
    
        // Set Member Type
        if (memberData?.mem_typecode !== undefined) {
            const typeRadio = document.querySelector(`input[name="mem_typecode"][value="${memberData.mem_typecode}"]`);
            if (typeRadio) {
                typeRadio.checked = true;
            }
        }
    
        // Populate Resident Information
        for (let i = 1; i <= 10; i++) {
            const residentName = memberData?.[`mem_Resident${i}`];
            const residentRelation = memberData?.[`mem_Relationship${i}`];
    
            const nameInput = document.querySelector(`input[name="residents[${i-1}][name]"]`);
            const relationSelect = document.querySelector(`select[name="residents[${i-1}][relationship]"]`);
    
            if (nameInput) nameInput.value = residentName || '';
            if (relationSelect) relationSelect.value = residentRelation || '';
        }
    
        // Clear and populate vehicle information
        const vehicleRows = document.querySelectorAll('.vehicle-row');
        vehicleRows.forEach(row => {
            // Clear all inputs first
            row.querySelectorAll('input, select').forEach(input => input.value = '');
        });
    
        if (vehicles && vehicles.length > 0) {
            vehicles.forEach((vehicle, index) => {
                if (index < vehicleRows.length) {
                    const row = vehicleRows[index];
                    
                    // Map the fields correctly
                    const fieldMappings = {
                        'car_sticker': 'car_sticker',
                        'vehicle_type': 'vehicle_type',
                        'vehicle_maker': 'vehicle_maker',
                        'vehicle_color': 'vehicle_color',
                        'vehicle_OR': 'vehicle_OR',
                        'vehicle_CR': 'vehicle_CR',
                        'vehicle_plate': 'vehicle_plate'
                    };
    
                    // Set each field value
                    Object.entries(fieldMappings).forEach(([dbField, formField]) => {
                        const input = row.querySelector(`input[name="vehicles[${index}][${formField}]"]`);
                        if (input && vehicle[dbField] !== undefined) {
                            input.value = vehicle[dbField];
                        }
                    });
    
                    // Set vehicle status (0 = active, 1 = inactive)
                    const statusSelect = row.querySelector(`select[name="vehicles[${index}][vehicle_active]"]`);
                    if (statusSelect) {
                        statusSelect.value = vehicle.vehicle_active.toString();
                    }
                }
            });
        }
    
        // Set remarks for both fields
        const memberRemarks = document.getElementById('member_remarks');
        const vehicleRemarks = document.getElementById('vehicle_remarks');
        
        if (memberRemarks) {
            memberRemarks.value = memberData?.mem_remarks || '';
        }
        
        if (vehicleRemarks && vehicles && vehicles.length > 0) {
            // Using the remarks from the first vehicle record
            vehicleRemarks.value = vehicles[0]?.remarks || '';
        }
    
        // Trigger change events
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        });

        this.showToastNotification('success', 'Member data loaded successfully');
    }    
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    new AddressLookup();
    
    // Explicitly ensure the Address ID inputs allow alphanumeric input
    document.querySelectorAll('.address-id-input').forEach(input => {
        input.removeAttribute('pattern');
        input.removeAttribute('inputmode');
    });
});

// Make showToast function available globally
window.showToast = function(type, message) {
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
};