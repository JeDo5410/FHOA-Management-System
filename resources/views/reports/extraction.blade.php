@extends('layouts.app')

@section('title', 'Data Extraction Reports')

@section('content')
@php
$isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/reports.css') }}">
<div class="container-fluid px-4">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="#" method="POST">
                @csrf
                <!-- Container for tabs and buttons -->
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <ul class="nav nav-tabs border-bottom-0" id="reportsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="members-tab" data-bs-toggle="tab" 
                                        data-bs-target="#members" type="button" role="tab" 
                                        aria-controls="members" aria-selected="true">
                                    Members Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="payable-tab" data-bs-toggle="tab" 
                                        data-bs-target="#payable" type="button" role="tab" 
                                        aria-controls="payable" aria-selected="false">
                                    Account Payable
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="receivable-tab" data-bs-toggle="tab" 
                                        data-bs-target="#receivable" type="button" role="tab" 
                                        aria-controls="receivable" aria-selected="false">
                                    Account Receivable
                                </button>
                            </li>
                        </ul>
                    </div>
                    <!-- Add a horizontal separator line -->
                    <hr class="mt-0 mb-4">
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="reportsTabsContent">
                    <!-- Members Information Tab Content -->
                    <div class="tab-pane fade show active" id="members" role="tabpanel" aria-labelledby="members-tab">
                        <h5 class="mb-4">Members Information Extraction</h5>
                        
                        <!-- Filters and Actions Container -->
                        <div class="filter-card">
                            <div class="filter-container">
                                <!-- Data Selection Column -->
                                <div class="filter-column">
                                    <div class="column-title">Data Selection</div>
                                    <div class="btn-group w-100 mb-3" role="group" aria-label="Data selection">
                                        <button type="button" class="btn btn-primary active" id="membersDataBtn" onclick="switchDataView('members')">
                                            Member's Data
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="carStickerBtn" onclick="switchDataView('cars')">
                                            Car Sticker
                                        </button>
                                    </div>
                                    
                                    <!-- Member Status Filter - Only visible for Member's Data -->
                                    <div id="memberStatusFilter">
                                        <div class="column-title">Member Status</div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="memberStatus" id="statusAll" value="all" checked onclick="loadMemberData('all')">
                                            <label class="form-check-label" for="statusAll">
                                                All Members
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="memberStatus" id="statusActive" value="active" onclick="loadMemberData('active')">
                                            <label class="form-check-label" for="statusActive">
                                                Active Members
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="memberStatus" id="statusDelinquent" value="delinquent" onclick="loadMemberData('delinquent')">
                                            <label class="form-check-label" for="statusDelinquent">
                                                Delinquent Members
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions Column -->
                                <div class="filter-column">
                                    <div class="column-title">Actions</div>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-success" id="downloadBtn">
                                            <i class="bi bi-download me-1"></i> Download CSV
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Table Container -->
                        <div class="table-container">
                            <!-- Member Data Table -->
                            <div id="memberDataTableContainer" class="table-responsive">
                                <table class="table table-bordered table-striped" id="memberDataTable">
                                    <thead>
                                        <tr>
                                            <th>Member ID</th>
                                            <th>Trans No.</th>
                                            <th>Address ID</th>
                                            <th>Name</th>
                                            <th>SPA/Tenant</th>
                                            <th>Type</th>
                                            <th>Monthly Dues</th>
                                            <th>Arrear Month</th>
                                            <th>Arrear</th>
                                            <th>Arrear Count</th>
                                            <th>Arrear Interest</th>
                                            <th>Last OR</th>
                                            <th>Last Pay Date</th>
                                            <th>Last Pay Amount</th>
                                            <th>Mobile</th>
                                            <th>Date</th>
                                            <th>Email</th>
                                            <th>Resident 1</th>
                                            <th>Resident 2</th>
                                            <th>Resident 3</th>
                                            <th>Resident 4</th>
                                            <th>Resident 5</th>
                                            <th>Resident 6</th>
                                            <th>Resident 7</th>
                                            <th>Resident 8</th>
                                            <th>Resident 9</th>
                                            <th>Resident 10</th>
                                            <th>Relationship 1</th>
                                            <th>Relationship 2</th>
                                            <th>Relationship 3</th>
                                            <th>Relationship 4</th>
                                            <th>Relationship 5</th>
                                            <th>Relationship 6</th>
                                            <th>Relationship 7</th>
                                            <th>Relationship 8</th>
                                            <th>Relationship 9</th>
                                            <th>Relationship 10</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Car Sticker Table (Hidden by default) -->
                            <div id="carStickerTableContainer" class="table-responsive" style="display: none;">
                                <table class="table table-bordered table-striped" id="carStickerTable">
                                    <thead>
                                        <tr>
                                            <th>Member ID</th>
                                            <th>Address ID</th>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>SPA/Tenant</th>
                                            <th>Vehicle Maker</th>
                                            <th>Vehicle Type</th>
                                            <th>Vehicle Color</th>
                                            <th>Vehicle OR</th>
                                            <th>Vehicle CR</th>
                                            <th>Vehicle Plate</th>
                                            <th>Car Sticker</th>
                                            <th>Vehicle Active</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Sticky Scrollbar -->
                            <div class="sticky-scrollbar-container" id="stickyScrollbar">
                                <div class="scrollbar-content" id="scrollbarContent"></div>
                            </div>
                        </div>
                    </div>


                    <!-- Account Payable Tab -->
                    <div class="tab-pane fade" id="payable" role="tabpanel" 
                         aria-labelledby="payable-tab">
                        <h5 class="mb-4">Account Payable Extraction</h5>
                        <!-- Content will be added for payable tab -->
                    </div>

                    <!-- Account Receivable Tab -->
                    <div class="tab-pane fade" id="receivable" role="tabpanel" 
                         aria-labelledby="receivable-tab">
                        <h5 class="mb-4">Account Receivable Extraction</h5>
                        <!-- Content will be added for receivable tab -->
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
<script src="{{ asset('assets/js/report-members.js') }}"></script>
<script>
    // Toast Notification Handler
    function showToast(type, message) {
        const toastElement = document.getElementById(type + 'Toast');
        const messageElement = document.getElementById(type + 'Message');
        
        if (toastElement && messageElement) {
            messageElement.textContent = message;
            
            const bsToast = new bootstrap.Toast(toastElement, {
                animation: true,
                autohide: true,
                delay: 6000
            });
            
            bsToast.show();
        }
    }

    // Show toasts for session messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast('success', '{{ session('success') }}');
        @endif
        
        @if(session('error'))
            showToast('error', '{{ session('error') }}');
        @endif
        
        @if(session('info'))
            showToast('info', '{{ session('info') }}');
        @endif
    });
</script>
@endsection