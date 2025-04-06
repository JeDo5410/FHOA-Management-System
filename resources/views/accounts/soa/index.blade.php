@extends('layouts.app')

@section('content')

<style>
    /* General text styles */
    td, th {
        font-size: 12.8px;
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
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form action="{{ route('accounts.soa.index') }}" method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="address_id" class="mr-2">Address ID:</label>
                                    <input type="text" class="form-control" id="address_id" name="address_id" value="{{ request('address_id') }}">
                                </div>
                                <div class="form-group mr-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="delinquent" name="delinquent" {{ request()->has('delinquent') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="delinquent">
                                            Delinquent Only (≥ 3 months)
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('accounts.soa.index') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                                <button type="button" class="btn btn-success ml-2" onclick="printStatements()">Print Selected</button>
                            </form>
                        </div>
                    </div>

                    @if($arrears->isEmpty())
                        <div class="alert alert-info">
                            No records found. Please adjust your search criteria.
                        </div>
                    @else
                        <!-- Removed the static alert that was here previously -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Member ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Monthly Dues</th>
                                        <th>Arrears</th>
                                        <th>Arrears Interest</th>
                                        <th>Total Due</th>
                                        <th>Last Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($arrears as $arrear)
                                    <tr>
                                        <td><input type="checkbox" class="member-checkbox" value="{{ $arrear->mem_id }}"></td>
                                        <td>{{ $arrear->mem_id }}</td>
                                        <td>{{ $arrear->mem_name }}</td>
                                        <td>{{ $arrear->mem_type }}</td>
                                        <td>₱{{ number_format($arrear->mem_monthlydues, 2) }}</td>
                                        <td>₱{{ number_format($arrear->current_arrear, 2) }}</td>
                                        <td>₱{{ number_format($arrear->arrear_interest, 2) }}</td>
                                        <td>₱{{ number_format($arrear->arrear_total, 2) }}</td>
                                        <td>
                                            @if($arrear->last_paydate)
                                                {{ date('M d, Y', strtotime($arrear->last_paydate)) }}<br>
                                                OR#: {{ $arrear->last_or }}<br>
                                                Amount: ₱{{ number_format($arrear->last_payamount, 2) }}
                                            @else
                                                No recent payment
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-details" data-id="{{ $arrear->mem_id }}" 
                                                    data-toggle="modal" data-target="#statementModal">
                                                View Details
                                            </button>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statement of Account Modal -->
<div class="modal fade" id="statementModal" tabindex="-1" role="dialog" aria-labelledby="statementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statementModalLabel">Statement of Account</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="statement-details">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary print-statement">Print</button>
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
        
        // View details button click handler
        const viewDetailsButtons = document.querySelectorAll('.view-details');
        viewDetailsButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-id');
                const statementDetails = document.getElementById('statement-details');
                statementDetails.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
                
                // AJAX request to get statement details
                fetch(`{{ route('accounts.soa.details') }}?member_id=${memberId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(data => {
                        statementDetails.innerHTML = data;
                    })
                    .catch(error => {
                        statementDetails.innerHTML = '<div class="alert alert-danger">Error loading statement details.</div>';
                    });
            });
        });
        
        // Print statement from modal
        const printStatementButton = document.querySelector('.print-statement');
        if(printStatementButton) {
            printStatementButton.addEventListener('click', function() {
                const printContents = document.getElementById('statement-details').innerHTML;
                const originalContents = document.body.innerHTML;
                
                document.body.innerHTML = `
                    <div style="padding: 20px;">
                        <div style="text-align: right; margin-bottom: 20px;">
                            <button onclick="window.print();" class="no-print">Print</button>
                            <button onclick="window.location.reload();" class="no-print">Back</button>
                        </div>
                        ${printContents}
                    </div>
                `;
                
                window.onafterprint = function() {
                    document.body.innerHTML = originalContents;
                    window.location.reload();
                };
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
        
        const url = "{{ route('accounts.soa.print-multiple') }}?member_ids=" + selectedMembers.join(',');
        window.open(url, '_blank');
    }
</script>
@endpush