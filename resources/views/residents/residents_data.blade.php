@extends('layouts.app')

@section('title', 'Resident Data')

@section('content')
@php
$isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp
<div class="container-fluid px-4">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('residents.store') }}" method="POST">
                @csrf

                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-4" id="residentTabs" role="tablist">
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
                                            placeholder="Enter Address ID"> 
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
                                        <input type="tel" class="form-control form-control-sm" id="contactNumber" name="contact_number">
                                    </div>
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
                                    rows="2"></textarea>
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
                                placeholder="Enter Address ID">
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

                <!-- Form Buttons -->
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-secondary btn-sm me-2" 
                                onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
        font-weight: 400;  /* Reduced from 500 */
        font-size: 0.813rem;  /* Slightly smaller than previous 0.875rem */
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

    .address-list li {
        padding: 8px 16px;
        cursor: pointer;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .address-id {
        font-weight: 500;
        color: #1e293b;
    }

    .address-formatted {
        font-size: 0.75rem;
        color: #64748b;
    }

    .address-list li:hover .address-id,
    .address-list li:hover .address-formatted {
        color: #2563eb;
    }

    .address-list li.active .address-id,
    .address-list li.active .address-formatted {
        color: #2563eb;
    }

    .address-list li {
        padding: 8px 16px;
        cursor: pointer;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
    }

    .address-list li:hover {
        background-color: #f1f5f9;
    }

    .dropdown-error {
        padding: 12px 16px;
        color: #dc2626;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dropdown-loading {
        padding: 12px 16px;
        color: #475569;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
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
    </style>
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
        <script src="{{ $isNgrok ? secure_asset('assets/js/address-lookup.js') : asset('assets/js/address-lookup.js') }}"></script>

        
@endsection
