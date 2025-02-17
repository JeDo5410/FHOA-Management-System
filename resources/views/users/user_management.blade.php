@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <button type="button" class="btn btn-primary" onclick="openAddModal()">
            Add New User
        </button>
    </div>

    <!-- Active Users Section -->
    <div class="card mb-4">
        <div class="card-header bg-success bg-opacity-10">
            <h5 class="card-title mb-0 text-success">Active Users</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Password Status</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @if($user->is_active)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                <span class="badge 
                                    @if($user->role === 1) bg-primary
                                    @elseif($user->role === 2) bg-info
                                    @else bg-secondary
                                    @endif">
                                    {{ $user->role === 1 ? 'Administrator' : ($user->role === 2 ? 'Editor' : 'Viewer') }}
                                </span>
                            </td>
                            <td>
                                @if($user->password)
                                    <span class="badge bg-secondary">Password Set</span>
                                @else
                                    <span class="badge bg-warning text-dark">No Password</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openEditModal({{ $user->id }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $inactiveUsers = $users->where('is_active', 0);
    @endphp

    @if($inactiveUsers->count() > 0)
    <!-- Inactive Users Section (same changes for role display) -->
    <div class="card">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="card-title mb-0 text-danger">Inactive Users</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Password Status</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @if(!$user->is_active)
                        <tr class="table-danger bg-opacity-10">
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                <span class="badge 
                                    @if($user->role === 1) bg-primary
                                    @elseif($user->role === 2) bg-info
                                    @else bg-secondary
                                    @endif">
                                    {{ $user->role === 1 ? 'Administrator' : ($user->role === 2 ? 'Editor' : 'Viewer') }}
                                </span>
                            </td>
                            <td>
                                @if($user->password)
                                    <span class="badge bg-secondary">Password Set</span>
                                @else
                                    <span class="badge bg-warning text-dark">No Password</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger">Inactive</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openEditModal({{ $user->id }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control" name="role" required>
                            <option value="3">Viewer</option>
                            <option value="2">Editor</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control" name="role" required>
                            <option value="3">Viewer</option>
                            <option value="2">Editor</option>
                            <option value="1">Administrator</option>
                        </select>
                    </div>                    
                    <div class="mb-3">
                        <label class="form-label">Password Management</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="clear_password" id="clearPassword">
                            <label class="form-check-label" for="clearPassword">
                                Clear user's password (User will need to set a new password on next login)
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Update</button>
            </div>
        </div>
    </div>
</div>

@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

<script>
// Initialize modals
let addModal, editModal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals with proper backdrop and keyboard options
    addModal = new bootstrap.Modal(document.getElementById('addUserModal'), {
        backdrop: 'static',
        keyboard: true
    });
    
    editModal = new bootstrap.Modal(document.getElementById('editUserModal'), {
        backdrop: 'static',
        keyboard: true
    });

    // Add event listeners for modal hiding
    document.getElementById('addUserModal').addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });

    document.getElementById('editUserModal').addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});

// Modified open functions
function openAddModal() {
    document.getElementById('addUserForm').reset();
    // Remove any lingering backdrops
    const existingBackdrop = document.querySelector('.modal-backdrop');
    if (existingBackdrop) {
        existingBackdrop.remove();
    }
    document.body.classList.remove('modal-open');
    
    // Show modal
    addModal.show();
}

function openEditModal(userId) {
    const user = users.find(u => u.id === userId);
    const form = document.getElementById('editUserForm');
    
    form.elements['user_id'].value = user.id;
    form.elements['username'].value = user.username;
    form.elements['role'].value = user.role;
    form.elements['is_active'].value = user.is_active ? '1' : '0';
    
    // Remove any lingering backdrops
    const existingBackdrop = document.querySelector('.modal-backdrop');
    if (existingBackdrop) {
        existingBackdrop.remove();
    }
    document.body.classList.remove('modal-open');
    
    // Show modal
    editModal.show();
}


function saveUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);

    fetch('{{ $isNgrok ? secure_url("users") : url("users") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message
        }).then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        const errorMessage = error.errors ? 
            Object.values(error.errors)[0][0] : 
            'An error occurred while saving the user';
            
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage
        });
    });
}

function updateUser() {
    const form = document.getElementById('editUserForm');
    const userId = form.elements['user_id'].value;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.clear_password = form.elements['clear_password'].checked;

    if (data.clear_password) {
        Swal.fire({
            title: 'Clear Password?',
            text: 'Are you sure you want to clear this user\'s password? They will need to set a new password on their next login.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, clear it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performUpdate(userId, data);
            }
        });
    } else {
        performUpdate(userId, data);
    }
}

function performUpdate(userId, data) {
    fetch(`{{ $isNgrok ? secure_url("users") : url("users") }}/${userId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message
        }).then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        const errorMessage = error.errors ? 
            Object.values(error.errors)[0][0] : 
            'An error occurred while updating the user';
            
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6'
        });
    });
}

</script>
@endsection

