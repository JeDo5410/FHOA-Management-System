@extends('layouts.app')

@section('content')

<style>
    /* General text styles */
    td, th {
        font-size: 11px;
    }
    h4 {
        font-size: 1.5rem;
    }
    .card-title {
        font-size: 1.5rem;
    }
    .btn-primary, .btn-success, .btn-info, .btn-outline-secondary {
        font-size: 12px;
    }
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-responsive {
        overflow-y: auto;
        max-height: 70vh;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa; /* Match your table header background */
        z-index: 10;
        /* Add box-shadow to create a visible separation */
        box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
    }
    .table-container {
        max-width: 100%;
    }
    .date-cell {
        min-width: 100px;
    }
    .actions-cell {
        min-width: 100px; 
    }
    .last-payment-cell {
        min-width: 200px;
    }
    /* Add these styles to your existing style section */
    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
    }

    .filter-input-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-label {
        font-weight: 600;
        margin-bottom: 0;
        white-space: nowrap;
    }

    .filter-input {
        min-width: 180px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 6px 12px;
        transition: border-color 0.15s ease-in-out;
    }

    .filter-input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .filter-checkbox-container {
        display: flex;
        align-items: center;
        margin-left: 8px;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .filter-actions {
            margin-left: 0;
            margin-top: 16px;
            width: 100%;
            justify-content: flex-end;
        }
    }

    .btn {
        border-radius: 4px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }   

    .table-responsive {
        overflow-y: auto;
        overflow-x: hidden; /* Hide the original horizontal scrollbar */
        max-height: 70vh;
        position: relative;
        padding-bottom: 16px; /* Space for the sticky scrollbar */
    }

    /* Create a sticky scrollbar container */
    .sticky-scrollbar-container {
        position: sticky;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 16px;
        background-color: #f8f9fa;
        overflow-x: auto;
        overflow-y: hidden;
        z-index: 100;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
    }

    /* The scrollbar mimic element */
    .scrollbar-content {
        height: 1px;
    }

    /* Style only the sticky scrollbar */   
    .table-responsive, .sticky-scrollbar-container {
        scrollbar-width: thin; /* For Firefox */
        scrollbar-color: #aaa #f1f1f1; /* For Firefox */
    }

    .table-responsive::-webkit-scrollbar, 
    .sticky-scrollbar-container::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-thumb, 
    .sticky-scrollbar-container::-webkit-scrollbar-thumb {
        background: #aaa; 
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-track, 
    .sticky-scrollbar-container::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }

    /* New Filter Form Styles */
    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
        border-left: 4px solid #007bff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
        width: 100%;
    }

    .filter-column {
        flex: 1 1 calc(33.333% - 20px); 
        min-width: 200px;
        max-width: calc(33.333% - 20px);
        display: flex;
        flex-direction: column;
    }

    .column-title {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 16px;
        text-align: left;
    }

    /* Search Criteria styles */
    .search-input {
        margin-bottom: 12px;
    }

    .search-input input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }

    .delinquent-option {
        display: flex;
        align-items: center;
        margin-top: 8px;
    }

    .delinquent-label {
        margin-left: 8px;
        background-color: #ffc107;
        color: #000;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
    }

    /* Document Types styles */
    .document-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .document-option {
        display: flex;
        align-items: center;
    }

    .document-checkbox {
        margin-right: 10px;
    }

    .document-label {
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .document-icon {
        margin-right: 5px;
        font-size: 16px;
    }

    /* Actions styles */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        width: 100%;
        text-align: center;
    }

    .btn-apply {
        background-color: #007bff;
        color: white;
    }

    .btn-reset {
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #ced4da;
        text-decoration: none;
        display: block;
    }

    .btn-print {
        background-color: #28a745;
        color: white;
    }


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-container {
            flex-direction: column;
        }
        
        .filter-column {
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
        }
    }

    /* Custom document icons */
    .soa-icon {
        color: #6c757d;
    }

    .demand-icon {
        color: #dc3545;
    }

    .nncv-icon {
        color: #6610f2;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statement of Account</h4>
                </div>
                <div class="card-body">
                    <!-- Search Filter -->
                    <div class="filter-card">
                        <form action="{{ route('accounts.soa.index') }}" method="GET" class="filter-form">
                            <div class="filter-container">
                                <!-- Search Criteria Column -->
                                <div class="filter-column">
                                    <div class="column-title">Search Criteria</div>
                                    <div class="search-input">
                                        <input type="text" class="form-control" id="address_id" name="address_id" 
                                            value="{{ request('address_id') }}" placeholder="Address ID">
                                    </div>
                                    <div class="delinquent-option">
                                        <input class="form-check-input" type="checkbox" id="delinquent" name="delinquent" 
                                            {{ request()->has('delinquent') ? 'checked' : '' }}>
                                        <label class="delinquent-label" for="delinquent">
                                            Delinquent Members Only
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Document Types Column -->
                                <div class="filter-column">
                                    <div class="column-title">Document Types</div>
                                    <div class="document-options">
                                        <div class="document-option">
                                            <input class="form-check-input document-type-checkbox" type="checkbox" 
                                                name="document_type[]" id="type_soa" value="soa" checked>
                                            <label class="document-label" for="type_soa">
                                                <span class="document-icon soa-icon">📄</span> SOA
                                            </label>
                                        </div>
                                        <div class="document-option">
                                            <input class="form-check-input document-type-checkbox" type="checkbox" 
                                                name="document_type[]" id="type_demand" value="demand">
                                            <label class="document-label" for="type_demand">
                                                <span class="document-icon demand-icon">📝</span> Demand Letter
                                            </label>
                                        </div>
                                        <div class="document-option">
                                            <input class="form-check-input document-type-checkbox" type="checkbox" 
                                                name="document_type[]" id="type_nncv1" value="nncv1">
                                            <label class="document-label" for="type_nncv1">
                                                <span class="document-icon nncv-icon">📋</span> NNCV
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions Column -->
                                <div class="filter-column">
                                    <div class="column-title">Actions</div>
                                    <div class="action-buttons">
                                        <button type="submit" class="btn btn-apply">Apply Filter</button>
                                        <a href="{{ route('accounts.soa.index') }}" class="btn btn-reset">Reset</a>
                                        <button type="button" class="btn btn-print" onclick="printStatements()">
                                            Print Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($arrears->isEmpty())
                        <div class="alert alert-info">
                            No records found. Please adjust your search criteria.
                        </div>
                    @else
                        <div class="table-container">
                            <div class="table-responsive" id="mainTableContainer">
                                <table class="table table-bordered table-striped" id="mainTable">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all"></th>
                                            <th>Member ID</th>
                                            <th>Transaction No</th>
                                            <th>Address ID</th>
                                            <th>Name</th>
                                            <th>SPA/Tenant</th>
                                            <th>Type</th>
                                            <th>Monthly Dues</th>
                                            <th>Arrear Month</th>
                                            <th>Current Month</th>
                                            <th>HOA Status</th>
                                            <th>Arrear Count</th>
                                            <th>Arrears</th>
                                            <th>Arrear Interest</th>
                                            <th>Total Due</th>
                                            <th>Last Payment</th>
                                            <th class="actions-cell">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($arrears as $arrear)
                                        <tr>
                                            <td><input type="checkbox" class="member-checkbox" value="{{ $arrear->mem_id }}"></td>
                                            <td>{{ $arrear->mem_id }}</td>
                                            <td>{{ $arrear->mem_transno }}</td>
                                            <td>{{ $arrear->mem_add_id }}</td>
                                            <td>{{ $arrear->mem_name }}</td>
                                            <td>{{ $arrear->mem_SPA_Tenant ?? 'N/A' }}</td>
                                            <td>{{ $arrear->mem_type }}</td>
                                            <td>₱{{ number_format($arrear->mem_monthlydues, 2) }}</td>
                                            <td class="date-cell">{{ date('M d, Y', strtotime($arrear->arrear_month)) }}</td>
                                            <td class="date-cell">{{ date('M d, Y', strtotime($arrear->current_month)) }}</td>
                                            <td>{{ $arrear->hoa_status }}</td>
                                            <td>{{ $arrear->arrear_count }}</td>
                                            <td>₱{{ number_format($arrear->arrear, 2) }}</td>
                                            <td>₱{{ number_format($arrear->arrear_interest, 2) }}</td>
                                            <td>₱{{ number_format($arrear->arrear_total, 2) }}</td>
                                            <td class="last-payment-cell">
                                                @if($arrear->last_paydate)
                                                    {{ date('M d, Y', strtotime($arrear->last_paydate)) }}<br>
                                                    OR#: {{ $arrear->last_or }}<br>
                                                    Amount: ₱{{ number_format($arrear->last_payamount, 2) }}
                                                @else
                                                    No recent payment
                                                @endif
                                            </td>
                                            <td class="actions-cell text-center">
                                               <a href="#" 
                                                onclick="event.preventDefault(); 
                                                        const selectedDocTypes = [];
                                                        document.querySelectorAll('.document-type-checkbox:checked').forEach(checkbox => {
                                                            selectedDocTypes.push(checkbox.value);
                                                        });
                                                        if (selectedDocTypes.length === 0) {
                                                            alert('Please select at least one document type to print.');
                                                            return;
                                                        }
                                                        window.open('{{ route('accounts.soa.print', ['id' => $arrear->mem_id]) }}' + '?document_types=' + selectedDocTypes.join(','), '_blank');" 
                                                class="btn btn-sm btn-primary">
                                                    Print
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                                <div class="sticky-scrollbar-container" id="stickyScrollbar">
                                <div class="scrollbar-content" id="scrollbarContent"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        
        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }
        
        // Individual checkbox change affects select all
        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = [...memberCheckboxes].every(cb => cb.checked);
                if(selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });

        // Clear address ID when delinquent checkbox is checked
        const delinquentCheckbox = document.getElementById('delinquent');
        const addressIdInput = document.getElementById('address_id');

        if(delinquentCheckbox && addressIdInput) {
            delinquentCheckbox.addEventListener('change', function() {
                if(this.checked) {
                    addressIdInput.value = '';
                }
            });
        }
    });
    
    // Function to print selected statements
    function printStatements() {
        const selectedMembers = [];
        document.querySelectorAll('.member-checkbox:checked').forEach(checkbox => {
            selectedMembers.push(checkbox.value);
        });
        
        if (selectedMembers.length === 0) {
            alert('Please select at least one member to print their statement.');
            return;
        }
        
        // Get all selected document types
        const selectedDocTypes = [];
        document.querySelectorAll('.document-type-checkbox:checked').forEach(checkbox => {
            selectedDocTypes.push(checkbox.value);
        });
        
        if (selectedDocTypes.length === 0) {
            alert('Please select at least one document type to print.');
            return;
        }
        
        const url = "{{ route('accounts.soa.print-multiple') }}?member_ids=" + selectedMembers.join(',') + "&document_types=" + selectedDocTypes.join(',');
        window.open(url, '_blank');
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const mainTable = document.getElementById('mainTable');
    const mainTableContainer = document.getElementById('mainTableContainer');
    const stickyScrollbar = document.getElementById('stickyScrollbar');
    const scrollbarContent = document.getElementById('scrollbarContent');
    
    // Set the width of scrollbar content to match table width
    function updateScrollbarWidth() {
        if (mainTable && scrollbarContent) {
            scrollbarContent.style.width = mainTable.offsetWidth + 'px';
        }
    }
    
    // Update width on load and resize
    updateScrollbarWidth();
    window.addEventListener('resize', updateScrollbarWidth);
    
    // Sync scroll positions
    if (mainTableContainer && stickyScrollbar) {
        mainTableContainer.addEventListener('scroll', function() {
            stickyScrollbar.scrollLeft = mainTableContainer.scrollLeft;
        });
        
        stickyScrollbar.addEventListener('scroll', function() {
            mainTableContainer.scrollLeft = stickyScrollbar.scrollLeft;
        });
    }
});
</script>
@php
// Update this version when you change your JS files
$jsVersion = '1.2.0';
@endphp
<script src="{{ asset('assets/js/address-id-validation.js') }}?v={{ $jsVersion }}"></script>
@endpush