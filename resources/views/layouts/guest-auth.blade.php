<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>Login - POS System</title>
    
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link id="pagestyle" href="{{ asset('assets/css/material-dashboard.css') }}" rel="stylesheet" />

    <style>
        /* GLASSMORPHISM BACKGROUND */
        body {
            /* Ganti URL gambar di bawah ini dengan gambar background yang Anda suka */
            background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* OVERLAY HITAM SUPAYA TEKS JELAS */
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); 
            z-index: -1;
        }

        /* EFEK KACA (GLASS CARD) */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            border-radius: 16px;
            padding: 3rem 2rem;
            width: 100%;
            max-width: 400px;
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }
        
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.2) !important;
            border-color: #fff !important;
            color: #fff !important;
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
    </style>
    @livewireStyles
</head>

<body>
    {{ $slot }}

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    @livewireScripts
</body>
</html>