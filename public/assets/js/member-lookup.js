// public/assets/js/member-lookup.js

class MemberLookup {
    constructor() {
        console.log('MemberLookup: Initializing component');
        
        // Initialize elements
        this.lookupBtn = document.getElementById('memberLookupBtn');
        this.searchInput = document.getElementById('memberNameSearch');
        this.resultsContainer = document.getElementById('memberSearchResults');
        this.modal = null;
        
        // Log element availability
        console.log('MemberLookup: Elements found:', {
            lookupBtn: !!this.lookupBtn,
            searchInput: !!this.searchInput,
            resultsContainer: !!this.resultsContainer,
            memberLookupModal: !!document.getElementById('memberLookupModal')
        });
        
        // Initialize modal
        if (document.getElementById('memberLookupModal')) {
            try {
                this.modal = new bootstrap.Modal(document.getElementById('memberLookupModal'));
                console.log('MemberLookup: Modal initialized successfully');
            } catch (error) {
                console.error('MemberLookup: Error initializing modal:', error);
            }
        } else {
            console.warn('MemberLookup: Modal element not found in DOM');
        }
        
        // State variables
        this.debounceTimer = null;
        this.isLoading = false;
        
        // Setup event listeners
        this.setupEventListeners();
        console.log('MemberLookup: Initialization complete');
    }
    
