@extends('layouts.app')

@section('title', 'Edit Arrear')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Arrear for Member ID: {{ $member->mem_id }}</h4>
                    <a href="{{ route('arrears.index') }}{{ $search ? '?search=' . urlencode($search) : '' }}{{ $returnAnchor ? '#' . $returnAnchor : '' }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Member Information</h6>
                                <p class="mb-1"><strong>Member ID:</strong> {{ $member->mem_id }}</p>
                                <p class="mb-0"><strong>Address:</strong> 
                                    @if($member->mem_add_id && strlen($member->mem_add_id) == 5)
                                        Phase {{ substr($member->mem_add_id, 0, 1) }}, 
                                        Block {{ substr($member->mem_add_id, 1, 2) }}, 
                                        Lot {{ substr($member->mem_add_id, 3, 2) }}
                                    @else
                                        {{ $member->mem_add_id ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('arrears.update', $member->mem_id) }}" method="POST" id="arrearForm">
                        @csrf
                        @method('PUT')
                        @if($search)
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        @if($returnAnchor)
                            <input type="hidden" name="return_anchor" value="{{ $returnAnchor }}">
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="arrear" class="form-label">Arrear Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" 
                                               class="form-control @error('arrear') is-invalid @enderror" 
                                               id="arrear" 
                                               name="arrear" 
                                               value="{{ old('arrear', $member->arrear ?? 0) }}" 
                                               step="0.01" 
                                               required>
                                    </div>
                                    @error('arrear')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="arrear_interest" class="form-label">Arrear Interest</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" 
                                               class="form-control @error('arrear_interest') is-invalid @enderror" 
                                               id="arrear_interest" 
                                               name="arrear_interest" 
                                               value="{{ old('arrear_interest', $member->arrear_interest ?? 0) }}" 
                                               step="0.01" 
                                               required>
                                    </div>
                                    @error('arrear_interest')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-light border">
                                    <h6><i class="bi bi-calculator"></i> Calculated Total</h6>
                                    <p class="mb-0">
                                        <strong>Current Arrear Total:</strong> 
                                        <span class="text-primary fs-5" id="calculatedTotal">
                                            ₱{{ number_format(($member->arrear ?? 0) + ($member->arrear_interest ?? 0), 2) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('arrears.index') }}{{ $search ? '?search=' . urlencode($search) : '' }}{{ $returnAnchor ? '#' . $returnAnchor : '' }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Update Arrear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const arrearInput = document.getElementById('arrear');
    const arrearInterestInput = document.getElementById('arrear_interest');
    const calculatedTotal = document.getElementById('calculatedTotal');

    function updateTotal() {
        const arrear = parseFloat(arrearInput.value) || 0;
        const arrearInterest = parseFloat(arrearInterestInput.value) || 0;
        const total = arrear + arrearInterest;
        
        calculatedTotal.textContent = '₱' + total.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    arrearInput.addEventListener('input', updateTotal);
    arrearInterestInput.addEventListener('input', updateTotal);
});
</script>
@endpush