@extends('layouts.app')

@section('title', 'Account Payable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

<div class="container-fluid px-4">
    <!-- Form starts here -->
    <form action="{{route('accounts.payables.store')}}" method="POST" id="payableForm">
        @csrf
        
        <!-- Header Section and buttons in the same row -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0 text-danger">Account Payable</h4>
            </div>
            @if (auth()->user()->role !== 3)
            <div>
                <button type="button" class="btn btn-secondary btn-sm me-2"
                        onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('accounts.payables') }}'; }, 1000);">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
            @endif
        </div>
        
        <!-- Main Card -->
        <div class="card shadow-sm border-danger border-top border-3 mb-3">
            <div class="card-body p-3">
                <!-- Header Section -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="mb-2">
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
                        <div class="mb-2">
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
                        <div class="mb-2">
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
                <div class="card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="table-responsive mb-3">
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
                                            @if (auth()->user()->role !== 3)
                                            <button type="button"
                                            class="btn btn-link text-danger remove-line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    @if (auth()->user()->role !== 3)
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link text-primary add-line">
                                                <i class="bi bi-plus-circle"></i> Add Line
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <div class="d-flex align-items-center">
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

                <!-- Payment Details -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="mb-2">
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
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
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
                                        rows="3"
                                        maxlength="300"
                                        style="resize: none;"
                                        autocomplete="off"
                                    ></textarea>
                                    <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="charCount">0/300</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
            </form>
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
                <span id="successMessage">Account payable created successfully</span>
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
                    <h5 class="modal-title" id="transactionChoiceModalLabel">Voucher Found - Choose Action</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Existing Voucher Found:</strong> This voucher already exists in the system.
                    </div>
                    
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2">Transaction Details:</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Voucher Number:</strong> <span id="modalVoucherNumber">-</span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="modalTransactionDate">-</span></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Amount:</strong> <span id="modalTransactionAmount">-</span></p>
                                <p class="mb-0"><strong>Payee:</strong> <span id="modalPayeeName">-</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-3">What would you like to do with this voucher?</p>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-info btn-lg" id="editTransactionBtn">
                            <i class="bi bi-pencil-square me-2"></i>Edit Transaction
                            <small class="d-block text-white-50">Modify the details of this voucher</small>
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" id="cancelTransactionBtn">
                            <i class="bi bi-x-circle me-2"></i>Cancel Transaction
                            <small class="d-block text-white-50">Reverse/cancel this voucher</small>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Exit</button>
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
    .add-line {
        font-size: 0.813rem;
    }
    .table > :not(caption) > * > * {
        padding: 0.25rem;
    }
    h4.text-danger {
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
    
    /* Form select styling */
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

    .form-select.enhanced {
        cursor: pointer;
    }
    
    /* Validation styling */
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
        .row.g-2.mb-3 .col-md-6 .d-flex.flex-wrap.gap-3 {
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
        
        /* Fix column widths on mobile */
        .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
        .col-md-5, .col-md-6, .col-md-7, .col-md-8 {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        /* Better spacing for the date field specifically */
        #date {
            width: 100%;
            max-width: 100% !important;
        }
        
        /* Adjust spacing for remarks section */
        .row.g-2 .col-md-12 .row.g-2.align-items-start {
            flex-direction: column;
        }
        
        .row.g-2 .col-md-12 .row.g-2.align-items-start > div {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }
        
        /* Make table more compact on mobile */
        .table > :not(caption) > * > * {
            padding: 0.2rem;
        }
        
        .table th {
            font-size: 0.7rem;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {    
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

@push('scripts')
<script src="{{ $isNgrok ? secure_asset('assets/js/payables.js') : asset('assets/js/payables.js') }}"></script>
<script src="{{ $isNgrok ? secure_asset('assets/js/payable-transaction-management.js') : asset('assets/js/payable-transaction-management.js') }}"></script>
@endpush
@endsection