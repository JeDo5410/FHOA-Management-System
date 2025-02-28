/**
 * Resident Form Behaviors
 * This handles the business rules for the resident form:
 * 1. Clear tenant/SPA field when mem_type is changed to homeowner
 * 2. Validate that tenant/SPA field is required when mem_type is Tenant or SPA
 * 3. When tenant/SPA field value changes, clear vehicle data
 * 4. Don't display car sticker data when status is inactive
 */
class ResidentFormBehaviors {
    constructor() {
        // Store references to key elements
        this.form = document.querySelector('form');
        this.tenantSpaField = document.getElementById('resident_tenantSpa');
        this.vehicleRows = document.querySelectorAll('.vehicle-row');
        this.memberTypeRadios = document.querySelectorAll('input[name="mem_typecode"]');
        
        // Initialize behaviors if all required elements are found
        if (this.validateRequiredElements()) {
            this.initializeBehaviors();
        }
    }
    
    validateRequiredElements() {
        const missing = [];
        if (!this.form) missing.push('form');
        if (!this.tenantSpaField) missing.push('tenant/SPA field');
        if (!this.memberTypeRadios.length) missing.push('member type radios');
        
        if (missing.length > 0) {
            console.warn(`ResidentFormBehaviors: Missing required elements: ${missing.join(', ')}`);
            return false;
        }
        return true;
    }
    
    initializeBehaviors() {
        // Set up event handlers
        this.setupMemberTypeHandlers();
        this.setupTenantSpaValidation();
        this.setupTenantSpaChangeHandler();
        this.setupVehicleStatusHandlers();
        
        // Apply initial state based on current selections
        this.applyInitialState();
        
        console.log('ResidentFormBehaviors: All behaviors initialized successfully');
    }
    
    setupMemberTypeHandlers() {
        this.memberTypeRadios.forEach(radio => {
            radio.addEventListener('change', (event) => {
                // Mark this as a user-initiated change
                if (event.isTrusted) {
                    radio.setAttribute('data-user-changed', 'true');
                }
                this.handleMemberTypeChange(radio, event);
            });
        });
    }
    
    handleMemberTypeChange(radioButton) {
        // Check if this change was triggered by user interaction or programmatically
        const isUserInitiated = radioButton.hasAttribute('data-user-changed') || 
                               (event && event.isTrusted);
        
        // Get the label text to identify the member type
        const label = radioButton.nextElementSibling;
        const labelText = label ? label.textContent.trim().toLowerCase() : '';
        
        // Check if this is a homeowner type
        const isHomeowner = labelText.includes('homeowner') || labelText.includes('home owner');
        
        if (isHomeowner) {
            // Clear tenant/SPA field for homeowner types
            this.tenantSpaField.value = '';
        }
        
        // Only clear vehicle data if this was a user-initiated change
        if (isUserInitiated) {
            this.clearVehicleData();
            showToast('info', 'Changes in resident type will archive car sticker data');
        }
        
        // Update validation state
        this.updateTenantSpaValidation();
    }
    
