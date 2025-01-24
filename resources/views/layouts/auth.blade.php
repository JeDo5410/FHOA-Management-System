<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fortezza HOA Management System')</title>
    @php
        $isNgrok = str_contains(request()->getHost(), 'ngrok');
    @endphp

    <link href="{{ $isNgrok ? secure_asset('assets/lib/bootstrap/css/bootstrap.min.css') : asset('assets/lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $isNgrok ? secure_asset('assets/lib/sweetalert2/css/sweetalert2.min.css') : asset('assets/lib/sweetalert2/css/sweetalert2.min.css') }}" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 80px;
        }
        .header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
        }
        .header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #2c3e50;
        }
        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .auth-content {
            flex: 1;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Fortezza HOA Management System</h1>
        <div class="header-center">Login</div>
        <div style="width: 150px;"></div>
    </header>
    
    <div class="auth-content">
        @yield('content')
    </div>

    <script src="{{ $isNgrok ? secure_asset('assets/lib/bootstrap/js/bootstrap.bundle.min.js') : asset('assets/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $isNgrok ? secure_asset('assets/lib/sweetalert2/js/sweetalert2.all.min.js') : asset('assets/lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>
</body>
</html>