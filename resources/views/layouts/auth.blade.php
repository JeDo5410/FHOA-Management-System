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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 80px;
        }

        .header {
            background: linear-gradient(to right, #ffffff, #f8f9fa);
            padding: 1rem 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #1a237e;
            font-weight: 600;
        }

        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.2rem;
            font-weight: 500;
            color: #3949ab;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            background: rgba(57, 73, 171, 0.1);
        }

        .auth-content {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(to bottom, #ffffff, #ffffff);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }

        .card-body {
            padding: 2.5rem !important;
        }

        h2 {
            color: #1a237e;
            font-weight: 600;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .form-label {
            color: #3949ab;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 0.8rem 1rem;
            border: 2px solid #e8eaf6;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #3949ab;
            box-shadow: 0 0 0 0.2rem rgba(57, 73, 171, 0.15);
        }

        .form-control.is-invalid {
            border-color: #ff5252;
            background-image: none;
        }

        .invalid-feedback {
            color: #ff5252;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .btn-primary {
            background-color: #3949ab;
            border: none;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
        }

        .btn-primary:hover {
            background-color: #283593;
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .btn-primary:disabled {
            background-color: #c5cae9;
            cursor: not-allowed;
        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
        }

        /* Custom animation for form groups */
        .mb-3 {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mb-3:nth-child(1) { animation-delay: 0.1s; }
        .mb-3:nth-child(2) { animation-delay: 0.2s; }
        .mb-3:nth-child(3) { animation-delay: 0.3s; }
        
        /* Input group improvements */
        .input-group {
            position: relative;
        }

        .form-control {
            background: #ffffff;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.2rem;
            }

            .header-center {
                font-size: 1rem;
                padding: 0.4rem 1.2rem;
            }

            .card-body {
                padding: 2rem !important;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Fortezza HOA Financial Management System</h1>
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