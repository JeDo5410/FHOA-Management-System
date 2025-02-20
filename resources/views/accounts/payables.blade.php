@extends('layouts.app')

@section('title', 'Account Payable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

<div class="container-fluid px-4">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{route('accounts.payables.store')}}" method="POST" id="payableForm">
                @csrf
                
                <!-- Header Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                <label for="voucherNo" class="col-form-label">Voucher No.</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    id="voucherNo" 
                                    name="voucher_no"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                <label for="payee" class="col-form-label">Payee</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    id="payee" 
                                    name="payee"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                <label for="date" class="col-form-label">Date</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="date" 
                                    class="form-control form-control-sm" 
                                    id="date" 
                                    name="date"
                                    required>
                            </div>
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
                                                required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="form-control form-control-sm amount-input" 
                                                name="items[0][amount]"
                                                step="0.01" 
                                                required>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
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
                        <div class="d-flex align-items-center gap-4">
                            <label class="form-label mb-0">Mode of Payment:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="pettyCash" value="PETTY CASH" required>
                                <label class="form-check-label" for="pettyCash">Petty Cash</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_mode" 
                                    id="cash" value="CASH">
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
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end; padding-left: 5px">
                                    <label for="reference" class="col-form-label">Reference #</label>
                                </div>
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
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="row g-2 align-items-start">
                            <div class="col-md-1">
                                <div class="container" style="text-align: end">
                                    <label for="remarks" class="col-form-label">Remarks:</label>
                                </div>
                            </div>
                            <div class="col-md-11">
                                <div class="position-relative">
                                    <textarea 
                                        class="form-control form-control-sm" 
                                        id="remarks" 
                                        name="remarks"
                                        rows="1"
                                        maxlength="45"
                                        style="resize: none;"
                                    ></textarea>
                                    <small class="text-muted position-absolute end-0 bottom-0 pe-2" id="charCount">0/45</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                

                <!-- Form Buttons -->
                <div class="row">
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-secondary btn-sm me-2" 
                                onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('accounts.payables') }}'; }, 1000);">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
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


</style>

@push('scripts')
<script src="{{ $isNgrok ? secure_asset('assets/select2/js/select2.min.js') : asset('assets/select2/js/select2.min.js') }}"></script>
<script src="{{ $isNgrok ? secure_asset('assets/js/payables.js') : asset('assets/js/payables.js') }}"></script>
@endpush
@endsection