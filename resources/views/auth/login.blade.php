@extends('layouts.auth')

@section('title', 'Login - HOA Management System')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Welcome Back</h2>
                    
                    <form id="loginForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                            <div id="usernameError" class="invalid-feedback"></div>
                        </div>
                    
                        <div class="mb-3" id="passwordGroup">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password">
                            <div id="passwordError" class="invalid-feedback"></div>
                        </div>
                    
                        <div class="mb-3 d-none" id="newPasswordGroup">
                            <label for="new_password" class="form-label">Set New Password</label>
                            <input type="password" class="form-control" name="new_password" id="new_password">
                            <div id="newPasswordError" class="invalid-feedback"></div>
                        </div>
                    
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('username').addEventListener('blur', checkUsername);
    document.getElementById('loginForm').addEventListener('submit', handleSubmit);

    let userState = {
        exists: false,
        hasPassword: false
    };

    async function checkUsername() {
        const username = document.getElementById('username').value.trim(); // Just trim whitespace, preserve case
        if (!username) return;

        try {
            const response = await fetch('/check-username', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ username })
            });

            const data = await response.json();
            console.log('User state after check:', data);  
            userState = {
                exists: data.exists,
                hasPassword: data.hasPassword
            };

            updateFormState();
            
            const usernameInput = document.getElementById('username');
            const usernameError = document.getElementById('usernameError');
            
            if (!data.exists) {
                usernameInput.classList.add('is-invalid');
                usernameError.textContent = data.message;
            } else {
                usernameInput.classList.remove('is-invalid');
                usernameError.textContent = '';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function updateFormState() {
        const passwordGroup = document.getElementById('passwordGroup');
        const newPasswordGroup = document.getElementById('newPasswordGroup');
        const submitBtn = document.getElementById('submitBtn');

        if (!userState.exists) {
            passwordGroup.classList.add('d-none');
            newPasswordGroup.classList.add('d-none');
            submitBtn.disabled = true;
            return;
        }

        submitBtn.disabled = false;

        if (userState.hasPassword) {
            passwordGroup.classList.remove('d-none');
            newPasswordGroup.classList.add('d-none');
        } else {
            passwordGroup.classList.add('d-none');
            newPasswordGroup.classList.remove('d-none');
        }
    }

    async function handleSubmit(e) {
    e.preventDefault();
    console.log('Current user state:', userState);  
    const username = document.getElementById('username').value;

    if (!userState.hasPassword) {
        const newPassword = document.getElementById('new_password').value;
        
        try {
            // Show confirmation dialog before setting password
            const result = await Swal.fire({
                title: 'Confirm New Password',
                text: 'Would you like to save this password?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it',
                cancelButtonText: 'No, cancel'
            });

            if (result.isConfirmed) {
                const response = await fetch('/set-initial-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ 
                        username: username, 
                        new_password: newPassword 
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: 'Success',
                        text: data.message,
                        icon: 'success',
                        timer: 7500,  
                        timerProgressBar: true  
                    });
                    document.getElementById('new_password').value = '';
                    document.getElementById('password').value = '';
                    userState.hasPassword = true;
                    updateFormState();
                } else {
                    const newPasswordInput = document.getElementById('new_password');
                    const newPasswordError = document.getElementById('newPasswordError');
                    newPasswordInput.classList.add('is-invalid');
                    newPasswordError.textContent = data.message || 'Failed to set password. Please try again.';
                }
            }
        } catch (error) {
            console.error('Error setting password:', error);
            await Swal.fire({
                title: 'Error',
                text: 'An error occurred while setting the password. Please try again.',
                icon: 'error'
            });
        }
    } else {
        const password = document.getElementById('password').value;
        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                const passwordInput = document.getElementById('password');
                const passwordError = document.getElementById('passwordError');
                passwordInput.classList.add('is-invalid');
                passwordError.textContent = data.message;
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                title: 'Error',
                text: 'An error occurred during login. Please try again.',
                icon: 'error'
            });
        }
    }
}
</script>
@endsection