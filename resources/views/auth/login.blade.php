<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
                @error('username')
                    <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                @error('password')
                    <span>{{ $message }}</span>
                @enderror
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>