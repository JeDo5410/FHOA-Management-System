@extends('layouts.app')

@section('title', 'Resident Data')

@section('content')
@php
$isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp
<style>
    .card {
        border-radius: 8px;
        border: 1px solid #dee2e6;
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
    .table > :not(caption) > * > * {
        padding: 0.25rem;
    }

    .resident-table td {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .light-placeholder::placeholder {
        opacity: 0.4;
        font-size: 0.813rem;
    }

    .light-placeholder option.placeholder {
        color: #999;
        font-size: 0.813rem;
    }

    .resident-table .form-control,
    .resident-table .form-select {
        background-color: #fcfcfc;
    }

    .table th {
        font-size: 0.75rem;
        font-weight: 400;
        color: #666;
        padding-bottom: 0.75rem !important;
    }

    /* Base select styling */
    .form-select {
        color: #212529;
        opacity: 1;
        cursor: pointer;
    }

    /* Placeholder styling */
    .form-select.placeholder {
        color: #999;
        opacity: 0.6;
    }

    /* Style only the placeholder option */
    .form-select option[value=""] {
        color: #999;
        opacity: 0.6;
    }

    /* Ensure options in dropdown are always full opacity */
    .form-select option:not([value=""]) {
        color: #212529 !important;
        opacity: 1 !important;
    }

    /* Ensure full opacity when focused */
    .form-select:focus {
        color: #212529;
        opacity: 1;
    }

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

    .vehicle-table th {
        font-size: 0.75rem;
        font-weight: 500;
        color: #666;
        padding-bottom: 0.75rem !important;
    }

    .vehicle-table td {
        padding: 0.5rem 0.25rem;
    }

    .btn-link {
        padding: 0;
        font-size: 0.875rem;
    }

    .remove-vehicle {
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .remove-vehicle:hover {
        opacity: 1;
    }

    /* Address Dropdown Styles - UPDATED */
    .address-dropdown {
        position: absolute;
        width: 100%;
        max-height: 280px;
        overflow-y: auto;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        display: none;
        z-index: 1050;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
        padding: 10px 16px;
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

    .address-list li:hover .address-id,
    .address-list li:hover .member-name,
    .address-list li:hover .address-formatted {
        color: #2563eb;
    }

    .address-list li.active .address-id,
    .address-list li.active .member-name,
    .address-list li.active .address-formatted {
        color: #2563eb;
        font-weight: 500;
    }

    .address-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .address-dropdown::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .address-dropdown::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }

    /* Loading and error states */
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

    .error-icon {
        font-size: 1rem;
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

    .toast {
        transition: opacity 0.3s ease-in-out;
    }
    
    /* Additional styling for toasts */
    #successToast, #errorToast {
        min-width: 280px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .toast-body {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }
    </style>
<div class="container-fluid px-4">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('residents.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_form_token" value="{{ Str::random(40) }}">
                <!-- Container for tabs and buttons -->
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <ul class="nav nav-tabs border-bottom-0" id="residentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="resident-tab" data-bs-toggle="tab" 
                                        data-bs-target="#resident" type="button" role="tab" 
                                        aria-controls="resident" aria-selected="true">
                                    Resident Data
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="vehicle-tab" data-bs-toggle="tab" 
                                        data-bs-target="#vehicle" type="button" role="tab" 
                                        aria-controls="vehicle" aria-selected="false">
                                    Car Sticker
                                </button>
                            </li>
                        </ul>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm me-2" 
                                onclick="showToast('info', 'Operation cancelled'); setTimeout(function() { window.location.href='{{ route('residents.residents_data') }}'; }, 1000);">
                                Cancel
                            </button>  
                            <button type="button" class="btn btn-info btn-sm me-2" id="memberLookupBtn">
                                <i class="bi bi-search"></i> Find
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </div>
                    <!-- Add a horizontal separator line -->
                    <hr class="mt-0 mb-4">
                </div>
                <!-- Tab Content -->
                <div class="tab-content" id="residentTabsContent">
                    <!-- Resident Data Tab -->
                    <div class="tab-pane fade show active" id="resident" role="tabpanel" 
                         aria-labelledby="resident-tab">
                        <div class="row g-5">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <h6 class="mb-2">Members Information</h6>
                                <!-- Address ID Field - Searchable -->
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label for="addressId" class="col-form-label">Address ID</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm address-id-input" 
                                            id="resident_addressId" 
                                            name="address_id" 
                                            data-tab="resident"
                                            placeholder="Enter Address ID"
                                            autocomplete="off"> 
                                    </div>
                                </div>

                                <!-- Address Field - Disabled -->
                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-4">
                                        <label for="address" class="col-form-label">Address</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="resident_address" 
                                            name="address" 
                                            disabled 
                                            data-tab="resident"
                                            aria-labe   l="Address field auto-filled from Address ID">                                        <small class="text-muted">**auto-filled based on Address ID**</small>
                                    </div>
                                </div>

                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-4">
                                        <label for="membersName" class="col-form-label">Member's Name</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="resident_membersName" 
                                            name="mem_name" 
                                            data-tab="resident">
                                    </div>
                                </div>

                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-4">
                                        <label class="col-form-label">Resident Type</label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="d-flex gap-3">
                                            @foreach($memberTypes as $type)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="mem_typecode" 
                                                           id="type_{{ $type->mem_typecode }}" 
                                                           value="{{ $type->mem_typecode }}">
                                                    <label class="form-check-label" for="type_{{ $type->mem_typecode }}">
                                                        {{ $type->mem_typedescription }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-4">
                                        <label for="tenantSpa" class="col-form-label">Tenant/SPA</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" 
                                            class="form-control form-control-sm" 
                                            id="resident_tenantSpa" 
                                            name="tenant_spa" 
                                            data-tab="resident">                                    
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <h6 class="mb-2">Contact Information</h6>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label for="contactNumber" class="col-form-label">Contact Number</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="tel" 
                                        class="form-control form-control-sm" 
                                        id="contactNumber" 
                                        name="contact_number"
                                        pattern="[0-9]*"
                                        inputmode="numeric"
                                        title="Please enter numbers only (0-9)">                                    </div>
                                </div>

                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-4">
                                        <label for="email" class="col-form-label">Email</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="email" class="form-control form-control-sm" id="email" name="email">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resident Information Section -->
                        <h6 class="mb-3 mt-5">Resident Information</h6>
                        <div class="row">
                            <div class="col-10 mx-auto"> <!-- Reduced width and centered -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless resident-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 45%" class="pb-2">Resident Name</th>
                                                <th style="width: 45%" class="pb-2">Relationship</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < 10; $i++)
                                            <tr>
                                                <td class="py-2">
                                                    <input type="text" class="form-control form-control-sm light-placeholder" 
                                                        name="residents[{{$i}}][name]" 
                                                        placeholder="Enter name">
                                                </td>
                                                <td class="py-2">
                                                    <select class="form-select form-select-sm" name="residents[{{$i}}][relationship]">
                                                        <option value="" selected>Select</option>
                                                        <option value="spouse">Spouse</option>
                                                        <option value="child">Child</option>
                                                        <option value="parent">Parent</option>
                                                        <option value="sibling">Sibling</option>
                                                        <option value="other">Other</option>
                                                    </select>                                                                                        
                                                <td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Section -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="member_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm" 
                                    id="member_remarks" 
                                    name="member_remarks" 
                                    rows="2"
                                    maxlength="100"></textarea>
                                <small class="text-muted">Maximum 100 characters</small>
                            </div>
                        </div>
                    </div>

                    <!-- 
                     --  Car Sticker Tab
                     -->
                        
                    <div class="tab-pane fade" id="vehicle" role="tabpanel" aria-labelledby="vehicle-tab">
                        <!-- Address ID Field - Searchable -->
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <label for="addressId" class="col-form-label">Address ID</label>
                            </div>
                            <div class="col-md-4">
                            <input type="text" 
                                class="form-control form-control-sm address-id-input" 
                                id="vehicle_addressId" 
                                name="address_id" 
                                data-tab="vehicle"
                                placeholder="Enter Address ID"
                                autocomplete="off">
                            </div>
                        </div>

                        <!-- Address Field - Disabled -->
                        <div class="row g-2 align-items-center mt-1">
                            <div class="col-md-2">
                                <label for="address" class="col-form-label">Address</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    id="vehicle_address" 
                                    name="address" 
                                    disabled 
                                    data-tab="vehicle"
                                    aria-label="Address field auto-filled from Address ID">
                                <small class="text-muted">**auto-filled based on Address ID**</small>
                            </div>
                        </div>
                        <div class="row g-2 align-items-center mt-1">
                            <div class="col-md-2">
                                <label for="membersName" class="col-form-label">Member's Name</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" 
                                class="form-control form-control-sm" 
                                id="vehicle_membersName" 
                                name="mem_name" 
                                data-tab="vehicle" 
                                disabled>                                    
                            </div>
                            </div>
                        <div class="row g-2 align-items-center mt-1">
                            <div class="col-md-2">
                                <label for="tenantSpa" class="col-form-label">Tenant/SPA</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" 
                                class="form-control form-control-sm" 
                                id="vehicle_tenantSpa" 
                                name="tenant_spa" 
                                data-tab="vehicle" 
                                disabled>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12 mx-auto mt-5">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless vehicle-table">
                                        <thead>
                                            <tr>
                                                <th>Sticker Number</th>
                                                <th>Vehicle Type</th>
                                                <th>Vehicle Maker</th>
                                                <th>Color</th>
                                                <th>OR Number</th>
                                                <th>CR Number</th>
                                                <th>Plate Number</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicleTableBody">
                                            @for ($i = 0; $i < 5; $i++)
                                            <tr class="vehicle-row">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][car_sticker]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_type]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_maker]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_color]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_OR]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_CR]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="vehicles[{{$i}}][vehicle_plate]">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" 
                                                        name="vehicles[{{$i}}][vehicle_active]">
                                                        <option value="0">Active</option>
                                                        <option value="1">Inactive</option>
                                                </select>
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Remarks Section -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="vehicle_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm" 
                                    id="vehicle_remarks" 
                                    name="vehicle_remarks" 
                                    rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    // Form submission prevention that preserves CSRF protection
                    (function() {
                        const form = document.querySelector('form');
                        const submitBtn = document.querySelector('button[type="submit"]');
                        
                        if (!form || !submitBtn) return;
                        
                        // Status flag - attached directly to the form to prevent conflicts
                        form._isSubmitting = false;
                        
                        // Create a hidden input to track if form has been submitted
                        const submittedField = document.createElement('input');
                        submittedField.type = 'hidden';
                        submittedField.name = '_form_submitted';
                        submittedField.value = '0';
                        form.appendChild(submittedField);
                        
                        // The final submit handler - this uses capture phase to run first
                        form.addEventListener('submit', function(e) {
                            // Check if already submitted
                            if (form._isSubmitting || submittedField.value === '1') {
                                console.log('Preventing duplicate submission');
                                e.preventDefault();
                                return false;
                            }
                            
                            // Mark form as submitting
                            form._isSubmitting = true;
                            submittedField.value = '1';
                            
                            // Disable button and show spinner
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                            
                            // Show toast if function exists
                            if (typeof showToast === 'function') {
                                showToast('info', 'Processing your request...');
                            }
                            
                            // Let the form submit normally with CSRF token intact
                            return true;
                        }, true); // true = use capture phase to run before other handlers
                        
                        // Handle Enter key - prevent it from submitting if already submitting
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                                if (form._isSubmitting || submittedField.value === '1') {
                                    e.preventDefault();
                                    return false;
                                }
                            }
                        }, true);
                    })();
                    </script>
                </form>
        </div>
    </div>
    <!-- Member Lookup Modal -->
    <div class="modal fade" id="memberLookupModal" tabindex="-1" aria-labelledby="memberLookupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberLookupModalLabel">Member Lookup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="memberNameSearch" class="form-label">Search by Name or Tenant/SPA</label>
                        <input type="text" class="form-control" id="memberNameSearch" 
                            placeholder="Type to search..." autocomplete="off">
                        <div class="form-text">Enter at least 2 characters to search</div>
                    </div>
                    <div id="memberSearchResults" class="mt-2">
                        <!-- Search results will be displayed here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script>
        document.querySelectorAll('.form-select').forEach(select => {
            const updatePlaceholderState = () => {
                select.classList.toggle('placeholder', !select.value);
            };
            
            select.addEventListener('change', updatePlaceholderState);
            // Initialize state
            updatePlaceholderState();
        });
        </script>
        @php
        // Update this version when you change your JS files
        $jsVersion = '1.0.3';
        @endphp

        <script src="{{ asset('assets/js/address-lookup.js') }}?v={{ $jsVersion }}"></script>
        <script src="{{ asset('assets/js/vehicle-table-navigation.js') }}?v={{ $jsVersion }}"></script>
        <script src="{{ asset('assets/js/member-lookup.js') }}?v={{ $jsVersion }}"></script>
        <script src="{{ asset('assets/js/resident-form-behaviors.js') }}?v={{ $jsVersion }}"></script>
