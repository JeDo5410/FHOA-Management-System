@extends('layouts.auth')

@section('title', 'Login - HOA Management System')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Welcome Back</h2>
                    <!-- Session timeout message -->
                    @if(session('message'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <!-- JavaScript timeout parameter handling -->
                    <div id="timeoutMessage" class="alert alert-warning alert-dismissible fade show mb-4" style="display: none;" role="alert">
                    Your session has expired. Please login again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    
                    <form id="loginForm" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required autocomplete="off">
                            <div id="usernameError" class="invalid-feedback"></div>
                        </div>
                    
                        <div class="mb-3" id="passwordGroup">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password" autocomplete="new-password">
                            <div id="passwordError" class="invalid-feedback"></div>
                        </div>
                    
                        <div class="mb-3 d-none" id="newPasswordGroup">
                            <label for="new_password" class="form-label">Set New Password</label>
                            <input type="password" class="form-control" name="new_password" id="new_password" autocomplete="new-password">
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
// Check for timeout parameter in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('reason') === 'timeout') {
        document.getElementById('timeoutMessage').style.display = 'block';
    }
});
// JavaScript remains unchanged
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('username').addEventListener('blur', checkUsername);
    document.getElementById('loginForm').addEventListener('submit', handleSubmit);

    // Check session status on page load
    checkSessionStatus();
});

let userState = {
    exists: false,
    hasPassword: false
};

async function checkSessionStatus() {
    if (window.isRedirecting) return;
    try {
        const response = await fetch('/check-session', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });
        
        if (response.status === 419 || response.status === 401) {
            // Session is invalid, try to refresh token
            const refreshed = await refreshCsrfToken();
            if (!refreshed) {
                throw new Error('Session expired');
            }
        }
    } catch (error) {
        console.error('Session check failed:', error);
        await Swal.fire({
            title: 'Session Expired',
            text: 'Your session has expired. Please refresh the page.',
            icon: 'warning',
            confirmButtonText: 'Refresh Page'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
    }
}

async function refreshCsrfToken() {
    try {
        const response = await fetch('/refresh-csrf');
        if (!response.ok) throw new Error('Failed to refresh CSRF token');
        const data = await response.json();
        document.querySelector('input[name="_token"]').value = data.token;
        return true;
    } catch (error) {
        console.error('Failed to refresh CSRF token:', error);
        return false;
    }
}

async function makeAuthenticatedRequest(url, options) {
    try {
        const response = await fetch(url, options);
        
        if (response.status === 419 || response.status === 401) {
            const refreshed = await refreshCsrfToken();
            if (refreshed) {
                options.headers['X-CSRF-TOKEN'] = document.querySelector('input[name="_token"]').value;
                return await fetch(url, options);
            } else {
                await Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please refresh the page.',
                    icon: 'warning',
                    confirmButtonText: 'Refresh Page'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
                return null;
            }
        }
        
        return response;
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

async function checkUsername() {
    const username = document.getElementById('username').value.trim();
    if (!username) return;

    try {
        const response = await makeAuthenticatedRequest('/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ username })
        });

        if (!response) return;
        
        const data = await response.json();
        console.log('User state after check:', data);
        
        userState = {
            exists: data.exists,
            hasPassword: data.hasPassword
        };

        const usernameInput = document.getElementById('username');
        const usernameError = document.getElementById('usernameError');
        
        if (!data.exists) {
            usernameInput.classList.add('is-invalid');
            usernameError.textContent = data.message || 'User not found';
        } else {
            usernameInput.classList.remove('is-invalid');
            usernameError.textContent = '';
        }

        updateFormState();
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            title: 'Session Error',
            text: 'Please refresh the page and try again.',
            icon: 'warning',
            confirmButtonText: 'Refresh Page'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
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
    
    const username = document.getElementById('username').value.trim();
    if (!username) {
        await Swal.fire({
            title: 'Error',
            text: 'Please enter a username',
            icon: 'error'
        });
        return;
    }

    // Always check username before proceeding with form submission
    await checkUsername();
    console.log('Current user state after validation:', userState);
    
    // Add a small delay to ensure username check completed
    await new Promise(resolve => setTimeout(resolve, 100));
    
    if (!userState.exists) {
        await Swal.fire({
            title: 'Error',
            text: 'Please enter a valid username',
            icon: 'error'
        });
        return;
    }

    const submitBtn = document.getElementById('submitBtn');

    if (!userState.hasPassword) {
        const newPassword = document.getElementById('new_password').value.trim();
        
        if (!newPassword) {
            await Swal.fire({
                title: 'Error',
                text: 'Please enter a new password',
                icon: 'error'
            });
            return;
        }

        // Add password strength validation
        if (newPassword.length < 5) {
            await Swal.fire({
                title: 'Error',
                text: 'Password must be at least 5 characters long',
                icon: 'error'
            });
            return;
        }
        
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
                submitBtn.disabled = true;
                
                const response = await makeAuthenticatedRequest('/set-initial-password', {
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

                if (!response) {
                    submitBtn.disabled = false;
                    return;
                }
                
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
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error setting password:', error);
            submitBtn.disabled = false;
            await Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. Please refresh and try again.',
                icon: 'warning',
                confirmButtonText: 'Refresh Page'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        }
    } else {
        const password = document.getElementById('password').value.trim();
        
        if (!password) {
            await Swal.fire({
                title: 'Error',
                text: 'Please enter your password',
                icon: 'error'
            });
            return;
        }

        try {
            submitBtn.disabled = true;

            const response = await makeAuthenticatedRequest('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ username, password })
            });

            if (!response) {
                submitBtn.disabled = false;
                return;
            }
            
            const data = await response.json();
            if (data.success) {
                window.isRedirecting = true;
                window.location.href = data.redirect;
            } else {
                const passwordInput = document.getElementById('password');
                const passwordError = document.getElementById('passwordError');
                passwordInput.classList.add('is-invalid');
                passwordError.textContent = data.message;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            submitBtn.disabled = false;
            await Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. Please refresh and try again.',
                icon: 'warning',
                confirmButtonText: 'Refresh Page'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        }
    }
}
</script>
@endsection