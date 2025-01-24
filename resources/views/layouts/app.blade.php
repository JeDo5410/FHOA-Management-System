<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HOA Management System')</title>
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
        .header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            z-index: 100;
        }
        .sidenav {
            height: 100vh;
            width: 250px;
            background-color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 100px 2rem 2rem;
        }
        .nav-link {
            display: block;
            padding: 1rem 2rem;
            color: #2c3e50;
            text-decoration: none;
        }
        .nav-link:hover {
            background-color: #f4f6f8;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>HOA Management System</h1>
        <form action="{{ route('logout') }}" method="POST" style="display: inline">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <nav class="sidenav">
        <a href="/dashboard" class="nav-link">Dashboard</a>
        <a href="#" class="nav-link">Residents</a>
        <a href="#" class="nav-link">Announcements</a>
        <a href="#" class="nav-link">Maintenance</a>
        <a href="#" class="nav-link">Payments</a>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>