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
// ===========================
// Configuration & State
// ===========================
const state = {
    user: {
        exists: false,
        hasPassword: false
    },
    isRedirecting: false
};

// ===========================
// Initialization
// ===========================
document.addEventListener('DOMContentLoaded', function() {
    // Check for timeout parameter in URL
    handleTimeoutParameter();
    
    // Set up event listeners
    initializeEventListeners();
    
    // Check session status
    checkSessionStatus();
});

// ===========================
// URL Parameter Handling
// ===========================
function handleTimeoutParameter() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('reason') === 'timeout') {
        // Show timeout message
        document.getElementById('timeoutMessage').style.display = 'block';
        
        // Clean up URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}

// ===========================
// Event Listeners Setup
// ===========================
function initializeEventListeners() {
    const usernameField = document.getElementById('username');
    
    // Username field blur event
    usernameField.addEventListener('blur', checkUsername);
    
    // Username field input event with debounce
    usernameField.addEventListener('input', debounce(checkUsername, 1500));
    
    // Username field enter key event
    usernameField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            checkUsername();
        }
    });
    
    // Form submit event
    document.getElementById('loginForm').addEventListener('submit', handleSubmit);
}

// ===========================
// Session Management
// ===========================
async function checkSessionStatus() {
    if (state.isRedirecting) return;
    
    try {
        const response = await fetch('/check-session', {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        if (response.status === 419 || response.status === 401) {
            const refreshed = await refreshCsrfToken();
            if (!refreshed) {
                throw new Error('Session expired');
            }
        }
    } catch (error) {
        console.error('Session check failed:', error);
        showSessionExpiredAlert();
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

// ===========================
// API Request Handling
// ===========================
async function makeAuthenticatedRequest(url, options) {
    try {
        const response = await fetch(url, options);
        
        if (response.status === 419 || response.status === 401) {
            const refreshed = await refreshCsrfToken();
            if (refreshed) {
                options.headers['X-CSRF-TOKEN'] = getCsrfToken();
                return await fetch(url, options);
            } else {
                showSessionExpiredAlert();
                return null;
            }
        }
        
        return response;
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

// ===========================
// Username Validation
// ===========================
async function checkUsername() {
    const username = document.getElementById('username').value.trim();
    
    // Clear error state if username is empty
    if (!username) {
        const usernameInput = document.getElementById('username');
        const usernameError = document.getElementById('usernameError');
        usernameInput.classList.remove('is-invalid');
        usernameError.textContent = '';
        
        // Reset form state
        state.user = {
            exists: false,
            hasPassword: false
        };
        updateFormState();
        return;
    }

    try {
        const response = await makeAuthenticatedRequest('/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ username })
        });

        if (!response) return;
        
        const data = await response.json();
        console.log('User state after check:', data);
        
        // Update state
        state.user = {
            exists: data.exists,
            hasPassword: data.hasPassword
        };

        // Update UI
        updateUsernameFieldUI(data);
        updateFormState();
        
    } catch (error) {
        console.error('Error:', error);
        showSessionErrorAlert();
    }
}

function updateUsernameFieldUI(data) {
    const usernameInput = document.getElementById('username');
    const usernameError = document.getElementById('usernameError');
    
    if (!data.exists) {
        usernameInput.classList.add('is-invalid');
        usernameError.textContent = data.message || 'User not found';
    } else {
        usernameInput.classList.remove('is-invalid');
        usernameError.textContent = '';
    }
}

// ===========================
// Form State Management
// ===========================
function updateFormState() {
    const passwordGroup = document.getElementById('passwordGroup');
    const newPasswordGroup = document.getElementById('newPasswordGroup');
    const submitBtn = document.getElementById('submitBtn');

    if (!state.user.exists) {
        // Hide all password fields and disable submit
        passwordGroup.classList.add('d-none');
        newPasswordGroup.classList.add('d-none');
        submitBtn.disabled = true;
        return;
    }

    // Enable submit button
    submitBtn.disabled = false;

    if (state.user.hasPassword) {
        // Show regular password field
        passwordGroup.classList.remove('d-none');
        newPasswordGroup.classList.add('d-none');
    } else {
        // Show new password field
        passwordGroup.classList.add('d-none');
        newPasswordGroup.classList.remove('d-none');
    }
}

// ===========================
// Form Submission
// ===========================
async function handleSubmit(e) {
    e.preventDefault();
    
    // Validate username
    const username = document.getElementById('username').value.trim();
    if (!username) {
        showErrorAlert('Please enter a username');
        return;
    }

    // Recheck username before submission
    await checkUsername();
    
    // Small delay to ensure state is updated
    await new Promise(resolve => setTimeout(resolve, 100));
    
    if (!state.user.exists) {
        showErrorAlert('Please enter a valid username');
        return;
    }

    // Handle based on user state
    if (!state.user.hasPassword) {
        await handleNewPasswordSubmission(username);
    } else {
        await handleLoginSubmission(username);
    }
}

// ===========================
// New Password Handling
// ===========================
async function handleNewPasswordSubmission(username) {
    const newPassword = document.getElementById('new_password').value.trim();
    
    // Validate new password
    if (!newPassword) {
        showErrorAlert('Please enter a new password');
        return;
    }

    if (newPassword.length < 5) {
        showErrorAlert('Password must be at least 5 characters long');
        return;
    }
    
    // Confirm password
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

    if (!result.isConfirmed) return;

    // Submit new password
    await submitNewPassword(username, newPassword);
}

async function submitNewPassword(username, newPassword) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    
    try {
        const response = await makeAuthenticatedRequest('/set-initial-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ username, new_password: newPassword })
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
            
            // Clear password fields
            document.getElementById('new_password').value = '';
            document.getElementById('password').value = '';
            
            // Update state
            state.user.hasPassword = true;
            updateFormState();
        } else {
            showFieldError('new_password', data.message || 'Failed to set password. Please try again.');
        }
    } catch (error) {
        console.error('Error setting password:', error);
        showSessionExpiredAlert();
    } finally {
        submitBtn.disabled = false;
    }
}

// ===========================
// Login Handling
// ===========================
async function handleLoginSubmission(username) {
    const password = document.getElementById('password').value.trim();
    
    if (!password) {
        showErrorAlert('Please enter your password');
        return;
    }

    await submitLogin(username, password);
}

async function submitLogin(username, password) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;

    try {
        const response = await makeAuthenticatedRequest('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ username, password })
        });

        if (!response) {
            submitBtn.disabled = false;
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            state.isRedirecting = true;
            window.location.href = data.redirect;
        } else {
            showFieldError('password', data.message);
            submitBtn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showSessionExpiredAlert();
        submitBtn.disabled = false;
    }
}

// ===========================
// Helper Functions
// ===========================
function getCsrfToken() {
    return document.querySelector('input[name="_token"]').value;
}

function showFieldError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const error = document.getElementById(fieldId + 'Error');
    input.classList.add('is-invalid');
    error.textContent = message;
}

// Debounce function to limit how often a function can run
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===========================
// Alert Functions
// ===========================
async function showErrorAlert(message) {
    await Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error'
    });
}

async function showSessionExpiredAlert() {
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

async function showSessionErrorAlert() {
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
</script>
@endsection