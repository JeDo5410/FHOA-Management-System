@extends('layouts.app')

@section('title', 'Dashboard - HOA Management System')

@section('content')
    <div class="welcome-card">
        <h2>Welcome, {{ auth()->user()->username }}!</h2>
        <p>Role: {{ auth()->user()->role }}</p>
        <p>Login Time: {{ now()->format('F j, Y g:i A') }}</p>
    </div>

    <div class="welcome-card">
        <h3>Quick Links</h3>
        <ul style="margin-left: 20px; margin-top: 10px;">
            <li>Manage Residents</li>
            <li>View Announcements</li>
            <li>Handle Maintenance Requests</li>
            <li>Manage Payments</li>
        </ul>
    </div>
@endsection