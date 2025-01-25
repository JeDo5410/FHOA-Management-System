@extends('layouts.app')

@section('title', 'Dashboard - HOA Management System')

@section('content')
    <div class="container-fluid">
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
    </div>
@endsection