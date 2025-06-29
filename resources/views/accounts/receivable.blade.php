@extends('layouts.app')

@section('title', 'Account Receivable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
    $currentUserName = Auth::user()->fullname ?? '';
@endphp

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-success">Account Receivable</h4>
        </div>
    </div>
    <!-- Container for tabs and buttons -->
    <div class="card shadow-sm border-success border-top border-3 mb-3">
        <div class="card-body p-3">
            <div class="mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs border-bottom-0" id="receivableTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="arrears-tab" data-bs-toggle="tab" 
                                    data-bs-target="#arrears" type="button" role="tab" 
                                    aria-controls="arrears" aria-selected="true">
                                HOA Monthly Dues
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="account-tab" data-bs-toggle="tab" 
                                    data-bs-target="#account" type="button" role="tab" 
                                    aria-controls="account" aria-selected="false">
                                Account Receivable
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
                <hr class="mt-0 mb-3">
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="receivableTabsContent">
                <!-- Arrears Receivable Tab -->
                <div class="tab-pane fade show active" id="arrears" role="tabpanel" aria-labelledby="arrears-tab">
                    <form action="{{route('accounts.receivables.store')}}" method="POST" id="arrearsReceivableForm">
                        @csrf
                        <input type="hidden" name="active_tab" id="arrears_active_tab" value="arrears">
                        <input type="hidden" name="form_type" value="arrears_receivable">
                        <!-- Member Info Fields moved outside the container -->
                        <!-- Modified Member Info Fields in 2 rows layout -->
                        <div class="row g-2 member-data mb-3">
                            <!-- First Row - 3 columns -->
                            <div class="col-md-4">
                                <div class="mb-2">
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
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <label for="memberAddress" class="form-label">Member Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="memberAddress" 
                                            name="member_address"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <label for="totalArrears" class="form-label">Total Arrears (with Interest)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₱</span>
                                        <input type="text" class="form-control form-control-sm text-danger fw-bold" id="total_arrears" name="total_arrears" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Second Row - 4 columns -->
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="lastPaydate" class="form-label">Last Payment Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="lastPaydate" 
                                            name="last_paydate"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="lastPayment" class="form-label">Last Payment Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-cash"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="lastPayment" 
                                            name="last_payment"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="lastOR" class="form-label">Last OR Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-receipt"></i></span>
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="lastOR" 
                                            name="last_or"
                                            disabled>
                                        <button class="btn btn-outline-secondary btn-sm" 
                                            type="button" 
                                            id="viewPaymentHistory" 
                                            disabled>
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Click <i class="bi bi-clock-history"></i> to view payment history</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="arrears" class="form-label">Arrears (Principal)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₱</span>
                                        <input type="text" class="form-control form-control-sm text-danger fw-bold" id="arrears_amount" name="arrears_amount" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Single container for HOA Monthly Dues tab -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body p-3">                                
                                <!-- Header Section for Arrears tab with Labels Above Inputs (moved inside the container) -->
                                <div class="row g-2 mb-3">
                                    <!-- First Column: Service Invoice No. -->
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <label for="arrears_serviceInvoiceNo" class="form-label">Service Invoice No.</label>
                                            <input type="number" 
                                                class="form-control form-control-sm" 
                                                id="arrears_serviceInvoiceNo" 
                                                name="arrears_service_invoice_no"
                                                min="1"
                                                autocomplete="off"
                                                required>
                                        </div>
                                    </div>
                                    
                                    <!-- Second Column: Date -->
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <label for="arrears_date" class="form-label">Date</label>
                                            <input type="date" 
                                                class="form-control form-control-sm" 
                                                id="arrears_date" 
                                                name="arrears_date"
                                                required>
                                        </div>
                                    </div>
                                    
                                    <!-- Third Column: Received From -->
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <label for="arrears_receivedFrom" class="form-label">Received From</label>
                                            <input type="text" 
                                                class="form-control form-control-sm"
                                                id="arrears_receivedFrom" 
                                                name="arrears_received_from"
                                                autocomplete="off"
                                                required>
                                        </div>
                                    </div>
                                    
                                    <!-- Fourth Column: Address ID -->
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <label for="arrears_addressId" class="form-label">Address ID</label>
                                            <input type="text" 
                                                class="form-control form-control-sm"
                                                id="arrears_addressId" 
                                                name="arrears_address_id"
                                                autocomplete="off"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Table -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-borderless" id="arrearsLineItemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%">Account Type</th>
                                                <th style="width: 50%">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="line-item">
                                                <td>
                                                    <select class="form-select form-select-sm enhanced" 
                                                        name="arrears_items[0][coa]" 
                                                        required>
                                                        <option value="">Select Account Type</option>
                                                        @foreach($duesAccountTypes as $type)
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
                                                    step="0.01" 
                                                    min="0.01"
                                                    required>
                                                </td>
                                            </tr>
                                        </tbody>
                                        {{-- <tfoot>
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
                                        </tfoot> --}}
                                    </table>
                                </div>
                                <!-- Received By Field -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-12">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-1">
                                                <label for="arrears_receivedBy" class="col-form-label">Received By</label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="arrears_receivedBy" 
                                                    name="arrears_received_by"
                                                    value="{{ $currentUserName }}"
                                                    autocomplete="off"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Payment Details for HOA Monthly Dues tab -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="form-label mb-0">Mode of Payment:</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="arrears_payment_mode" 
                                                    id="arrears_cash" value="CASH" required checked>
                                                <label class="form-check-label" for="arrears_cash">Cash</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="arrears_payment_mode" 
                                                    id="arrears_gcash" value="GCASH">
                                                <label class="form-check-label" for="arrears_gcash">GCash</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="arrears_payment_mode" 
                                                    id="arrears_check" value="CHECK">
                                                <label class="form-check-label" for="arrears_check">Check</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="arrears_payment_mode" 
                                                    id="arrears_bankTransfer" value="BANK_TRANSFER">
                                                <label class="form-check-label" for="arrears_bankTransfer">Bank Transfer</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-4">
                                                <label for="arrears_reference" class="col-form-label">Reference No.</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" 
                                                    class="form-control form-control-sm" 
                                                    id="arrears_reference" 
                                                    name="arrears_reference_no"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Remarks Field -->
                                <div class="row g-2">
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
                                                        rows="1"
                                                        maxlength="100"
                                                        style="resize: none;"
                                                        autocomplete="off"
                                                    ></textarea>
                                                    <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="arrearsCharCount">0/100</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Account Receivable Tab -->
                <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="account-tab">
                    <form action="{{route('accounts.receivables.store')}}" method="POST" id="accountReceivableForm">
                        @csrf
                        <input type="hidden" name="active_tab" id="account_active_tab" value="account">
                        <input type="hidden" name="form_type" value="account_receivable">
                        <!-- Header Section with Labels Above Inputs -->
                        <div class="row g-2 mb-3">
                            <!-- First Column: Service Invoice No. -->
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="serviceInvoiceNo" class="form-label">Service Invoice No.</label>
                                    <input type="number" 
                                        class="form-control form-control-sm" 
                                        id="serviceInvoiceNo" 
                                        name="service_invoice_no"
                                        min="1"
                                        autocomplete="off"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Second Column: Date -->
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" 
                                        class="form-control form-control-sm" 
                                        id="date" 
                                        name="date"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Third Column: Received From -->
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="receivedFrom" class="form-label">Received From</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="receivedFrom" 
                                        name="received_from"
                                        autocomplete="off"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Fourth Column: Address -->
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        id="address" 
                                        name="address"
                                        autocomplete="off"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Single container for Account Receivable tab -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body p-3">
                                <!-- Line Items Table -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-borderless" id="lineItemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%">Account Type</th>
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
                                                    min="1"
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

                                <!-- Received By Field -->
                                <div class="row g-2 mb-3">
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
                                                    value="{{ $currentUserName }}"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Payment Details -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="form-label mb-0">Mode of Payment:</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payment_mode" 
                                                    id="cash" value="CASH" required checked>
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
                                                    name="reference_no"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Remarks Field -->
                                <div class="row g-2">
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
                                                        rows="1"
                                                        maxlength="100"
                                                        style="resize: none;"
                                                        autocomplete="off"
                                                    ></textarea>
                                                    <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="charCount">0/100</small>
                                                </div>
                                            </div>
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
                <span id="successMessage">Account receivable created successfully</span>
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
    <!-- Transaction Choice Modal -->
    <div class="modal fade" id="transactionChoiceModal" tabindex="-1" aria-labelledby="transactionChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="transactionChoiceModalLabel">SIN Found - Choose Action</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Existing SIN Found:</strong> This SIN already exists in the system.
                    </div>
                    
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2">Transaction Details:</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>SIN Number:</strong> <span id="modalSinNumber">-</span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="modalTransactionDate">-</span></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Amount:</strong> <span id="modalTransactionAmount">-</span></p>
                                <p class="mb-0"><strong>Payor:</strong> <span id="modalPayorName">-</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-3">What would you like to do with this SIN?</p>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-info btn-lg" id="editTransactionBtn">
                            <i class="bi bi-pencil-square me-2"></i>Edit Transaction
                            <small class="d-block text-white-50">Modify the details of this SIN</small>
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" id="cancelTransactionBtn">
                            <i class="bi bi-x-circle me-2"></i>Cancel Transaction
                            <small class="d-block text-white-50">Reverse/cancel this SIN</small>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Exit</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryModalLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Member info summary -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Member:</strong> <span id="modalMemberName">-</span></p>
                                <p class="mb-0"><strong>Address:</strong> <span id="modalMemberAddress">-</span></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1"><strong>Arrears (Principal):</strong> <span id="modalCurrentArrears" class="text-danger">-</span></p>
                                <p class="mb-0"><strong>Total Arrears (with Interest):</strong> <span id="modalTotalArrears" class="text-danger">-</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div id="paymentHistoryLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading payment history...</p>
                    </div>
                    
                    <!-- Error message -->
                    <div id="paymentHistoryError" class="alert alert-danger d-none">
                        Failed to load payment history. Please try again later.
                    </div>
                    
                    <!-- Payment history table -->
                    <div id="paymentHistoryTableContainer" class="table-responsive d-none">
                        <table class="table table-sm table-hover" id="paymentHistoryTable">
                            <thead>
                                <tr>
                                    <th class="text-start">Date</th>
                                    <th class="text-start">OR Number</th>
                                    <th class="text-start">Payor Name</th>
                                    <th class="text-start">Account Type</th>
                                    <th class="text-start">Amount</th>
                                    <th class="text-start">Balance After Payment</th>
                                    <th class="text-start">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table rows will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- No records found message -->
                    <div id="noPaymentHistory" class="alert alert-info d-none">
                        No payment history found for this member.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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
    
    /* Form container styles */
    .form-container {
        padding: 0.5rem;
        border-radius: 4px;
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
        /* Reduced spacing for smaller screens */
        .mb-3 {
            margin-bottom: 0.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 0.75rem !important;
        }
        
        .card-body {
            padding: 0.5rem !important;
        }
        
        .p-3 {
            padding: 0.5rem !important;
        }
        
        .p-4 {
            padding: 0.75rem !important;
        }
        
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
        
        /* Mode of payment alternative layout for smaller screens */
        .row.g-2.mb-3 .col-md-8 .d-flex.align-items-center.gap-3 {
            flex-wrap: wrap;
            gap: 0.5rem !important;
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
            margin-bottom: 0.5rem;
        }
        
        /* Better spacing for the date field specifically */
        #date, #arrears_date {
            width: 100%;
            max-width: 100% !important;
        }
        
        /* Adjust spacing for remarks section */
        .row.g-3 .col-md-12 .row.g-2.align-items-start,
        .row.g-2 .col-md-12 .row.g-2.align-items-start {
            flex-direction: column;
        }
        
        .row.g-3 .col-md-12 .row.g-2.align-items-start > div,
        .row.g-2 .col-md-12 .row.g-2.align-items-start > div {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }
        
        /* HOA Monthly Dues Tab Specific Styles */
        .card-body .form-group {
            margin-bottom: 0.5rem;
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
        
        /* Make table more compact on mobile */
        .table > :not(caption) > * > * {
            padding: 0.2rem;
        }
        
        .table th {
            font-size: 0.7rem;
        }
        
        /* Fix for account receivable tab */
        .account-form-container .card-body,
        .arrears-form-container .card-body {
            padding: 0.25rem !important;
        }
        
        /* Compact table styles */
        .table-responsive {
            padding: 0.25rem !important;
        }
    }

    /* Very small screens (under 480px) */
    @media (max-width: 480px) {
        /* Further reduce spacing */
        .card-body {
            padding: 0.25rem !important;
        }
        
        .form-check-inline {
            margin-right: 0.25rem;
        }
        
        .form-check-label {
            font-size: 0.75rem;
        }
        
        /* Make buttons smaller */
        .btn {
            padding: 0.2rem 0.75rem;
            font-size: 0.75rem;
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
    
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    /* Payment History Modal - Smaller Text Styles */
    #paymentHistoryModal .modal-body {
    font-size: 14px;
    }

    #paymentHistoryModal .table {
    font-size: 14px;
    }

    #paymentHistoryModal .table th {
    font-size: 14px;
    font-weight: 500;
    }

    #paymentHistoryModal .table td {
    font-size: 14px;
    }

    #paymentHistoryModal .modal-title {
    font-size: 16px; /* Slightly larger for the title */
    }

    #paymentHistoryModal .bg-light p {
    font-size: 14px;
    margin-bottom: 0.5rem;
    }

    #paymentHistoryModal .alert {
    font-size: 14px;
    }

    #paymentHistoryModal .spinner-border + p {
    font-size: 14px;
    }
