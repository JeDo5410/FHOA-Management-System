@extends('layouts.app')
@section('title', 'Construction Permit')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/reports.css') }}">
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
                                Permit Status
                            </button>
                        </li>
                    </ul>                    
                    <div id="permitActionButtons">
                        <button type="button" class="btn btn-primary btn-sm me-2 permit-action-btn" id="newBtn">New</button>
                        <button type="button" class="btn btn-secondary btn-sm me-2 permit-action-btn" id="editBtn">Edit</button>
                        <button type="submit" class="btn btn-success btn-sm permit-action-btn" form="constructionPermitForm" id="saveBtn">Save</button>
                    </div>
                </div>
                <!-- Add a horizontal separator line -->
                <hr class="mt-0 mb-3">
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="permitTabsContent">
                <!-- Construction Permit Tab -->
                <div class="tab-pane fade show active" id="construction-permit" role="tabpanel" aria-labelledby="construction-permit-tab">
<form action="{{ route('construction-permit.store') }}" method="POST" id="constructionPermitForm" style="display: none;">
            @csrf

                    <!-- First Row: Permit Number and Status -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="permitNumber" class="form-label">Permit Number</label>
                                <input type="text" class="form-control form-control-sm bg-light" id="permitNumber" name="permit_number" readonly>
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
                                        name="address_id"
                                        placeholder="Enter PhaseLotBlock">
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
                                    <input type="text" class="form-control form-control-sm" id="permitSin" name="permit_sin" placeholder="Enter Existing Permit SIN">
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

                <!-- Permit Status Tab -->
                <div class="tab-pane fade" id="permit-history" role="tabpanel" aria-labelledby="permit-history-tab">
                    <h5 class="mb-4">Construction Permit Status</h5>
                    
                    <!-- Filters and Actions Container -->
                    <div class="filter-card mb-4">
                        <div class="filter-container">
                            <!-- Status Filter Column -->
                            <div class="filter-column">
                                <div class="column-title">Permit Status Filter</div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary" id="currentPermitCount">0</span>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusAll" value="all" checked onclick="loadPermitStatusData('all')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusAll">
                                        <span>All Permits</span>
                                        <small class="text-muted" id="allPermitsCount">Loading...</small>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusOngoing" value="1" onclick="loadPermitStatusData('1')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusOngoing">
                                        <span>On-Going</span>
                                        <small class="text-muted" id="ongoingPermitsCount">Loading...</small>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusInspection" value="2" onclick="loadPermitStatusData('2')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusInspection">
                                        <span>For Inspection</span>
                                        <small class="text-muted" id="inspectionPermitsCount">Loading...</small>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusBondRelease" value="3" onclick="loadPermitStatusData('3')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusBondRelease">
                                        <span>For Bond Release</span>
                                        <small class="text-muted" id="bondReleasePermitsCount">Loading...</small>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusForfeited" value="4" onclick="loadPermitStatusData('4')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusForfeited">
                                        <span>Close (Forfeited Bond)</span>
                                        <small class="text-muted" id="forfeitedPermitsCount">Loading...</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="permitStatus" id="statusReleased" value="5" onclick="loadPermitStatusData('5')">
                                    <label class="form-check-label d-flex justify-content-between w-100" for="statusReleased">
                                        <span>Close (Bond Released)</span>
                                        <small class="text-muted" id="releasedPermitsCount">Loading...</small>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Actions Column -->
                            <div class="filter-column">
                                <div class="column-title">Actions</div>
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-success" id="downloadPermitBtn">
                                        <i class="bi bi-download me-1"></i> Download CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table Container -->
                    <div class="table-container">
                        <div id="permitStatusTableContainer" class="table-responsive">
                            <table class="table table-bordered table-striped" id="permitStatusTable">
                                <thead>
                                    <tr>
                                        <th>Permit No.</th>
                                        <th>Permit Type</th>
                                        <th>Permit Status</th>
                                        <th>Permit Start Date</th>
                                        <th>Permit End Date</th>
                                        <th>HOA Address ID</th>
                                        <th>HOA Name</th>
                                        <th>Application Date</th>
                                        <th>Applicant Name</th>
                                        <th>Applicant Contact</th>
                                        <th>Contractor Name</th>
                                        <th>Contractor Contact</th>
                                        <th>Payment SIN</th>
                                        <th>SIN Date</th>
                                        <th>Fee Amount</th>
                                        <th>Bond ARN</th>
                                        <th>Bond Amount</th>
                                        <th>Bond Date</th>
                                        <th>Inspector</th>
                                        <th>Inspection Date</th>
                                        <th>Inspector Note</th>
                                        <th>Bond Release Type</th>
                                        <th>Bond Receiver</th>
                                        <th>Bond Release Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Sticky Scrollbar -->
                        <div class="sticky-scrollbar-container" id="permitStickyScrollbar">
                            <div class="scrollbar-content" id="permitScrollbarContent"></div>
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

