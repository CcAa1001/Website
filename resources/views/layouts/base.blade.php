<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('assets') }}/img/favicon.png">
    <title>Startup Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link id="pagestyle" href="{{ asset('assets') }}/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
    @livewireStyles
    <style>
        .product-card:hover { transform: translateY(-5px); transition: 0.3s; }
        .theme-btn { transition: all 0.2s ease-in-out; }
        ::-webkit-scrollbar { display: none; } /* Hide scrollbars for cleaner startup look */
    </style>
</head>
<body class="g-sidenav-show bg-gray-200">
    {{ $slot }}

    <script src="{{ asset('assets') }}/js/core/popper.min.js"></script>
    <script src="{{ asset('assets') }}/js/core/bootstrap.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    @stack('js')
    <script src="{{ asset('assets') }}/js/material-dashboard.min.js?v=3.0.0"></script>
    @livewireScripts

    <script>
        // GLOBAL THEME SYNC SCRIPT
        function syncStartupTheme() {
            const savedColor = localStorage.getItem('material-theme-color') || 'primary';
            
            // Sync Buttons
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.forEach(c => { if(c.startsWith('bg-gradient-')) btn.classList.remove(c); });
                btn.classList.add('bg-gradient-' + savedColor);
            });

            // Sync Card Headers
            document.querySelectorAll('.theme-card-header').forEach(header => {
                header.classList.forEach(c => { 
                    if(c.startsWith('bg-gradient-')) header.classList.remove(c); 
                    if(c.startsWith('shadow-')) header.classList.remove(c); 
                });
                header.classList.add('bg-gradient-' + savedColor);
                header.classList.add('shadow-' + savedColor);
            });
        }

        document.addEventListener('DOMContentLoaded', syncStartupTheme);
        document.addEventListener('livewire:navigated', syncStartupTheme);
        window.addEventListener('livewire:load', syncStartupTheme);
    </script>
</body>
</html>