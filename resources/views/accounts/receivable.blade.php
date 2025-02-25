@extends('layouts.app')

@section('title', 'Account Receivable')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

<div class="container-fluid px-4">
    <!-- Form starts here -->
    <form action="{{route('accounts.receivables.store')}}" method="POST" id="receivableForm">
        @csrf
        
        <!-- Header Section and buttons in the same row -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 text-success">Account Receivable</h4>
            </div>
            <div>
                <button type="button" class="btn btn-secondary btn-sm me-2" 
                        onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('accounts.receivables') }}'; }, 1000);">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </div>
        
        <!-- Note: Restored border-top styling -->
        <div class="card shadow-sm border-success border-top border-3">
            <div class="card-body p-4">
                <!-- Header Section -->
                <div class="row g-3 mb-4">                    
                    <div class="col-md-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                <label for="address" class="col-form-label">Address</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    id="address" 
                                    name="address"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                <label for="receivedFrom" class="col-form-label">Received From</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    id="receivedFrom" 
                                    name="received_from"
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
                                        <th style="width: 40%">Charts of Account (COA)</th>
                                        <th style="width: 20%">Amount</th>
                                        <th style="width: 30%">Address ID</th>
                                        <th style="width: 10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="line-item">
                                        <td>
                                            <input type="text" 
                                                class="form-control form-control-sm" 
                                                name="items[0][coa]" 
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
                                            <input type="text" 
                                                class="form-control form-control-sm" 
                                                name="items[0][address_id]" 
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

                <!-- Received By Field -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="container" style="text-align: end">
                                    <label for="receivedBy" class="col-form-label">Received By</label>
                                </div>
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
                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-4">
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
</style>

@push('scripts')
<script src="{{ $isNgrok ? secure_asset('assets/select2/js/select2.min.js') : asset('assets/select2/js/select2.min.js') }}"></script>
<script src="{{ $isNgrok ? secure_asset('assets/js/receivables.js') : asset('assets/js/receivables.js') }}"></script>
@endpush
@endsection
