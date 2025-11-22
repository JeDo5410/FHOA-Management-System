@extends('layouts.app')

@section('title', 'Dashboard - HOA Management System')

@section('content')
    <!-- Toast Notification for Inspection Forms -->
    <div class="toast-container position-fixed" style="top: 20px; right: 20px; z-index: 1060;">
        <!-- Warning Toast for Inspection Forms -->
        <div id="inspectionFormsToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" style="cursor: pointer;" id="inspectionFormsMessage">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="inspectionFormsText"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    {{-- <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title h4 mb-4">Welcome, {{ auth()->user()->username }}!</h2>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="mb-2 text-muted">User Role</h6>
                                    <p class="mb-0 fw-bold">{{ auth()->user()->role === 1 ? 'Administrator' : 'Staff' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="mb-2 text-muted">Login Time</h6>
                                    <p class="mb-0">{{ now()->format('F j, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        checkForPermitsNeedingInspectionForms();

    });

    function checkForPermitsNeedingInspectionForms() {
        fetch('/construction-permit/check-inspection-forms-needed', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                showInspectionFormsNotification(data.count);
                // Mark notification as shown for this session
                sessionStorage.setItem('inspection_notification_shown', 'true');
            }
        })
        .catch(error => {
            console.error('Error checking inspection forms:', error);
        });
    }

    function showInspectionFormsNotification(count) {
        const toastElement = document.getElementById('inspectionFormsToast');
        const textElement = document.getElementById('inspectionFormsText');
        const messageBody = document.getElementById('inspectionFormsMessage');

        if (toastElement && textElement && messageBody) {
            // Set the notification text
            const permitText = count === 1 ? 'permit is' : 'permits are';
            const formText = count === 1 ? 'an inspection form' : 'inspection forms';
            textElement.textContent = `${count} ${permitText} awaiting ${formText} creation. Click here to view.`;

            // Make the toast body clickable to redirect to construction permit page
            messageBody.addEventListener('click', function() {
                window.location.href = '{{ route('construction-permit.index') }}';
            });

            // Show the toast
            const bsToast = new bootstrap.Toast(toastElement, {
                animation: true,
                autohide: true,
                delay: 8000  // Show for 8 seconds (longer than normal for important alerts)
            });

            bsToast.show();
        }
    }
</script>
@endpush