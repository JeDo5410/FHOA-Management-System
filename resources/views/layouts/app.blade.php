<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fortezza HOA Financial Management System')</title>
    @php
        $isNgrok = str_contains(request()->getHost(), 'ngrok');
    @endphp

    <link href="{{ $isNgrok ? secure_asset('assets/lib/bootstrap/css/bootstrap.min.css') : asset('assets/lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $isNgrok ? secure_asset('assets/lib/sweetalert2/css/sweetalert2.min.css') : asset('assets/lib/sweetalert2/css/sweetalert2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        .header {
            background-color: white;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            width: 100%;
            z-index: 1030;
            height: 60px;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--primary-color);
        }

        .sidenav {
            height: 100vh;
            width: var(--sidebar-width);
            background-color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: var(--accent-color);
            border-left-color: var(--accent-color);
        }

        .nav-link.active {
            background-color: #e9ecef;
            color: var(--accent-color);
            border-left-color: var(--accent-color);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 80px 2rem 2rem;
            min-height: 100vh;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .sidenav {
                width: 70px;
            }
            .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
            .header h1 {
                font-size: 1.2rem;
            } 
        }
            .user-greeting {
                color: var(--primary-color);
                font-weight: 500;
            }
            
            @media (max-width: 768px) {
                .user-greeting {
                    font-size: 0.9rem;
                }
            }

    </style>
</head>
<body>
    <header class="header d-flex justify-content-between align-items-center">
        <h1>Fortezza HOA Financial Management System</h1>
        <div class="d-flex align-items-center">
            <div class="me-4">
                <span class="user-greeting">Welcome, {{ auth()->user()->username }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display: inline">
                @csrf
                <button type="submit" class="logout-btn d-flex align-items-center">
                    Logout
                </button>
            </form>
        </div>
    </header>
    
    <nav class="sidenav">
        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        @php
            $userRole = auth()->user()->role;
        @endphp
        
        @if ($userRole === 1)
            <a href="{{ route('users.users_management') }}" class="nav-link {{ request()->routeIs('users.users_management') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
        @endif
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    <script src="{{ $isNgrok ? secure_asset('assets/lib/bootstrap/js/bootstrap.bundle.min.js') : asset('assets/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $isNgrok ? secure_asset('assets/lib/sweetalert2/js/sweetalert2.all.min.js') : asset('assets/lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>
</body>
</html>