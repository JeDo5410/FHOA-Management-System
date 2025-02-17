@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">Access Denied</div>
                <div class="card-body">
                    <h4>Unauthorized Action</h4>
                    <p>You do not have permission to access this resource.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
