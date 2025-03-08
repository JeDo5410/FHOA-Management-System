@extends('layouts.app')

@section('title', 'Account Receivable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-success">Account Receivable</h4>
        </div>
    </div>
    <!-- Container for tabs and buttons -->
    <div class="card shadow-sm border-success border-top border-3 mb-4">
        <div class="card-body p-4">
            <div class="mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs border-bottom-0" id="receivableTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="account-tab" data-bs-toggle="tab" 
                                    data-bs-target="#account" type="button" role="tab" 
                                    aria-controls="account" aria-selected="true">
                                Account Receivable
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="arrears-tab" data-bs-toggle="tab" 
                                    data-bs-target="#arrears" type="button" role="tab" 
                                    aria-controls="arrears" aria-selected="false">
                                HOA Monthly Dues
                            </button>
                        </li>
                    </ul>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm me-2" 
                                onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('accounts.receivables') }}'; }, 1000);">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-sm save-btn" id="accountSaveBtn">Save</button>
                    </div>
                </div>
                <!-- Add a horizontal separator line -->
                <hr class="mt-0 mb-4">
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="receivableTabsContent">
                <!-- Account Receivable Tab -->
                <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
                    <form action="{{route('accounts.receivables.store')}}" method="POST" id="accountReceivableForm">
                        @csrf
                        <input type="hidden" name="form_type" value="account_receivable">
                        <!-- Header Section with Labels Above Inputs -->
                        <div class="row g-3 mb-4">                    
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="address" 
                                        name="address"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="receivedFrom" class="form-label">Received From</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="receivedFrom" 
                                        name="received_from"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="serviceInvoiceNo" class="form-label">Service Invoice No.</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="serviceInvoiceNo" 
                                        name="service_invoice_no"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" 
                                        class="form-control form-control-sm" 
                                        id="date" 
                                        name="date"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items Table -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body p-0">
                                <div class="bg-light py-2 text-center mb-3 rounded-top border-bottom">
                                    <h6 class="m-0 fw-bold">DESCRIPTION</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-sm table-borderless" id="lineItemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%">Charts of Account (COA)</th>
                                                <th style="width: 40%">Amount</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="line-item">
                                                <td>
                                                    <select class="form-select form-select-sm enhanced" 
                                                        name="items[0][coa]" 
                                                        required>
                                                        <option value="">Select Account Type</option>
                                                        @foreach($accountTypes as $type)
                                                            <option value="{{ $type->acct_type_id }}">
                                                                {{ $type->acct_description }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>                                        
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm amount-input" 
                                                        name="items[0][amount]"
                                                        step="1" 
                                                        required>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                    class="btn btn-link text-danger remove-line">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-link text-primary add-line">
                                                        <i class="bi bi-plus-circle"></i> Add Line
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                
                                                <td colspan="1">
                                                    <div class="d-flex align-items-center ">
                                                        <strong>Total:</strong>
                                                        <input type="text" class="form-control form-control-sm text-end ms-2" id="totalAmount" name="total_amount" readonly style="min-width: 200px;">
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Received By Field -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label for="receivedBy" class="col-form-label">Received By</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="receivedBy" 
                                            name="received_by"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3">
                                    <label class="form-label mb-0">Mode of Payment:</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_mode" 
                                            id="cash" value="CASH" required>
                                        <label class="form-check-label" for="cash">Cash</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_mode" 
                                            id="gcash" value="GCASH">
                                        <label class="form-check-label" for="gcash">GCash</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_mode" 
                                            id="check" value="CHECK">
                                        <label class="form-check-label" for="check">Check</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_mode" 
                                            id="bankTransfer" value="BANK_TRANSFER">
                                        <label class="form-check-label" for="bankTransfer">Bank Transfer</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label for="reference" class="col-form-label">Reference No.</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="reference" 
                                            name="reference_no">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Field -->
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="row g-2 align-items-start">
                                    <div class="col-md-1">
                                        <label for="remarks" class="col-form-label">Remarks:</label>
                                    </div>
                                    <div class="col-md-11">
                                        <div class="position-relative">
                                            <textarea 
                                                class="form-control form-control-sm" 
                                                id="remarks" 
                                                name="remarks"
                                                rows="2"
                                                maxlength="45"
                                                style="resize: none;"
                                            ></textarea>
                                            <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="charCount">0/45</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Arrears Receivable Tab -->
                <div class="tab-pane fade" id="arrears" role="tabpanel" aria-labelledby="arrears-tab">
                    <form action="{{route('accounts.receivables.store')}}" method="POST" id="arrearsReceivableForm">
                        @csrf
                        <input type="hidden" name="form_type" value="arrears_receivable">
                        <!-- Header Section for Arrears tab with Labels Above Inputs -->
                        <div class="row g-3 mb-4">
                            <!-- First Column: Received From -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="arrears_receivedFrom" class="form-label">Received From</label>
                                    <input type="text" 
                                        class="form-control form-control-sm"
                                        id="arrears_receivedFrom" 
                                        name="arrears_received_from"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Second Column: Service Invoice No. -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="arrears_serviceInvoiceNo" class="form-label">Service Invoice No.</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="arrears_serviceInvoiceNo" 
                                        name="arrears_service_invoice_no"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Third Column: Date -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="arrears_date" class="form-label">Date</label>
                                    <input type="date" 
                                        class="form-control form-control-sm" 
                                        id="arrears_date" 
                                        name="arrears_date"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Auto Populate Section with Improved Form Layout -->
                        <div class="card mb-4 shadow-sm border-left-primary">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary font-weight-bold">
                                    <i class="bi bi-search me-2"></i> Member Arrears Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-1">
                                    <div class="col-md-7 d-flex align-items-center justify-content-end">
                                        <div id="lookupStatus" class="d-none">
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Member data loaded</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 member-data">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="memberName" class="form-label">Member Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="memberName" 
                                                    name="member_name"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="memberAddress" class="form-label">Member Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="memberAddress" 
                                                    name="member_address"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="arrears" class="form-label">Arrears Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="text" 
                                                    class="form-control form-control-sm text-danger fw-bold" 
                                                    id="arrears" 
                                                    name="arrears_amount"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="lastPaydate" class="form-label">Last Payment Date</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="lastPaydate" 
                                                    name="last_paydate"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="lastPayment" class="form-label">Last Payment Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-cash"></i></span>
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="lastPayment" 
                                                    name="last_payment"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Arrears Line Items Table -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body p-0">
                                <div class="bg-light py-2 text-center mb-3 rounded-top border-bottom">
                                    <h6 class="m-0 fw-bold">DESCRIPTION</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-sm table-borderless" id="arrearsLineItemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%">Charts of Account (COA)</th>
                                                <th style="width: 40%">Amount</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="line-item">
                                                <td>
                                                    <select class="form-select form-select-sm enhanced" 
                                                        name="arrears_items[0][coa]" 
                                                        required>
                                                        <option value="">Select Account Type</option>
                                                        @foreach($accountTypes as $type)
                                                            <option value="{{ $type->acct_type_id }}">
                                                                {{ $type->acct_description }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>                                        
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm arrears-amount-input" 
                                                        name="arrears_items[0][amount]"
                                                        step="1" 
                                                        required>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                    class="btn btn-link text-danger remove-arrears-line">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td >
                                                    <div class="d-flex align-items-center">
                                                        <strong>Total:</strong>
                                                        <input type="text" class="form-control form-control-sm text-end ms-2" id="arrearsTotalAmount" name="arrears_total_amount" readonly style="min-width: 200px;">
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Field for Arrears tab -->
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="row g-2 align-items-start">
                                    <div class="col-md-1">
                                        <label for="arrears_remarks" class="col-form-label">Remarks:</label>
                                    </div>
                                    <div class="col-md-11">
                                        <div class="position-relative">
                                            <textarea 
                                                class="form-control form-control-sm" 
                                                id="arrears_remarks" 
                                                name="arrears_remarks"
                                                rows="2"
                                                maxlength="45"
                                                style="resize: none;"
                                            ></textarea>
                                            <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="arrearsCharCount">0/45</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
    .form-check-label {
        font-size: 0.875rem;
    }
    .btn-link {
        text-decoration: none;
        padding: 0;
        font-size: 0.875rem;
    }
    .add-line, .add-arrears-line {
        font-size: 0.813rem;
    }
    .table > :not(caption) > * > * {
        padding: 0.25rem;
    }
    h4.text-success {
        font-weight: 500;
    }
    .header-icon {
        font-size: 2rem;
    }
    .card.border-success {
        border-top-width: 3px !important;
        border-right: none;
        border-bottom: none;
        border-left: none;
    }
    
    /* Border Left Primary Style */
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    
    /* Nav tabs styling similar to residents_data */
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
    
    /* Auto Populate Section Styles */
    .card-header h6 {
        font-size: 0.95rem;
    }
    
    .form-text {
        font-size: 0.7rem;
    }
    
    .member-data {
        transition: opacity 0.3s ease;
    }
    
    .member-data.loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .input-group-text {
        font-size: 0.9rem;
    }
    
    #lookupStatus {
        transition: all 0.3s ease;
    }
    
    #lookupStatus.fade-in {
        animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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
    
    /* Responsive Form Layout Styles */
    @media (max-width: 768px) {
    /* General row transformation for mobile */
    .row.g-2.align-items-center,
    .row.g-3 .col-md-3 .d-flex,
    .row.g-3 .col-md-4 .d-flex,
    .d-flex.align-items-center,
    .d-flex.justify-content-center.align-items-center,
    .d-flex.justify-content-end.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        width: 100%;
    }
    
    /* Make labels and inputs full width on mobile */
    .row.g-2.align-items-center > div,
    .row.g-3 .col-md-3 .d-flex > div,
    .row.g-3 .col-md-4 .d-flex > div,
    .d-flex.align-items-center > div,
    .d-flex.justify-content-center.align-items-center > div,
    .d-flex.justify-content-end.align-items-center > div {
        width: 100%;
        min-width: 100% !important;
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
    }
    
    /* Add spacing between label and input */
    .col-form-label {
        margin-bottom: 0.25rem;
        padding-bottom: 0;
    }
    
    /* Full-width inputs */
    .form-control,
    .form-select {
        width: 100%;
    }
    
    /* Reset alignment on mobile */
    .d-flex.justify-content-end,
    .d-flex.justify-content-center {
        justify-content: flex-start !important;
    }
    
    /* Fix column widths on mobile */
    .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
    .col-md-5, .col-md-6, .col-md-7, .col-md-8 {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    /* Better spacing for the date field specifically */
    #date, #arrears_date {
        width: 100%;
        max-width: 100% !important;
    }
    
    /* Adjust spacing for remarks section */
    .row.g-3 .col-md-12 .row.g-2.align-items-start {
        flex-direction: column;
    }
    
    .row.g-3 .col-md-12 .row.g-2.align-items-start > div {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
    }
    
    /* HOA Monthly Dues Tab Specific Styles */
    .card-body .form-group {
        margin-bottom: 1rem;
    }
    
    .card-body .form-group .input-group {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .card-body .form-group .input-group .input-group-text {
        width: auto;
        margin-bottom: 0.25rem;
        border-radius: 4px;
    }
    
    .card-body .form-group .input-group .form-control {
        width: 100%;
        border-radius: 4px !important;
        margin-left: 0;
    }
    
    .member-data .col-md-4 {
        width: 100%;
    }
    }

    /* Label style enhancement for all screen sizes */
    .col-form-label {
    font-weight: 500;
    }

/* Transition for smooth responsive changes */
.row, .d-flex, .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
.col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-12 {
  transition: all 0.3s ease-in-out;
}

</style>

<script>
// Character count for remarks textarea
document.addEventListener('DOMContentLoaded', function() {
    const remarksTextarea = document.getElementById('remarks');
    const charCountDisplay = document.getElementById('charCount');
    
    if (remarksTextarea && charCountDisplay) {
        remarksTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            charCountDisplay.textContent = `${currentLength}/${maxLength}`;
        });
    }
    
    const arrearsRemarksTextarea = document.getElementById('arrears_remarks');
    const arrearsCharCountDisplay = document.getElementById('arrearsCharCount');
    
    if (arrearsRemarksTextarea && arrearsCharCountDisplay) {
        arrearsRemarksTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            arrearsCharCountDisplay.textContent = `${currentLength}/${maxLength}`;
        });
    }
    
    // Set current date as default for date fields
    const dateFields = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateFields.forEach(field => {
        field.value = today;
    });
    
    // Add line functionality for Account Receivable tab
    const addLineBtn = document.querySelector('.add-line');
    const tbody = document.querySelector('#lineItemsTable tbody');
    
    if (addLineBtn && tbody) {
        addLineBtn.addEventListener('click', function() {
            const rowCount = tbody.querySelectorAll('tr').length;
            const newRow = document.createElement('tr');
            newRow.className = 'line-item';
            
            newRow.innerHTML = `
                <td>
                    <select class="form-select form-select-sm enhanced" name="items[${rowCount}][coa]" required>
                        <option value="">Select Account Type</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type->acct_type_id }}">
                                {{ $type->acct_description }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm amount-input" name="items[${rowCount}][amount]" step="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-link text-danger remove-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
            attachRemoveLineListeners();
            calculateTotal();
        });
    }
    
    // Add line functionality for Arrears Receivable tab
    const addArrearsLineBtn = document.querySelector('.add-arrears-line');
    const arrearsTbody = document.querySelector('#arrearsLineItemsTable tbody');
    
    if (addArrearsLineBtn && arrearsTbody) {
        addArrearsLineBtn.addEventListener('click', function() {
            const rowCount = arrearsTbody.querySelectorAll('tr').length;
            const newRow = document.createElement('tr');
            newRow.className = 'line-item';
            
            newRow.innerHTML = `
                <td>
                    <select class="form-select form-select-sm enhanced" name="arrears_items[${rowCount}][coa]" required>
                        <option value="">Select Account Type</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type->acct_type_id }}">
                                {{ $type->acct_description }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm arrears-amount-input" name="arrears_items[${rowCount}][amount]" step="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-link text-danger remove-arrears-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            arrearsTbody.appendChild(newRow);
            attachRemoveArrearsLineListeners();
            calculateArrearsTotal();
        });
    }
    
    // Attach remove line event listeners
    function attachRemoveLineListeners() {
        document.querySelectorAll('.remove-line').forEach(button => {
            button.addEventListener('click', function() {
                if (tbody.querySelectorAll('tr').length > 1) {
                    this.closest('tr').remove();
                    reindexRows();
                    calculateTotal();
                } else {
                    showToast('info', 'Cannot remove the last line item');
                }
            });
        });
    }
    
    // Attach remove line event listeners for arrears tab
    function attachRemoveArrearsLineListeners() {
        document.querySelectorAll('.remove-arrears-line').forEach(button => {
            button.addEventListener('click', function() {
                if (arrearsTbody.querySelectorAll('tr').length > 1) {
                    this.closest('tr').remove();
                    reindexArrearsRows();
                    calculateArrearsTotal();
                } else {
                    showToast('info', 'Cannot remove the last line item');
                }
            });
        });
    }
    
    // Reindex rows after removal
    function reindexRows() {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelectorAll('[name^="items["]').forEach(element => {
                const name = element.getAttribute('name');
                const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                element.setAttribute('name', newName);
            });
        });
    }
    
    // Reindex rows after removal for arrears tab
    function reindexArrearsRows() {
        const rows = arrearsTbody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelectorAll('[name^="arrears_items["]').forEach(element => {
                const name = element.getAttribute('name');
                const newName = name.replace(/arrears_items\[\d+\]/, `arrears_items[${index}]`);
                element.setAttribute('name', newName);
            });
        });
    }
    
    // Calculate total amount
    function calculateTotal() {
        const amountInputs = document.querySelectorAll('.amount-input');
        let total = 0;
        
        amountInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        document.getElementById('totalAmount').value = total.toFixed(2);
    }
    
    // Calculate total amount for arrears tab
    function calculateArrearsTotal() {
        const amountInputs = document.querySelectorAll('.arrears-amount-input');
        let total = 0;
        
        amountInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        document.getElementById('arrearsTotalAmount').value = total.toFixed(2);
    }
    
    // Handle form submission based on active tab
    document.getElementById('accountSaveBtn').addEventListener('click', function() {
        const activeTab = document.querySelector('.tab-pane.active');
        const activeTabId = activeTab.getAttribute('id');
        
        if (activeTabId === 'account') {
            document.getElementById('accountReceivableForm').submit();
        } else if (activeTabId === 'arrears') {
            document.getElementById('arrearsReceivableForm').submit();
        }
    });
    
    // Attach change event to amount inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('amount-input')) {
            calculateTotal();
        }
        if (e.target.classList.contains('arrears-amount-input')) {
            calculateArrearsTotal();
        }
    });
    
    // Initialize
    attachRemoveLineListeners();
    attachRemoveArrearsLineListeners();
    calculateTotal();
    calculateArrearsTotal();
    
    // Add event listener for tab changes
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            // Update active tab styling if needed
        });
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
</script>

@endsection