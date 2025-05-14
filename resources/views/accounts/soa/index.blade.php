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
                            <div class="filter-input-group">
                                <label for="address_id" class="filter-label">Address ID:</label>
                                <input type="text" class="form-control filter-input" id="address_id" name="address_id" value="{{ request('address_id') }}">
                            </div>
                            
                            <div class="filter-checkbox-container">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="delinquent" name="delinquent" {{ request()->has('delinquent') ? 'checked' : '' }}>
                                    <label class="form-check-label filter-label" for="delinquent">
                                        Delinquent Members Only
                                    </label>
                                </div>
                            </div>
                            
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('accounts.soa.index') }}" class="btn btn-outline-secondary">Reset</a>
                                <button type="button" class="btn btn-success" onclick="printStatements()">Print Selected</button>
                            </div>
                        </form>
                    </div>

                    @if($arrears->isEmpty())
                        <div class="alert alert-info">
                            No records found. Please adjust your search criteria.
                        </div>
                    @else
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
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
                                                <a href="{{ route('accounts.soa.print', ['id' => $arrear->mem_id]) }}" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    Print
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
        
        const url = "{{ route('accounts.soa.print-multiple') }}?member_ids=" + selectedMembers.join(',');
        window.open(url, '_blank');
    }
</script>
@php
// Update this version when you change your JS files
$jsVersion = '1.2.0';
@endphp
<script src="{{ asset('assets/js/address-id-validation.js') }}?v={{ $jsVersion }}"></script>
@endpush