</style>

<script>
// Character count for remarks textarea
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    // If tab parameter exists in URL, activate that tab
    if (activeTab) {
        // First, find the corresponding tab button
        const tabToActivate = document.getElementById(`${activeTab}-tab`);
        if (tabToActivate) {
            // Create a new bootstrap Tab instance and show it
            const tab = new bootstrap.Tab(tabToActivate);
            tab.show();
        }
    }
    
    // Update active_tab field whenever tabs change
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const tabId = e.target.getAttribute('id').replace('-tab', '');
            
            // Update both hidden fields to ensure the correct one is submitted
            document.getElementById('arrears_active_tab').value = tabId;
            document.getElementById('account_active_tab').value = tabId;
        });
    });

    const remarksTextarea = document.getElementById('remarks');
    const charCountDisplay = document.getElementById('charCount');
    const accountTab = document.getElementById('account-tab');
    
    if (accountTab) {
        // Add tab change listener to re-focus the service invoice no when the account tab is shown
        accountTab.addEventListener('shown.bs.tab', function() {
            const serviceInvoiceField = document.getElementById('serviceInvoiceNo');
            if (serviceInvoiceField) {
                setTimeout(() => {
                    serviceInvoiceField.focus();
                }, 100); // Small delay to ensure the tab is fully shown
            }
        });
    }

    
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
    
    // Set current date as default for date fields using Philippine time (UTC+8)
    const dateFields = document.querySelectorAll('input[type="date"]');

    // Get current date adjusted for Philippine timezone (UTC+8)
    const now = new Date();
    const philippineTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
    // Format as YYYY-MM-DD for date inputs
    const formattedDate = philippineTime.toISOString().split('T')[0];

    dateFields.forEach(field => {
        field.value = formattedDate;
    });

    
    // IMMEDIATE FOCUS: Set focus on arrears_addressId field on page load
    // This is outside any setTimeout to happen as soon as possible
    try {
        // Try to focus immediately
        document.getElementById('arrears_addressId').focus();
    } catch (e) {
        console.log("Immediate focus failed, will retry with delay");
    }

    // Add line functionality for Account Receivable tab
    const addLineBtn = document.querySelector('.add-line');
    const tbody = document.querySelector('#lineItemsTable tbody');

    if (addLineBtn && tbody) {
        addLineBtn.addEventListener('click', function() {
            // First check if current lines are all valid
            const currentRows = tbody.querySelectorAll('tr.line-item');
            let allRowsComplete = true;
            let incompleteRow = null;
            
            // Check each existing row for completeness
            for (const row of currentRows) {
                const selectField = row.querySelector('select');
                const amountField = row.querySelector('.amount-input');
                
                // Check if either field is empty or invalid
                if (!selectField.value || 
                    !amountField.value || 
                    parseFloat(amountField.value) <= 0) {
                    
                    allRowsComplete = false;
                    incompleteRow = row;
                    break;
                }
            }
            
            if (!allRowsComplete) {
                // Show error message
                showToast('error', 'Please complete the current line item before adding a new one');
                
                // Focus on the first invalid field in the incomplete row
                if (incompleteRow) {
                    const selectField = incompleteRow.querySelector('select');
                    const amountField = incompleteRow.querySelector('.amount-input');
                    
                    if (!selectField.value) {
                        selectField.focus();
                    } else if (!amountField.value || parseFloat(amountField.value) <= 0) {
                        amountField.focus();
                    }
                }
                
                return;
            }
            
            // If all rows are complete, proceed with adding a new row
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
                    <input type="number" class="form-control form-control-sm amount-input" name="items[${rowCount}][amount]" step="0.01" min="0.01" required>
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
            
            // Focus on the newly added row's select field
            const newSelect = newRow.querySelector('select');
            if (newSelect) {
                newSelect.focus();
            }
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
        
    // Handle form submission based on active tab
    let isSubmitting = false; // Flag to prevent multiple submissions

    document.getElementById('accountSaveBtn').addEventListener('click', function() {
        // Prevent multiple submissions
        if (isSubmitting) {
            return;
        }
        
        const activeTab = document.querySelector('.tab-pane.active');
        const activeTabId = activeTab.getAttribute('id');
        
        // Validate the form before submission
        if (activeTabId === 'account') {
            const form = document.getElementById('accountReceivableForm');
            if (validateAccountReceivableForm(form)) {
                // Set submitting flag and disable the button
                isSubmitting = true;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                
                // Show processing message
                showToast('info', 'Processing your request...');
                
                // Re-enable disabled fields for form submission
                prepareFormForSubmission(form);
                
                // Submit the form
                form.submit();
            }
        } else if (activeTabId === 'arrears') {
            const form = document.getElementById('arrearsReceivableForm');
            if (validateArrearsReceivableForm(form)) {
                // Set submitting flag and disable the button
                isSubmitting = true;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                
                // Show processing message
                showToast('info', 'Processing your request...');
                
                // Re-enable disabled fields for form submission
                prepareFormForSubmission(form);
                
                // Submit the form
                form.submit();
            }
        }
    });

    // Validate the Account Receivable form
    function validateAccountReceivableForm(form) {
        // Check if we're in reversal mode
        const isReversalMode = document.getElementById('accountSaveBtn').getAttribute('data-reversal-mode') === 'true' || 
                            (document.getElementById('remarks').value || '').includes('CANCELLED OR');
        
        console.log("Validation in reversal mode:", isReversalMode);
        
        // Basic HTML5 validation
        if (!form.checkValidity()) {
            console.log("Form validity check failed");
            form.reportValidity();
            return false;
        }
        
        // Check if at least one line item is added
        const lineItems = form.querySelectorAll('.amount-input');
        let hasValue = false;
        
        lineItems.forEach(input => {
            const value = parseFloat(input.value);
            // In reversal mode we want negative values, otherwise positive values
            if (isReversalMode && value < 0) {
                hasValue = true;
            } else if (!isReversalMode && value > 0) {
                hasValue = true;
            }
        });
        
        if (!hasValue) {
            const message = isReversalMode 
                ? 'Please add at least one line item with a negative amount for reversal'
                : 'Please add at least one line item with an amount greater than zero';
            showToast('error', message);
            return false;
        }
        
        // Additional validation for reference number when payment is not CASH
        const paymentMode = form.querySelector('input[name="payment_mode"]:checked')?.value;
        const referenceNo = form.querySelector('#reference').value;
        
        if (paymentMode && paymentMode !== 'CASH' && !referenceNo) {
            showToast('error', 'Reference number is required for ' + paymentMode + ' payments');
            form.querySelector('#reference').focus();
            return false;
        }
        
        return true;
    }
    

    // Set default account type for arrears tab to "Association Dues"
    function setDefaultAccountType() {
        // Get the select element in the arrears tab
        const arrearsSelect = document.querySelector('#arrearsLineItemsTable select[name="arrears_items[0][coa]"]');
        
        if (arrearsSelect) {
            // Look through all options to find "Association Dues"
            const options = arrearsSelect.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].text.includes("Association Dues")) {
                    arrearsSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    // Run when page loads
    setDefaultAccountType();

    // Also update the code to run this when switching to the arrears tab
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            if (e.target.id === 'arrears-tab') {
                // Add this line to set default account type when switching to arrears tab
                setDefaultAccountType();
                
                // Rest of the existing code for the arrears tab...
                document.getElementById('arrears_addressId').focus();
                // ...
            }
            // Rest of your existing tab-switching code...
        });
    });

    // Validate the Arrears Receivable form
    function validateArrearsReceivableForm(form) {
        // Check if we're in reversal mode
        const isReversalMode = document.getElementById('accountSaveBtn').getAttribute('data-reversal-mode') === 'true' || 
                            (document.getElementById('arrears_remarks').value || '').includes('CANCELLED OR');
        
        console.log("Arrears validation in reversal mode:", isReversalMode);
        
        // Basic HTML5 validation
        if (!form.checkValidity()) {
            console.log("Form validity check failed");
            form.reportValidity();
            return false;
        }
        
        // Check if the line item has a valid amount
        const lineItems = form.querySelectorAll('.arrears-amount-input');
        let hasValue = false;
        
        lineItems.forEach(input => {
            const value = parseFloat(input.value);
            // In reversal mode we want negative values, otherwise positive values
            if (isReversalMode && value < 0) {
                hasValue = true;
            } else if (!isReversalMode && value > 0) {
                hasValue = true;
            }
        });
        
        if (!hasValue) {
            const message = isReversalMode 
                ? 'Please add at least one line item with a negative amount for reversal'
                : 'Please add at least one line item with an amount greater than zero';
            showToast('error', message);
            return false;
        }
        
        // Additional validation for reference number when payment is not CASH
        const arrearsPaymentMode = form.querySelector('input[name="arrears_payment_mode"]:checked')?.value;
        const arrearsReferenceNo = form.querySelector('#arrears_reference').value;
        
        if (arrearsPaymentMode && arrearsPaymentMode !== 'CASH' && !arrearsReferenceNo) {
            showToast('error', 'Reference number is required for ' + arrearsPaymentMode + ' payments');
            form.querySelector('#arrears_reference').focus();
            return false;
        }
        
        return true;
    }
        
    // Function to handle payment mode display logic
    function initializePaymentModeLogic() {
        // For Account Receivable tab
        const accountReferenceContainer = document.querySelector('#reference').closest('.col-md-4');
        const accountPaymentRadios = document.querySelectorAll('input[name="payment_mode"]');
        const referenceField = document.getElementById('reference');
        
        // For HOA Monthly Dues tab
        const arrearsReferenceContainer = document.querySelector('#arrears_reference').closest('.col-md-4');
        const arrearsPaymentRadios = document.querySelectorAll('input[name="arrears_payment_mode"]');
        const arrearsReferenceField = document.getElementById('arrears_reference');
        
        // Event handlers for Account Receivable tab
        if (accountPaymentRadios) {
            accountPaymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'CASH') {
                        accountReferenceContainer.style.display = 'none';
                        referenceField.removeAttribute('required');
                        referenceField.value = ''; // Clear value
                        
                        // Move focus to another field if reference had focus
                        if (document.activeElement === referenceField) {
                            document.getElementById('remarks').focus();
                        }
                    } else {
                        accountReferenceContainer.style.display = 'block';
                        referenceField.setAttribute('required', 'required');
                        referenceField.focus();
                    }
                });
            });
        }
        
        // Event handlers for HOA Monthly Dues tab
        if (arrearsPaymentRadios) {
            arrearsPaymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'CASH') {
                        arrearsReferenceContainer.style.display = 'none';
                        arrearsReferenceField.removeAttribute('required');
                        arrearsReferenceField.value = ''; // Clear value
                        
                        // Move focus to another field if reference had focus
                        if (document.activeElement === arrearsReferenceField) {
                            document.getElementById('arrears_remarks').focus();
                        }
                    } else {
                        arrearsReferenceContainer.style.display = 'block';
                        arrearsReferenceField.setAttribute('required', 'required');
                        arrearsReferenceField.focus();
                    }
                });
            });
        }
        
        // Set initial state for both tabs
        if (accountReferenceContainer) {
            accountReferenceContainer.style.display = 'none';
            referenceField.removeAttribute('required');
        }
        
        if (arrearsReferenceContainer) {
            arrearsReferenceContainer.style.display = 'none';
            arrearsReferenceField.removeAttribute('required');
        }
        
        // Fix for address lookup focus issue - intercept focus events
        if (arrearsReferenceField) {
            arrearsReferenceField.addEventListener('focus', function(e) {
                const cashRadio = document.getElementById('arrears_cash');
                if (cashRadio && cashRadio.checked) {
                    e.preventDefault();
                    // Move focus to amount field instead
                    const amountInput = document.querySelector('.arrears-amount-input');
                    if (amountInput) {
                        amountInput.focus();
                    } else {
                        document.getElementById('arrears_serviceInvoiceNo').focus();
                    }
                }
            });
        }
        
        if (referenceField) {
            referenceField.addEventListener('focus', function(e) {
                const cashRadio = document.getElementById('cash');
                if (cashRadio && cashRadio.checked) {
                    e.preventDefault();
                    // Move focus to amount field instead
                    const amountInput = document.querySelector('.amount-input');
                    if (amountInput) {
                        amountInput.focus();
                    } else {
                        document.getElementById('serviceInvoiceNo').focus();
                    }
                }
            });
        }
    }

    // Initialize on page load
    initializePaymentModeLogic();


    // Attach change event to amount inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('amount-input')) {
            calculateTotal();
        }
    });
    
    // Auto-focus amount field after account type selection
    document.addEventListener('change', function(e) {
        // For Account Receivable tab
        if (e.target.matches('#lineItemsTable select[name^="items"]')) {
            const row = e.target.closest('tr');
            const amountInput = row.querySelector('.amount-input');
            if (amountInput && e.target.value) {
                amountInput.focus();
            }
        }
        
        // For HOA Monthly Dues tab
        if (e.target.matches('#arrearsLineItemsTable select[name^="arrears_items"]')) {
            const row = e.target.closest('tr');
            const amountInput = row.querySelector('.arrears-amount-input');
            if (amountInput && e.target.value) {
                amountInput.focus();
            }
        }
    });

    
    // Initialize
    attachRemoveLineListeners();
    calculateTotal();
    
    // Handle tab switching events
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            // Set focus on the first input field of the active tab
            setTimeout(() => {
                if (e.target.id === 'arrears-tab') {
                    document.getElementById('arrears_addressId').focus();
                    
                    // Reset to CASH payment option for HOA Monthly Dues tab
                    const cashRadio = document.getElementById('arrears_cash');
                    if (cashRadio && !cashRadio.checked) {
                        cashRadio.checked = true;
                        cashRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Hide reference field
                    const arrearsReferenceContainer = document.querySelector('#arrears_reference').closest('.col-md-4');
                    if (arrearsReferenceContainer) arrearsReferenceContainer.style.display = 'none';
                    
                } else if (e.target.id === 'account-tab') {
                    document.getElementById('serviceInvoiceNo').focus();
                    
                    // Reset to CASH payment option for Account Receivable tab
                    const cashRadio = document.getElementById('cash');
                    if (cashRadio && !cashRadio.checked) {
                        cashRadio.checked = true;
                        cashRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Hide reference field
                    const accountReferenceContainer = document.querySelector('#reference').closest('.col-md-4');
                    if (accountReferenceContainer) accountReferenceContainer.style.display = 'none';
                }
            }, 100); // Small delay to ensure proper focus
        });
    });

    
    // BACKUP FOCUS: Set focus again after a delay in case the immediate focus failed
    // This adds redundancy to make sure the focus is set
    setTimeout(() => {
        const activeTabId = document.querySelector('.tab-pane.active').getAttribute('id');
        if (activeTabId === 'arrears') {
            document.getElementById('arrears_addressId').focus();
        } else if (activeTabId === 'account') {
            document.getElementById('serviceInvoiceNo').focus();
        }
    }, 500);

    // Store the initial user name for reset functionality
    const currentUserName = "{{ $currentUserName }}";

    // Optional: Add reset functionality to restore original user
    document.querySelectorAll('#receivedBy, #arrears_receivedBy').forEach(field => {
        field.addEventListener('focus', function() {
            // Add double-click handler to reset to original name
            field.addEventListener('dblclick', function() {
                field.value = currentUserName;
            });
        });
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            // Reset any reversal mode when switching tabs
            if (typeof resetReversalMode === 'function') {
                resetReversalMode();
            }
            
            // Rest of your existing tab-switching code...
        });
    });
    
    // Add this to your cancel button click handler
    document.querySelector('button.btn-secondary').addEventListener('click', function() {
        // Reset reversal mode when canceling
        if (typeof resetReversalMode === 'function') {
            resetReversalMode();
        }
        
        // Your existing cancel code...
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

// Add an additional window load event for extra reliability with focus
window.addEventListener('load', function() {
    // This runs after everything (images, styles, etc.) is fully loaded
    setTimeout(() => {
        const activeTabId = document.querySelector('.tab-pane.active').getAttribute('id');
        if (activeTabId === 'arrears') {
            document.getElementById('arrears_addressId').focus();
        } else if (activeTabId === 'account') {
            document.getElementById('serviceInvoiceNo').focus();
        }
    }, 300);
});

// Add this to the existing DOMContentLoaded event handler or create a new one
document.addEventListener('DOMContentLoaded', function() {
    // Check for flash messages from the server
    @if(session('success'))
        showToast('success', "{{ session('success') }}");
    @endif
    
    @if(session('error'))
        showToast('error', "{{ session('error') }}");
    @endif
    
    @if(session('info'))
        showToast('info', "{{ session('info') }}");
    @endif
});
</script>
@php
// Update this version when you change your JS files
$jsVersion = '1.2.0';
@endphp
<script src="{{ asset('assets/js/receivable-address-lookup.js') }}?v={{ $jsVersion }}"></script>
<script src="{{ asset('assets/js/transaction-reversal.js') }}?v={{ $jsVersion }}"></script>
<script src="{{ asset('assets/js/form-reset.js') }}?v={{ $jsVersion }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to do a double redirect
    @if(session('double_redirect'))
        // First redirect is already done by Laravel, now do the second one
        setTimeout(function() {
            window.location.href = "{{ route('accounts.receivables', ['tab' => request()->get('tab', 'account')]) }}";
        }, 1000); // Wait 1 second before second redirect
    @endif
    
    // Your existing code for flash messages...
    @if(session('success'))
        showToast('success', "{{ session('success') }}");
    @endif
    
    @if(session('error'))
        showToast('error', "{{ session('error') }}");
    @endif
    
    @if(session('info'))
        showToast('info', "{{ session('info') }}");
    @endif
});
</script>
@endsection