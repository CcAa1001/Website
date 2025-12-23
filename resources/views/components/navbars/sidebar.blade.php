<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white">Startup Platform v1.0</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'dashboard' ? ' active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Analytics/Reporting</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Modules</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'pos' ? ' active bg-gradient-primary' : '' }}" href="{{ route('pos') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">point_of_sale</i>
                    </div>
                    <span class="nav-link-text ms-1">POS Checkout</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'inventory' ? ' active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Menu/Inventory</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Customer Branding</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('public.menu', ['userId' => auth()->id()]) }}" target="_blank">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">qr_code</i>
                    </div>
                    <span class="nav-link-text ms-1">My Digital Menu</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Management</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'user-management' ? ' active bg-gradient-primary' : '' }}" href="{{ route('user-management') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">people</i>
                    </div>
                    <span class="nav-link-text ms-1">Users/Tenants</span>
                </a>
            </li>
        </ul>
    </div>
</aside>