    setupEventListeners() {
        // Open modal when lookup button is clicked
        if (this.lookupBtn) {
            this.lookupBtn.addEventListener('click', () => {
                this.openModal();
            });
        }
        
        // Search as user types
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e);
            });
            
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.modal.hide();
                }
            });
        }
    }
    
    openModal() {
        // Clear previous results and search input
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = '';
        }
        
        // Show the modal
        if (this.modal) {
            this.modal.show();
            
            // Focus on search input
            setTimeout(() => {
                if (this.searchInput) {
                    this.searchInput.focus();
                }
            }, 300);
        }
    }
    
    handleSearchInput(e) {
        const value = e.target.value.trim();
        
        clearTimeout(this.debounceTimer);
        
        if (value.length >= 2) {
            this.showLoading();
            this.debounceTimer = setTimeout(() => this.searchMembers(value), 300);
        } else {
            if (this.resultsContainer) {
                this.resultsContainer.innerHTML = '';
            }
        }
    }
    
    showLoading() {
        if (!this.resultsContainer) return;
        
        this.resultsContainer.innerHTML = `
            <div class="d-flex align-items-center mt-3">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Searching...</span>
            </div>
        `;
        this.isLoading = true;
    }
    
    async searchMembers(query) {
        console.log('MemberLookup: Searching for members with query:', query);
        
        try {
            const url = `/residents/search-by-name?query=${encodeURIComponent(query)}`;
            console.log('MemberLookup: Fetching from URL:', url);
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('MemberLookup: Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            if (!response.ok) {
                // Get response text for better error context
                const errorText = await response.text();
                console.error('MemberLookup: Response error details:', {
                    status: response.status,
                    text: errorText
                });
                throw new Error(`Network response error: ${response.status} ${response.statusText}`);
            }
            
            const members = await response.json();
            console.log('MemberLookup: Search results:', {
                count: members.length,
                data: members
            });
            
            this.displayResults(members);
        } catch (error) {
            console.error('MemberLookup: Search error:', error);
            console.error('MemberLookup: Error stack:', error.stack);
            this.showError(`Error searching members: ${error.message}`);
        }
    }
    
    // Function to translate address ID to formatted address
    translateAddressId(addressId) {
        try {
            // Check if it follows the standard numeric format (for backward compatibility)
            if (/^\d{5}$/.test(addressId)) {
                const phase = addressId[0];
                const block = addressId.substring(1, 3);
                const lot = addressId.substring(3, 5);
                return `Phase ${phase} Block ${block} Lot ${lot}`;
            } 
            // For alphanumeric IDs, return a formatted version
            return `Address ID: ${addressId}`;
        } catch (error) {
            console.error('Error translating address ID:', error);
            return addressId; // Return the original ID if translation fails
        }
    }
    
    displayResults(members) {
        if (!this.resultsContainer) return;
        
        this.isLoading = false;
        
        if (!members || !members.length) {
            this.resultsContainer.innerHTML = `
                <div class="alert alert-info mt-3">
                    No members found matching your search.
                </div>
            `;
            return;
        }
        
        // Create a table to display results
        let resultsHtml = `
            <div class="table-responsive mt-3">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Address</th>
                            <th>Member Name</th>
                            <th>Tenant/SPA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        members.forEach(member => {
            // Convert address ID to formatted address
            const formattedAddress = this.translateAddressId(member.mem_add_id);
            
            resultsHtml += `
                <tr>
                    <td>
                        <span class="d-block">${formattedAddress}</span>
                        <small class="text-muted">${member.mem_add_id}</small>
                    </td>
                    <td>${member.mem_name || 'N/A'}</td>
                    <td>${member.mem_SPA_Tenant || 'N/A'}</td>
                    <td>
                        <button type="button" 
                                class="btn btn-sm btn-primary select-member"
                                data-mem-id="${member.mem_id}"
                                data-address-id="${member.mem_add_id}">
                            Select
                        </button>
                    </td>
                </tr>
            `;
        });
        
        resultsHtml += `
                    </tbody>
                </table>
            </div>
        `;
        
        this.resultsContainer.innerHTML = resultsHtml;
        
        // Add event listeners to the select buttons
        this.resultsContainer.querySelectorAll('.select-member').forEach(button => {
            button.addEventListener('click', (e) => {
                const memId = e.target.getAttribute('data-mem-id');
                const addressId = e.target.getAttribute('data-address-id');
                this.selectMember(memId, addressId);
            });
        });
    }
    
    showError(message) {
        if (!this.resultsContainer) return;
        
        this.resultsContainer.innerHTML = `
            <div class="alert alert-danger mt-3">
                ${message}
            </div>
        `;
    }
    
    async selectMember(memId, addressId) {
        console.log('MemberLookup: Member selected', {
            memId: memId,
            addressId: addressId
        });
        
        try {
            // Close the modal
            if (this.modal) {
                console.log('MemberLookup: Hiding modal');
                this.modal.hide();
            } else {
                console.warn('MemberLookup: Modal not available to hide');
            }
            
            // Set address ID in inputs and trigger the input event
            const addressInputs = document.querySelectorAll('.address-id-input');
            console.log('MemberLookup: Found address inputs:', {
                count: addressInputs.length,
                inputs: Array.from(addressInputs).map(el => el.id)
            });
            
            const addressInput = addressInputs[0]; // Get the first one to trigger
            
            if (addressInput) {
                console.log('MemberLookup: Setting address ID and triggering input event');
                
                // Set value for all address inputs
                addressInputs.forEach(input => {
                    input.value = addressId;
                    console.log(`MemberLookup: Set address value for ${input.id || 'unnamed input'}`);
                });
                
                // Focus and trigger input event to load member data
                addressInput.focus();
                console.log('MemberLookup: Dispatching input event to trigger data loading');
                addressInput.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Show success notification
                showToast('success', 'Member found. Loading data...');
            } else {
                console.warn('MemberLookup: No address inputs found for auto-population');
                console.log('MemberLookup: Falling back to direct API call');
                
                // Fallback: direct API call if address input not found
                await this.loadMemberDataDirectly(memId);
            }
        } catch (error) {
            console.error('MemberLookup: Error selecting member:', error);
            console.error('MemberLookup: Error stack:', error.stack);
            showToast('error', `Failed to load member details: ${error.message}`);
        }
    }
    
    async loadMemberDataDirectly(memId) {
        try {
            const response = await fetch(`/residents/get-member-details/${memId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Failed to load member details');
            const data = await response.json();
            
            // Get address ID
            const addressId = data.memberSum?.mem_add_id;
            
            if (addressId) {
                // Populate form fields using the global address lookup function if available
                if (window.addressLookup) {
                    window.addressLookup.selectAddress({
                        mem_id: memId,
                        mem_add_id: addressId
                    });
                    showToast('success', 'Member data loaded successfully');
                } else {
                    // Manual form population if needed
                    this.populateFormManually(data);
                }
            } else {
                showToast('error', 'Member data incomplete');
            }
        } catch (error) {
            console.error('Error loading member data:', error);
            showToast('error', 'Failed to load member data');
        }
    }
    
    // Fallback method if addressLookup is not available
    populateFormManually(data) {
        if (!data || !data.memberSum || !data.memberData) {
            return;
        }
        
        // Basic form population logic
        const { memberSum, memberData } = data;
        
        // Set address fields
        document.querySelectorAll('.address-id-input').forEach(input => {
            input.value = memberSum.mem_add_id;
        });
        
        // Update member information
        document.querySelectorAll('[id$="_membersName"]').forEach(input => {
            input.value = memberData.mem_name || '';
        });
        
        document.querySelectorAll('[id$="_tenantSpa"]').forEach(input => {
            input.value = memberData.mem_SPA_Tenant || '';
        });
        
        // Set contact info
        const contactInput = document.getElementById('contactNumber');
        if (contactInput) contactInput.value = memberData.mem_mobile || '';
        
        const emailInput = document.getElementById('email');
        if (emailInput) emailInput.value = memberData.mem_email || '';
        
        showToast('success', 'Member data loaded');
    }
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('MemberLookup: DOM content loaded, initializing component');
    
    try {
        // Check for required elements before initialization
        const requiredElements = {
            lookupBtn: !!document.getElementById('memberLookupBtn'),
            searchInput: !!document.getElementById('memberNameSearch'),
            resultsContainer: !!document.getElementById('memberSearchResults'),
            modal: !!document.getElementById('memberLookupModal')
        };
        
        console.log('MemberLookup: Required elements check:', requiredElements);
        
        // Check for Bootstrap
        if (typeof bootstrap === 'undefined') {
            console.warn('MemberLookup: Bootstrap not detected! Modal functionality may not work.');
        } else {
            console.log('MemberLookup: Bootstrap detected:', bootstrap.version || 'version unknown');
        }
        
        // Initialize the component
        const memberLookup = new MemberLookup();
        
        // Make the instance available globally
        window.memberLookup = memberLookup;
        console.log('MemberLookup: Initialization successful, instance available as window.memberLookup');
    } catch (error) {
        console.error('MemberLookup: Error during initialization:', error);
        console.error('MemberLookup: Stack trace:', error.stack);
    }
});