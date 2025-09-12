@extends('layouts.app')
@section('title', 'Construction Permit')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-success">Construction Permit</h4>
        </div>
    </div>
    <!-- Container for tabs and buttons -->
    <div class="card shadow-sm border-success border-top border-3 mb-3">
        <div class="card-body p-3">
            <div class="mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs border-bottom-0" id="permitTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="construction-permit-tab" data-bs-toggle="tab" 
                                    data-bs-target="#construction-permit" type="button" role="tab" 
                                    aria-controls="construction-permit" aria-selected="true">
                                Construction Permit
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="permit-history-tab" data-bs-toggle="tab" 
                                    data-bs-target="#permit-history" type="button" role="tab" 
                                    aria-controls="permit-history" aria-selected="false">
                                Permit History
                            </button>
                        </li>
                    </ul>                    
                    <div>
                        <button type="button" class="btn btn-primary btn-sm me-2">New</button>
                        <button type="button" class="btn btn-secondary btn-sm me-2">Edit</button>
                        <button type="submit" class="btn btn-success btn-sm" form="constructionPermitForm">Save</button>
                    </div>
                </div>
                <!-- Add a horizontal separator line -->
                <hr class="mt-0 mb-3">
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="permitTabsContent">
                <!-- Construction Permit Tab -->
                <div class="tab-pane fade show active" id="construction-permit" role="tabpanel" aria-labelledby="construction-permit-tab">