<script>
        // Toast Notification Handler
        document.addEventListener('DOMContentLoaded', function() {
            // Check for flash messages from the session
            @if(session('success'))
                showToast('success', '{{ session('success') }}');
            @endif
            
            @if(session('error'))
                showToast('error', '{{ session('error') }}');
            @endif
            
            // Handle form submission
            const form = document.getElementById('residentForm');
            if (form) {
                form.addEventListener('submit', function() {
                    // You can add form validation here if needed
                    const addressId = document.getElementById('resident_addressId').value;
                    if (!addressId) {
                        showToast('error', 'Please enter an Address ID');
                        event.preventDefault();
                        return false;
                    }
                });
            }
        });
        
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
<script>
    // Phone number validation - allow any characters but limit to 45 chars
    document.addEventListener('DOMContentLoaded', function() {
        const contactInput = document.getElementById('contactNumber');
        
        if (contactInput) {
            // Remove any existing pattern and inputmode attributes
            contactInput.removeAttribute('pattern');
            contactInput.removeAttribute('inputmode');
            contactInput.setAttribute('maxlength', '45');
            contactInput.setAttribute('title', 'Enter contact number (max 45 characters)');
            
            // Replace existing event listeners with new one that only limits length
            // Clear any other restrictive event listeners by cloning and replacing
            const newInput = contactInput.cloneNode(true);
            contactInput.parentNode.replaceChild(newInput, contactInput);
            
            // Add back our single validation for length
            newInput.addEventListener('input', function(e) {
                if (this.value.length > 45) {
                    this.value = this.value.slice(0, 45);
                }
            });
        }
    });
    </script>        
        
@endsection
