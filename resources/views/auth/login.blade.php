<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .hidden {
            display: none;
        }
        .error-message {
            color: red;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div>
        <form id="loginForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
                <span id="usernameError" class="error-message"></span>
            </div>
        
            <div class="form-group" id="passwordGroup">
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
                <span id="passwordError" class="error-message"></span>
            </div>
        
            <div class="form-group hidden" id="newPasswordGroup">
                <label for="new_password">Set New Password</label>
                <input type="password" name="new_password" id="new_password">
                <span id="newPasswordError" class="error-message"></span>
            </div>
        
            <button type="submit" class="btn" id="submitBtn">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('username').addEventListener('blur', checkUsername);
        document.getElementById('loginForm').addEventListener('submit', handleSubmit);

        let userState = {
            exists: false,
            hasPassword: false
        };

        async function checkUsername() {
            const username = document.getElementById('username').value;
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
                userState = {
                    exists: data.exists,
                    hasPassword: data.hasPassword
                };

                updateFormState();

                if (!data.exists) {
                    document.getElementById('usernameError').textContent = data.message;
                } else {
                    document.getElementById('usernameError').textContent = '';
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
                passwordGroup.classList.add('hidden');
                newPasswordGroup.classList.add('hidden');
                submitBtn.disabled = true;
                return;
            }

            submitBtn.disabled = false;

            if (userState.hasPassword) {
                passwordGroup.classList.remove('hidden');
                newPasswordGroup.classList.add('hidden');
            } else {
                passwordGroup.classList.add('hidden');
                newPasswordGroup.classList.remove('hidden');
            }
        }

        async function handleSubmit(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;

            if (!userState.hasPassword) {
                // Handle initial password setup
                const newPassword = document.getElementById('new_password').value;
                console.log('Attempting to set new password for:', username);
                
                try {
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

                    console.log('Response status:', response.status);
                    const data = await response.json();
                    console.log('Response data:', data);

                    if (data.success) {
                        alert(data.message);
                        // Reset form for fresh login
                        document.getElementById('new_password').value = '';
                        document.getElementById('password').value = '';
                        userState.hasPassword = true;
                        updateFormState();
                    } else {
                        document.getElementById('newPasswordError').textContent = 
                            data.message || 'Failed to set password. Please try again.';
                    }
                } catch (error) {
                    console.error('Error setting password:', error);
                    document.getElementById('newPasswordError').textContent = 
                        'An error occurred. Please try again.';
                }

            } else {
                // Handle normal login
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
                        document.getElementById('passwordError').textContent = data.message;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
    </script>
</body>
</html>