    setupTenantSpaValidation() {
        // Add form submission validation
        this.form.addEventListener('submit', (event) => {
            if (!this.validateTenantSpaField()) {
                event.preventDefault();
            }
        });
        
        // Also validate when member type changes
        this.memberTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.updateTenantSpaValidation();
            });
        });
    }
    
    validateTenantSpaField() {
        const selectedType = document.querySelector('input[name="mem_typecode"]:checked');
        if (!selectedType) return true;
        
        const label = selectedType.nextElementSibling;
        const labelText = label ? label.textContent.trim().toLowerCase() : '';
        
        // Check if this is a tenant or SPA type
        const isTenantOrSpa = labelText.includes('tenant') || labelText.includes('spa');
        
        if (isTenantOrSpa && !this.tenantSpaField.value.trim()) {
            showToast('error', 'Tenant/SPA name is required for this member type');
            this.tenantSpaField.focus();
            return false;
        }
        
        return true;
    }
    
    updateTenantSpaValidation() {
        const selectedType = document.querySelector('input[name="mem_typecode"]:checked');
        if (!selectedType) return;
        
        const label = selectedType.nextElementSibling;
        const labelText = label ? label.textContent.trim().toLowerCase() : '';
        
        // Check if this is a tenant or SPA type
        const isTenantOrSpa = labelText.includes('tenant') || labelText.includes('spa');
        
        // Find the tenant/SPA field label
        const parentRow = this.tenantSpaField.closest('.row');
        const tenantSpaLabel = parentRow ? parentRow.querySelector('label') : null;
        
        // Update visual indicators for required field
        if (isTenantOrSpa) {
            this.tenantSpaField.setAttribute('required', 'required');
            
            // Add a visual indicator to the label if not already present
            if (tenantSpaLabel && !tenantSpaLabel.querySelector('.required-indicator')) {
                const span = document.createElement('span');
                span.className = 'required-indicator text-danger ms-1';
                span.textContent = '*';
                tenantSpaLabel.appendChild(span);
            }
        } else {
            this.tenantSpaField.removeAttribute('required');
            
            // Remove the visual indicator from the label
            if (tenantSpaLabel) {
                const indicator = tenantSpaLabel.querySelector('.required-indicator');
                if (indicator) {
                    tenantSpaLabel.removeChild(indicator);
                }
            }
        }
    }
    
    setupTenantSpaChangeHandler() {
        // Store original value when field gets focus
        this.tenantSpaField.addEventListener('focus', function() {
            this.setAttribute('data-original-value', this.value);
        });
        
        // Check for changes when field loses focus
        this.tenantSpaField.addEventListener('blur', (event) => {
            const originalValue = event.target.getAttribute('data-original-value') || '';
            
            // Check if this was a user-initiated change
            if (event.isTrusted && event.target.value !== originalValue) {
                this.clearVehicleData();
                showToast('info', 'Changes in resident type will archive car sticker data');
            }
        });
    }
    
    clearVehicleData() {
        if (!this.vehicleRows.length) return;
        
        this.vehicleRows.forEach(row => {
            // Clear all input fields
            row.querySelectorAll('input').forEach(input => {
                input.value = '';
                input.removeAttribute('data-saved-value');
            });
            
            // Reset status select to Active (0)
            const statusSelect = row.querySelector('select[name$="[vehicle_active]"]');
            if (statusSelect) {
                statusSelect.value = '0';
                
                // Make sure inputs are enabled
                row.querySelectorAll('input').forEach(input => {
                    input.disabled = false;
                    input.classList.remove('text-muted', 'bg-light');
                });
            }
        });
        
        // Clear vehicle remarks
        const vehicleRemarks = document.getElementById('vehicle_remarks');
        if (vehicleRemarks) {
            vehicleRemarks.value = '';
        }
    }
    
    setupVehicleStatusHandlers() {
        if (!this.vehicleRows.length) return;
        
        this.vehicleRows.forEach(row => {
            const statusSelect = row.querySelector('select[name$="[vehicle_active]"]');
            if (!statusSelect) return;
            
            // Apply initial state
            this.updateVehicleRowState(row, statusSelect.value);
            
            // Add change handler
            statusSelect.addEventListener('change', (event) => {
                this.updateVehicleRowState(row, event.target.value);
            });
        });
    }
    
    updateVehicleRowState(row, status) {
        const inputs = row.querySelectorAll('input');
        const isInactive = status === '1';
        
        inputs.forEach(input => {
            if (isInactive) {
                // Save current value before clearing
                if (input.value) {
                    input.setAttribute('data-saved-value', input.value);
                }
                
                // Clear and disable the input
                input.value = '';
                input.disabled = true;
                input.classList.add('text-muted', 'bg-light');
            } else {
                // Restore saved value if available
                const savedValue = input.getAttribute('data-saved-value');
                if (savedValue) {
                    input.value = savedValue;
                    input.removeAttribute('data-saved-value');
                }
                
                // Enable the input
                input.disabled = false;
                input.classList.remove('text-muted', 'bg-light');
            }
        });
    }
    
    // Note: This is a client-side fallback only.
    // The real filtering of inactive vehicles should happen server-side.
    
    applyInitialState() {
        // Apply validation state to tenant/SPA field
        this.updateTenantSpaValidation();
        
        // Apply state to vehicle rows based on current status
        this.vehicleRows.forEach(row => {
            const statusSelect = row.querySelector('select[name$="[vehicle_active]"]');
            if (statusSelect) {
                this.updateVehicleRowState(row, statusSelect.value);
            }
        });
    }
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    new ResidentFormBehaviors();
});