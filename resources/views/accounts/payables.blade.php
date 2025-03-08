@extends('layouts.app')

@section('title', 'Account Payable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp
{{-- @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif --}}
<div class="container-fluid px-4">
    <!-- Form starts here -->
    <form action="{{route('accounts.payables.store')}}" method="POST" id="payableForm">
        @csrf
        
        <!-- Header Section and buttons in the same row -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 text-danger">Account Payable</h4>
            </div>
            <div>
                <button type="button" class="btn btn-secondary btn-sm me-2" 
                        onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('accounts.payables') }}'; }, 1000);">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </div>
        
        <!-- Note: Restored border-top styling -->
        <div class="card shadow-sm border-danger border-top border-3">
            <div class="card-body p-4">
                <!-- Header Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="voucherNo" class="form-label">Voucher No.</label>
                            <input type="text" 
                                class="form-control form-control-sm" 
                                id="voucherNo" 
                                name="voucher_no"
                                autocomplete="off"
                                required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payee" class="form-label">Payee</label>
                            <input type="text" 
                                class="form-control form-control-sm" 
                                id="payee" 
                                name="payee"
                                autocomplete="off"
                                required>
                        </div>
                    </div>
                    <div class="col-md-4">
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
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless" id="lineItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Particular</th>
                                        <th style="width: 20%">Amount</th>
                                        <th style="width: 30%">Account Type</th>
                                        <th style="width: 10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="line-item">
                                        <td>
                                            <input type="text" 
                                                class="form-control form-control-sm" 
                                                name="items[0][particular]"
                                                autocomplete="off" 
                                                required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="form-control form-control-sm amount-input" 
                                                name="items[0][amount]"
                                                step="1" 
                                                required>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm enhanced" 
                                                name="items[0][account_type]" 
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
                                            <button type="button" 
                                            class="btn btn-link text-danger remove-line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <button type="button" class="btn btn-link text-primary add-line">
                                                <i class="bi bi-plus-circle"></i> Add Line
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" id="totalAmount" name="total_amount" readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Mode of Payment:</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="pettyCash" value="PETTY CASH" required>
                                <label class="form-check-label" for="pettyCash">Petty Cash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="cash" value="CASH">
                                <label class="form-check-label" for="cash">Cash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="gcash" value="GCASH">
                                <label class="form-check-label" for="gcash">GCash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="check" value="CHECK">
                                <label class="form-check-label" for="check">Check</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference #</label>
                            <input type="text" 
                                class="form-control form-control-sm" 
                                id="reference" 
                                name="reference_no"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Remarks Field -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks:</label>
                            <div class="position-relative">
                                <textarea 
                                    class="form-control form-control-sm" 
                                    id="remarks" 
                                    name="remarks"
                                    rows="1"
                                    maxlength="45"
                                    style="resize: none;"
                                    autocomplete="off"
                                ></textarea>
                                <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="charCount">0/45</small>
                            </div>
                        </div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>

<!-- Info Toast -->
<div class="toast-container position-fixed" style="top: 20px; right: 20px; z-index: 1060;">
    <div id="infoToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-info-circle me-2"></i>
                <span id="infoMessage">Information message</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>    
</div>

<style>
    .form-select {
    appearance: none;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    color: #212529;
    display: block;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    padding: 0.25rem 2.25rem 0.25rem 0.75rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-select.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-select.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

/* Add a custom class for our enhanced selects */
.form-select.enhanced {
    cursor: pointer;
}

/* Custom styles for better mobile experience */
@media (max-width: 768px) {
    .form-select {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }
}
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
    .add-line {
        font-size: 0.813rem;
    }
    .table > :not(caption) > * > * {
        padding: 0.25rem;
    }

    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #dc3545;
    }

    .form-control.is-invalid ~ .invalid-feedback {
        display: block;
    }

    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .was-validated .form-control:invalid:focus,
    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .was-validated .form-control:invalid ~ .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback {
        display: block;
    }

    h4.text-danger {
        color: #dc3545;
        font-weight: 500;
    }

    .header-icon {
        font-size: 2rem;
    }
    .card.border-danger {
        border-top-width: 3px !important;
        border-right: none;
        border-bottom: none;
        border-left: none;
    }
    /* Responsive Form Layout Styles */
    @media (max-width: 768px) {
    /* General row transformation for mobile */
    .row.g-2.align-items-center,
    .row.g-3.align-items-center,
    .row.g-3 .col-md-4 .row.g-2.align-items-center,
    .row.g-3 .col-md-6 .row.g-2.align-items-center,
    .d-flex.align-items-center,
    .d-flex.align-items-center.gap-4 {
        flex-direction: column;
        align-items: flex-start !important;
        width: 100%;
    }
    
    /* Make labels and inputs full width on mobile */
    .row.g-2.align-items-center > div,
    .row.g-3.align-items-center > div,
    .row.g-3 .col-md-4 .row.g-2.align-items-center > div,
    .row.g-3 .col-md-6 .row.g-2.align-items-center > div {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        margin-bottom: 0.5rem;
    }
    
    /* Center-align the labels on mobile */
    .col-form-label {
        text-align: left !important;
        margin-bottom: 0.25rem;
        padding-bottom: 0;
    }
    
    /* Reset the text-align end styling */
    .container[style*="text-align: end"] {
        text-align: left !important;
        padding-left: 0 !important;
    }
    
    /* Full-width inputs */
    .form-control,
    .form-select {
        width: 100%;
    }
    
    /* Fix column widths on mobile */
    .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
    .col-md-5, .col-md-6, .col-md-7, .col-md-8,
    .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    /* Better spacing for the date field specifically */
    #date {
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
    
    /* Handle payment mode radio buttons */
    .d-flex.align-items-center.gap-4 {
        gap: 0.5rem !important;
    }
    
    .form-check.form-check-inline {
        margin-bottom: 0.5rem;
        margin-left: 0 !important;
        display: flex;
        align-items: center;
    }
    
    /* Make sure the form buttons stay in line */
    .d-flex.justify-content-between.align-items-center {
        flex-wrap: wrap;
        gap: 1rem;
    }
    }

    /* Label style enhancement for all screen sizes */
    .col-form-label {
    font-weight: 500;
    }

    /* Transition for smooth responsive changes */
    .row, .d-flex, .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
    .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9,
    .col-md-10, .col-md-11, .col-md-12 {
    transition: all 0.3s ease-in-out;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style>

@push('scripts')
{{-- <script src="{{ $isNgrok ? secure_asset('assets/select2/js/select2.min.js') : asset('assets/select2/js/select2.min.js') }}"></script> --}}
<script src="{{ $isNgrok ? secure_asset('assets/js/payables.js') : asset('assets/js/payables.js') }}"></script>
@endpush
@endsection