<form action="{{-- Add your form action route here --}}" method="POST" id="constructionPermitForm">
            @csrf

                    <!-- First Row: Permit Number and Status -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="permitNumber" class="form-label">Permit Number</label>
                                <input type="text" class="form-control form-control-sm" id="permitNumber" name="permit_number">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control form-control-sm" id="status" name="status" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Address ID Container -->
                    <div class="border rounded p-3 mb-4">
                        <div class="row g-3 member-data">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="addressId" class="form-label">Address ID</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="addressId" 
                                        name="address_id">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="memberName" class="form-label">Member Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="memberName" 
                                            name="member_name"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="address" 
                                            name="address"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="totalArrears" class="form-label">Total Arrears</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₱</span>
                                        <input type="text" class="form-control form-control-sm text-danger fw-bold" id="totalArrears" name="total_arrears" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Permit Details Section inside card container -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-body p-4">
                            
                            <!-- Row 1: Applicant Name, Application Date, Applicant Contact No. -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="applicantName" class="form-label">Applicant Name</label>
                                    <input type="text" class="form-control form-control-sm" id="applicantName" name="applicant_name">
                                </div>
                                <div class="col-md-4">
                                    <label for="applicationDate" class="form-label">Application Date</label>
                                    <input type="date" class="form-control form-control-sm" id="applicationDate" name="application_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="applicantContact" class="form-label">Applicant Contact No.</label>
                                    <input type="text" class="form-control form-control-sm" id="applicantContact" name="applicant_contact">
                                </div>
                            </div>

                            <!-- Row 2: Contractor Name, Contractor Contact, Permit Type -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="contractorName" class="form-label">Contractor Name</label>
                                    <input type="text" class="form-control form-control-sm" id="contractorName" name="contractor_name">
                                </div>
                                <div class="col-md-4">
                                    <label for="contractorContact" class="form-label">Contractor Contact No.</label>
                                    <input type="text" class="form-control form-control-sm" id="contractorContact" name="contractor_contact">
                                </div>
                                <div class="col-md-4">
                                    <label for="permitTypeId" class="form-label">Permit Type</label>
                                    <select class="form-select form-select-sm" id="permitTypeId" name="permit_type_id">
                                        <option selected value="">Select...</option>
                                        @foreach($permitTypes as $permitType)
                                            <option value="{{ $permitType->typecode }}">{{ $permitType->typedescription }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Permit SIN, Amount Paid, Paid Date -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="permitSin" class="form-label">Permit SIN</label>
                                    <input type="text" class="form-control form-control-sm" id="permitSin" name="permit_sin">
                                </div>
                                <div class="col-md-4">
                                    <label for="amountPaid" class="form-label">Amount Paid</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₱</span>
                                        <input type="number" class="form-control form-control-sm" id="amountPaid" name="amount_paid" step="0.01" disabled>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="paidDate" class="form-label">Paid Date</label>
                                    <input type="date" class="form-control form-control-sm" id="paidDate" name="paid_date" disabled>
                                </div>
                            </div>

                            <!-- Row 4: Bond ARN, Bond Paid, Bond Paid Date -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="bondArn" class="form-label">Bond ARN</label>
                                    <input type="text" class="form-control form-control-sm" id="bondArn" name="bond_arn">
                                </div>
                                <div class="col-md-4">
                                    <label for="bondPaid" class="form-label">Bond Paid</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₱</span>
                                        <input type="number" class="form-control form-control-sm" id="bondPaid" name="bond_paid" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="bondPaidDate" class="form-label">Bond Paid Date</label>
                                    <input type="date" class="form-control form-control-sm" id="bondPaidDate" name="bond_paid_date">
                                </div>
                            </div>

                            <!-- Row 5: Permit Start Date, Permit End Date -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="permitStartDate" class="form-label">Permit Start Date</label>
                                    <input type="date" class="form-control form-control-sm" id="permitStartDate" name="permit_start_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="permitEndDate" class="form-label">Permit End Date</label>
                                    <input type="date" class="form-control form-control-sm" id="permitEndDate" name="permit_end_date">
                                </div>
                            </div>

                            <!-- Row 6: Inspector, Inspector Note, Inspection Date (Hidden by default, shown when status is 3,4,5) -->
                            <div class="row g-3 mb-3" id="inspectorSection" style="display: none;">
                                <div class="col-md-4">
                                    <label for="inspector" class="form-label">Inspector</label>
                                    <input type="text" class="form-control form-control-sm" id="inspector" name="inspector">
                                </div>
                                <div class="col-md-4">
                                    <label for="inspectorNote" class="form-label">Inspector Note</label>
                                    <select class="form-select form-select-sm" id="inspectorNote" name="inspector_note">
                                        <option selected value="">Select...</option>
                                        <option value="For Bond Release">For Bond Release</option>
                                        <option value="For Bond Forfeiture">For Bond Forfeiture</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inspectionDate" class="form-label">Inspection Date</label>
                                    <input type="date" class="form-control form-control-sm" id="inspectionDate" name="inspection_date">
                                </div>
                            </div>

                            <!-- Row 7: Bond Receiver, Bond Release Date, Payment Type (Hidden by default, shown when status is 3,4,5) -->
                            <div class="row g-3 mb-3" id="bondSection" style="display: none;">
                                <div class="col-md-4">
                                    <label for="bondReceiver" class="form-label">Bond Receiver</label>
                                    <input type="text" class="form-control form-control-sm" id="bondReceiver" name="bond_receiver">
                                </div>

                                <div class="col-md-4">
                                    <label for="bondReleaseDate" class="form-label">Bond Release Date</label>
                                    <input type="date" class="form-control form-control-sm" id="bondReleaseDate" name="bond_release_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="paymentType" class="form-label">Payment Type</label>
                                    <select class="form-select form-select-sm" id="paymentType" name="payment_type">
                                        <option selected value="">Select...</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Check">Check</option>
                                        <option value="GCash">GCash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>

                                <!-- Remarks Section -->
                                <div class="col-md-12 mt-3">
                                    <div class="mb-3">
                                        <label for="remarks" class="form-label">Remarks</label>
                                        <div class="position-relative">
                                            <textarea 
                                                class="form-control form-control-sm" 
                                                id="remarks" 
                                                name="remarks"
                                                rows="3"
                                                maxlength="200"
                                                style="resize: none;"
                                            ></textarea>
                                            <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="remarksCharCount">0/200</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                </div>

                <!-- Permit History Tab -->
                <div class="tab-pane fade" id="permit-history" role="tabpanel" aria-labelledby="permit-history-tab">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="text-center text-muted">
                                <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Permit History</h5>
                                <p>This section will display the history of permits for the selected member.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed" style="top: 20px; right: 20px; z-index: 1060;">
    <!-- Success Toast -->
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="successMessage">Operation completed successfully</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <!-- Info Toast -->
    <div id="infoToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-info-circle me-2"></i>
                <span id="infoMessage">Information message</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    
    <!-- Error Toast -->
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorMessage">An error occurred</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<style>
/* Base styling */
.form-control, .form-select {
    border-radius: 4px;
}
.btn {
    border-radius: 4px;
    padding: 0.25rem 1rem;
}
.col-form-label {
    font-weight: 400;
    font-size: 0.813rem;
}
.table th {
    font-size: 0.75rem;
    font-weight: 400;
    color: #666;
    padding-bottom: 0.75rem !important;
}

.form-label {
    font-size: 14px;
}

.form-check-label {
    font-size: 0.875rem;
}
.btn-link {
    text-decoration: none;
    padding: 0;
    font-size: 0.875rem;
}
h4.text-success {
    font-weight: 500;
}
.card.border-success {
    border-top-width: 3px !important;
    border-right: none;
    border-bottom: none;
    border-left: none;
}

/* Form container styles */
.form-container {
    padding: 0.5rem;
    border-radius: 4px;
}

/* Nav tabs styling similar to receivable page */
.nav-tabs .nav-link {
    color: #495057;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: none;
}

/* Input group styling */
.input-group-text {
    font-size: 0.9rem;
}

/* Member data section styling */
.member-data {
    transition: opacity 0.3s ease;
}

.member-data.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Address Dropdown Styles */
.address-dropdown {
    position: absolute;
    width: 100%;
    max-height: 280px;
    overflow-y: auto;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: none;
    z-index: 1050;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 4px;
    scrollbar-width: thin;
    padding: 0;
}

.address-list {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 100%;
}

.address-list li {
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 0.875rem;
    color: #1e293b;
    width: 100%;
}

.address-list li:last-child {
    border-bottom: none;
}

.address-list li:hover {
    background-color: #f1f5f9;
}

.address-list li.active {
    background-color: #f0f7ff;
}

.address-list li .d-flex {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 3px;
    align-items: center;
}

.address-id {
    font-weight: 500;
    color: #1e293b;
    font-size: 0.875rem;
}

.member-name {
    font-size: 0.75rem;
    color: #64748b;
    text-align: right;
}

.address-formatted {
    font-size: 0.75rem;
    color: #64748b;
    display: block;
    width: 100%;
}

.dropdown-loading,
.dropdown-error {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dropdown-loading {
    color: #475569;
}

.dropdown-error {
    color: #dc2626;
}

.loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
    to {
        transform: rotate(360deg);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .row.g-3 .col-md-4 {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character count for remarks textarea
    const remarksTextarea = document.getElementById('remarks');
    const charCountDisplay = document.getElementById('remarksCharCount');
    
    if (remarksTextarea && charCountDisplay) {
        remarksTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            charCountDisplay.textContent = `${currentLength}/${maxLength}`;
        });
    }
    
    // Tab switching functionality
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            // Set focus on the first input field when switching tabs
            setTimeout(() => {
                if (e.target.id === 'construction-permit-tab') {
                    const firstInput = document.querySelector('#construction-permit input[type="text"]:not([disabled])');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }
            }, 100);
        });
    });
    
    // Remove default date values - dates will be set manually or from database
    
    // Function to show/hide inspector and bond sections based on status
    function toggleInspectorBondSections() {
        const statusField = document.getElementById('status');
        const inspectorSection = document.getElementById('inspectorSection');
        const bondSection = document.getElementById('bondSection');
        
        if (statusField && inspectorSection && bondSection) {
            const statusValue = statusField.value;
            
            // Show sections if status is 3, 4, 5 or contains specific text
            const shouldShow = statusValue === '3' || statusValue === '4' || statusValue === '5' ||
                             statusValue.includes('For Bond Release') ||
                             statusValue.includes('Close (Forfeited Bond)') ||
                             statusValue.includes('Close (Bond Released)');
            
            if (shouldShow) {
                inspectorSection.style.display = 'block';
                bondSection.style.display = 'block';
            } else {
                inspectorSection.style.display = 'none';
                bondSection.style.display = 'none';
            }
        }
    }
    
    // Add event listener to status field if it exists
    const statusField = document.getElementById('status');
    if (statusField) {
        statusField.addEventListener('change', toggleInspectorBondSections);
        // Also check on page load
        toggleInspectorBondSections();
    }
    
    // Toast Notification Handler
    function showToast(type, message) {
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
    }
});
</script>

@php
// Update this version when you change your JS files
$jsVersion = '1.0.0';
@endphp
<script src="{{ asset('assets/js/construction-permit-address-lookup.js') }}?v={{ $jsVersion }}"></script>

@endsection
