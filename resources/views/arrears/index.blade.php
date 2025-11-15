@extends('layouts.app')

@section('title', 'Arrear Management')

@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Arrear Management</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('arrears.index') }}">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search by Member ID or Address ID...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    @if($search)
                                        <a href="{{ route('arrears.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        @if($search)
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    Showing results for: <strong>"{{ $search }}"</strong>
                                    ({{ $members->count() }} {{ $members->count() == 1 ? 'result' : 'results' }} found)
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($search && $members->count() == 1 && isset($singleMember))
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="member_name" class="form-label fw-bold">Member Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="member_name"
                                   value="{{ $singleMember->memberData->first()->mem_name ?? 'N/A' }}"
                                   readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="member_address" class="form-label fw-bold">Member Address</label>
                            <input type="text"
                                   class="form-control"
                                   id="member_address"
                                   value="@if($singleMember->mem_add_id && strlen($singleMember->mem_add_id) == 5)Phase {{ substr($singleMember->mem_add_id, 0, 1) }}, Block {{ substr($singleMember->mem_add_id, 1, 2) }}, Lot {{ substr($singleMember->mem_add_id, 3, 2) }}@else{{ $singleMember->mem_add_id ?? 'N/A' }}@endif"
                                   readonly>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Member ID</th>
                                    <th>Address</th>
                                    <th>Arrear</th>
                                    <th>Arrear Interest</th>
                                    <th>Arrear Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                <tr id="member-{{ $member->mem_id }}">
                                    <td>{{ $member->mem_id }}</td>
                                    <td>
                                        @if($member->mem_add_id && strlen($member->mem_add_id) == 5)
                                            Phase {{ substr($member->mem_add_id, 0, 1) }}, 
                                            Block {{ substr($member->mem_add_id, 1, 2) }}, 
                                            Lot {{ substr($member->mem_add_id, 3, 2) }}
                                        @else
                                            {{ $member->mem_add_id ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($member->arrear ?? 0, 2) }}</td>
                                    <td>₱{{ number_format($member->arrear_interest ?? 0, 2) }}</td>
                                    <td>₱{{ number_format($member->arrear_total ?? 0, 2) }}</td>
                                    <td>
                                        <a href="{{ route('arrears.edit', $member->mem_id) }}{{ $search ? '?search=' . urlencode($search) . '&' : '?' }}return_anchor=member-{{ $member->mem_id }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($members->isEmpty())
                        <div class="text-center py-4">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                @if($search)
                                    No members found matching your search criteria.
                                @else
                                    No members found in the system.
                                @endif
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
    console.log('Page loaded, checking for hash:', window.location.hash);
    
    // Check if there's an anchor in the URL (indicating we returned from edit)
    if (window.location.hash) {
        const targetId = window.location.hash.substring(1); // Remove the #
        const targetElement = document.getElementById(targetId);
        
        console.log('Looking for element with ID:', targetId);
        console.log('Found element:', targetElement);
        
        if (targetElement) {
            // Scroll to center the element in the viewport
            setTimeout(function() {
                const elementTop = targetElement.offsetTop;
                const elementHeight = targetElement.offsetHeight;
                const windowHeight = window.innerHeight;
                
                // Calculate position to place element in upper-middle of viewport
                const scrollTo = elementTop - (windowHeight / 3);
                
                console.log('Scrolling to position:', scrollTo);
                
                window.scrollTo({
                    top: scrollTo,
                    behavior: 'smooth'
                });
                
                // No blinking animation - just scroll positioning
                
            }, 200); // Small delay to ensure page is fully loaded
        } else {
            console.log('Target element not found!');
        }
    } else {
        console.log('No hash in URL');
    }
});
</script>
@endpush