<!-- Edit Permit Modal -->
<div class="modal fade" id="editPermitModal" tabindex="-1" aria-labelledby="editPermitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermitModalLabel">Edit Construction Permit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editPermitNumber" class="form-label">Enter Permit Number</label>
                    <input type="text" class="form-control" id="editPermitNumber" placeholder="Enter permit number to edit" autocomplete="off">
                    <div class="invalid-feedback" id="editPermitNumberError"></div>
                </div>
                <div id="permitNotFoundAlert" class="alert alert-danger d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Permit not found. Please check the permit number and try again.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="searchPermitBtn">
                    <span class="spinner-border spinner-border-sm d-none me-2" id="searchSpinner"></span>
                    Search
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Override reports.css for permit action buttons */
.permit-action-btn {
    border-radius: 4px !important;
    padding: 0.25rem 1rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    width: auto !important;
    text-align: center !important;
}

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

/* Form validation styles */
.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-valid,
.form-select.is-valid {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

/* Readonly field styling */
.form-control[readonly] {
    background-color: #f8f9fa !important;
    cursor: not-allowed;
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
// Utility function to format dates for HTML date inputs
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

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
            const permitActionButtons = document.getElementById('permitActionButtons');
            
            // Hide/show action buttons based on active tab
            if (e.target.id === 'permit-history-tab') {
                // Hide buttons when permit status tab is active
                if (permitActionButtons) {
                    permitActionButtons.style.display = 'none';
                }
            } else {
                // Show buttons for other tabs
                if (permitActionButtons) {
                    permitActionButtons.style.display = '';
                }
            }
            
            // Set focus on the first input field when switching to construction permit tab
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
    
    // Attach to window for global access
    window.showToast = showToast;
    
    // Function to generate next permit number
    function generateNextPermitNumber() {
        const permitNumberField = document.getElementById('permitNumber');
        
        // Show loading in permit number field
        if (permitNumberField) {
            permitNumberField.value = 'Generating...';
        }
        
        // Make AJAX request to get next permit number
        fetch('/construction-permit/next-permit-number', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set the generated permit number
                if (permitNumberField) {
                    permitNumberField.value = data.permit_number;
                }
                
                showToast('success', `Permit number ${data.permit_number} generated successfully`);
                
                console.log('Permit number generated:', {
                    permit_number: data.permit_number,
                    year: data.year,
                    month: data.month,
                    sequence: data.sequence
                });
            } else {
                // Handle error
                if (permitNumberField) {
                    permitNumberField.value = '';
                }
                showToast('error', data.message || 'Failed to generate permit number');
            }
        })
        .catch(error => {
            console.error('Error generating permit number:', error);
            
            // Clear the field and show error
            if (permitNumberField) {
                permitNumberField.value = '';
            }
            showToast('error', 'Network error while generating permit number. Please try again.');
        });
    }
    
    // Form state management
    let formHasChanges = false;
    let isFormVisible = false;
    let originalEndDate = ''; // Store the original end date
    
    // New button functionality
    const newBtn = document.getElementById('newBtn');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const form = document.getElementById('constructionPermitForm');
    const statusField2 = document.getElementById('status');
    
    if (newBtn && form) {
        newBtn.addEventListener('click', function() {
            // Show the form
            form.style.display = 'block';
            isFormVisible = true;
            
            // Clear all form fields
            clearForm();
            
            // Set status to "New"
            if (statusField2) {
                statusField2.value = 'New';
            }
            
            // Generate next permit number
            generateNextPermitNumber();
            
            // Focus on first input (after permit number is loaded)
            setTimeout(() => {
                const firstInput = form.querySelector('input[type="text"]:not([disabled]):not([readonly])');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 500);
            
            showToast('info', 'New construction permit form is ready');
        });
    }
    
    // Edit button functionality
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            // Show the edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editPermitModal'));
            editModal.show();
            
            // Clear previous values and errors
            document.getElementById('editPermitNumber').value = '';
            document.getElementById('editPermitNumber').classList.remove('is-invalid');
            document.getElementById('editPermitNumberError').textContent = '';
            document.getElementById('permitNotFoundAlert').classList.add('d-none');
            
            // Focus on permit number input
            setTimeout(() => {
                document.getElementById('editPermitNumber').focus();
            }, 500);
        });
    }
    
    // Search permit functionality
    const searchPermitBtn = document.getElementById('searchPermitBtn');
    const editPermitNumberInput = document.getElementById('editPermitNumber');
    
    if (searchPermitBtn && editPermitNumberInput) {
        // Handle search button click
        searchPermitBtn.addEventListener('click', function() {
            const permitNumber = editPermitNumberInput.value.trim();
            
            if (!permitNumber) {
                editPermitNumberInput.classList.add('is-invalid');
                document.getElementById('editPermitNumberError').textContent = 'Please enter a permit number.';
                return;
            }
            
            searchPermit(permitNumber);
        });
        
        // Handle Enter key press in input
        editPermitNumberInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchPermitBtn.click();
            }
        });
        
        // Clear validation on input
        editPermitNumberInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('editPermitNumberError').textContent = '';
            document.getElementById('permitNotFoundAlert').classList.add('d-none');
        });
    }
    
    // Function to search for permit
    function searchPermit(permitNumber) {
        const searchBtn = document.getElementById('searchPermitBtn');
        const spinner = document.getElementById('searchSpinner');
        const errorAlert = document.getElementById('permitNotFoundAlert');
        const input = document.getElementById('editPermitNumber');
        
        // Show loading state
        searchBtn.disabled = true;
        spinner.classList.remove('d-none');
        errorAlert.classList.add('d-none');
        input.classList.remove('is-invalid');
        
        // Make AJAX request to fetch permit
        fetch(`/construction-permit/search/${permitNumber}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editPermitModal')).hide();
                
                // Populate form with permit data
                populateFormWithPermitData(data.permit);
                // Show form
                const form = document.getElementById('constructionPermitForm');
                form.style.display = 'block';
                isFormVisible = true;
                
                showToast('success', 'Permit loaded successfully for editing');
            } else {
                // Show error
                errorAlert.classList.remove('d-none');
                input.classList.add('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error searching permit:', error);
            errorAlert.classList.remove('d-none');
            input.classList.add('is-invalid');
        })
        .finally(() => {
            // Hide loading state
            searchBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    }
    
    // Handle inspector note change to control permit end date field
    const inspectorNoteSelect = document.getElementById('inspectorNote');
    const permitEndDateField = document.getElementById('permitEndDate');
    
    if (inspectorNoteSelect && permitEndDateField) {
        inspectorNoteSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            
            if (selectedValue === 'For Bond Release' || selectedValue === 'For Bond Forfeiture') {
                // Store current value as original if not already stored
                if (!originalEndDate) {
                    originalEndDate = permitEndDateField.value;
                }
                
                // Reset to original date and make readonly
                permitEndDateField.value = originalEndDate;
                permitEndDateField.readOnly = true;
                permitEndDateField.classList.add('bg-light');
                
                // Show info message
                showToast('info', 'Permit end date cannot be modified when inspector note is set to bond release or forfeiture.');
            } else {
                // Remove readonly and allow editing
                permitEndDateField.readOnly = false;
                permitEndDateField.classList.remove('bg-light');
            }
        });
    }
    
    // Function to populate form with permit data
    function populateFormWithPermitData(permit) {
        // Update form action for editing
        const form = document.getElementById('constructionPermitForm');
        form.action = `/construction-permit/${permit.permit_no}`;
        
        // Add method spoofing for PUT request
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
        }
        
        // Populate all form fields
        document.getElementById('permitNumber').value = permit.permit_no || '';
        document.getElementById('status').value = getStatusText(permit.status_type) || '';
        document.getElementById('addressId').value = permit.address_id || '';
        document.getElementById('memberName').value = permit.member_name || '';
        document.getElementById('address').value = permit.address || '';
        document.getElementById('totalArrears').value = permit.total_arrears || '';
        document.getElementById('applicantName').value = permit.applicant_name || '';
        document.getElementById('applicationDate').value = formatDate(permit.application_date);
        document.getElementById('applicantContact').value = permit.applicant_contact || '';
        document.getElementById('contractorName').value = permit.contractor_name || '';
        document.getElementById('contractorContact').value = permit.contractor_contact || '';
        document.getElementById('permitTypeId').value = permit.permit_type || '';
        document.getElementById('permitSin').value = permit.permit_sin || '';
        document.getElementById('amountPaid').value = permit.permit_fee || '';
        document.getElementById('paidDate').value = formatDate(permit.permit_fee_date);
        document.getElementById('bondArn').value = permit.permit_arn || '';
        document.getElementById('bondPaid').value = permit.permit_bond || '';
        document.getElementById('bondPaidDate').value = formatDate(permit.permit_bond_date);
        document.getElementById('permitStartDate').value = formatDate(permit.permit_start_date);
        document.getElementById('permitEndDate').value = formatDate(permit.permit_end_date);
        // Store original end date for comparison
        originalEndDate = permit.permit_end_date || '';
        document.getElementById('inspector').value = permit.Inspector || '';
        document.getElementById('inspectorNote').value = permit.inspector_note || '';
        document.getElementById('inspectionDate').value = permit.inspection_date || '';
        document.getElementById('bondReceiver').value = permit.bond_receiver || '';
        document.getElementById('bondReleaseDate').value = permit.bond_release_date || '';
        document.getElementById('paymentType').value = permit.bond_release_type || '';
        document.getElementById('remarks').value = permit.remarks || '';
        
        // Show inspector and bond sections for editing
        const inspectorSection = document.getElementById('inspectorSection');
        const bondSection = document.getElementById('bondSection');
        if (inspectorSection) inspectorSection.style.display = 'flex';
        if (bondSection) bondSection.style.display = 'flex';
        
        // Enable amount paid and paid date fields for editing
        document.getElementById('amountPaid').disabled = false;
        document.getElementById('paidDate').disabled = false;
        
        formHasChanges = false;
    }
    
    // Function to get status text from status type
    function getStatusText(statusType) {
        const statusMap = {
            1: 'On-Going',
            2: 'Denied',
            3: 'For Bond Release',
            4: 'Close (Forfeited Bond)',
            5: 'Close (Bond Released)'
        };
        return statusMap[statusType] || 'Unknown';
    }
    
    // Function to clear form
    function clearForm() {
        const form = document.getElementById('constructionPermitForm');
        if (form) {
            // Store the permit number before resetting (if it exists and is not empty)
            const permitNumberField = document.getElementById('permitNumber');
            const currentPermitNumber = permitNumberField ? permitNumberField.value : '';
            
            // Reset all form inputs
            form.reset();
            
            // Restore the permit number if it was auto-generated (not empty and not 'Generating...')
            if (permitNumberField && currentPermitNumber && currentPermitNumber !== 'Generating...') {
                permitNumberField.value = currentPermitNumber;
            }
            
            // Clear specific fields that might not be reset properly
            const fieldsToDisable = ['memberName', 'address', 'totalArrears', 'amountPaid', 'paidDate', 'status'];
            fieldsToDisable.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && fieldId !== 'status') {
                    field.value = '';
                    if (fieldId === 'amountPaid' || fieldId === 'paidDate') {
                        field.disabled = true;
                    }
                }
            });
            
            // Hide inspector and bond sections
            const inspectorSection = document.getElementById('inspectorSection');
            const bondSection = document.getElementById('bondSection');
            if (inspectorSection) inspectorSection.style.display = 'none';
            if (bondSection) bondSection.style.display = 'none';
            
            // Reset original end date
            originalEndDate = '';
            
            formHasChanges = false;
        }
    }
    
    // Track form changes
    if (form) {
        form.addEventListener('input', function() {
            formHasChanges = true;
        });
        
        form.addEventListener('change', function() {
            formHasChanges = true;
        });
    }
    
    // Tab change prevention
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(tab => {
        tab.addEventListener('click', function(e) {
            if (formHasChanges && isFormVisible) {
                const confirmed = confirm('You have unsaved changes. Are you sure you want to switch tabs? All changes will be lost.');
                if (!confirmed) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                // If confirmed, reset form state
                formHasChanges = false;
                isFormVisible = false;
                if (form) form.style.display = 'none';
            }
        });
    });
    
    // Window beforeunload protection
    window.addEventListener('beforeunload', function(e) {
        if (formHasChanges && isFormVisible) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    // Handle form submission with AJAX
    let isSubmitting = false; // Flag to prevent multiple submissions
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Prevent multiple submissions
            if (isSubmitting) {
                return;
            }
            
            // Get form data
            const formData = new FormData(form);
            
            // Validate required fields
            const requiredFields = [
                'permit_number', 'address_id', 'applicant_name', 'application_date', 'applicant_contact',
                'contractor_name', 'contractor_contact', 'permit_type_id', 'permit_sin',
                'amount_paid', 'paid_date', 'bond_arn', 'bond_paid', 'bond_paid_date',
                'permit_start_date', 'permit_end_date'
            ];
            
            let hasErrors = false;
            let errorMessages = [];
            
            // Check required fields
            requiredFields.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field && (!field.value || field.value.trim() === '')) {
                    hasErrors = true;
                    field.classList.add('is-invalid');
                    // Get label text more safely
                    const label = form.querySelector(`label[for="${field.id}"]`);
                    const labelText = label ? label.textContent : fieldName;
                    errorMessages.push(`${labelText} is required.`);
                    console.log(`Validation failed for ${fieldName}: value = "${field.value}"`);
                } else if (field) {
                    field.classList.remove('is-invalid');
                    console.log(`Validation passed for ${fieldName}: value = "${field.value}"`);
                }
            });
            
            // Show validation errors
            if (hasErrors) {
                showToast('error', 'Please fill in all required fields.');
                return;
            }
            
            // Set submitting flag and disable the save button
            isSubmitting = true;
            const saveButton = document.getElementById('saveBtn');
            if (saveButton) {
                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            }
            
            // Show loading message
            showToast('info', 'Saving construction permit...');
            
            // Submit form data via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    formHasChanges = false;
                    
                    // Reset form and hide it
                    clearForm();
                    form.style.display = 'none';
                    isFormVisible = false;
                    
                    // Optionally, you can redirect or update UI
                    console.log('Permit created with ID:', data.permit_no);
                } else {
                    showToast('error', data.message || 'An error occurred while saving.');
                    
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(fieldName => {
                            const field = form.querySelector(`[name="${fieldName}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error saving construction permit:', error);
                showToast('error', 'An error occurred while saving the permit. Please try again.');
            })
            .finally(() => {
                // Reset submission state in all cases (success, error, or catch)
                isSubmitting = false;
                const saveButton = document.getElementById('saveBtn');
                if (saveButton) {
                    saveButton.disabled = false;
                    saveButton.innerHTML = 'Save';
                }
            });
        });
    }
});
</script>

@php
// Update this version when you change your JS files
$jsVersion = '1.0.0';
@endphp
<script src="{{ asset('assets/js/construction-permit-address-lookup.js') }}?v={{ $jsVersion }}"></script>
<script src="{{ asset('assets/js/construction-permit-sin-lookup.js') }}?v={{ $jsVersion }}"></script>
<script src="{{ asset('assets/js/construction-permit-status.js') }}?v={{ $jsVersion }}"></script>

@endsection
