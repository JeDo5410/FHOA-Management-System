<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HOA Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }
        .navbar {
            background-color: #2c3e50;
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content {
            padding: 2rem;
        }
        .welcome-card {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-form {
            display: inline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>HOA Management System</h1>
        <form class="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>

    <div class="content">
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
    </div>
</body>
</html>