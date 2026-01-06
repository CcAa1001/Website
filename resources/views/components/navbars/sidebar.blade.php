<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white text-uppercase">Admin Panel</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'dashboard' ? ' active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="material-icons opacity-10">dashboard</i>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'pos' ? ' active' : '' }}" href="{{ route('pos') }}">
                    <i class="material-icons opacity-10">shopping_cart</i>
                    <span class="nav-link-text ms-1">Point of Sale</span>
                </a>
            </li>
            
            <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Data Master</h6></li>
            
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'inventory' ? ' active' : '' }}" href="{{ route('inventory') }}">
                    <i class="material-icons opacity-10">inventory_2</i>
                    <span class="nav-link-text ms-1">Produk & Menu</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'categories' ? ' active' : '' }}" href="{{ route('categories') }}">
                    <i class="material-icons opacity-10">category</i>
                    <span class="nav-link-text ms-1">Kategori</span>
                </a>
            </li>
            
            <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Analitik</h6></li>
            
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'reports' ? ' active' : '' }}" href="{{ route('reports') }}">
                    <i class="material-icons opacity-10">assessment</i>
                    <span class="nav-link-text ms-1">Laporan Penjualan</span>
                </a>
            </li>
            
            <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Manajemen Akun</h6></li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'user-management' ? ' active' : '' }}" href="{{ route('user-management') }}">
                    <i class="material-icons opacity-10">group</i>
                    <span class="nav-link-text ms-1">Kelola Staff</span>
                </a>
            </li>
        </ul>
    </div>
</aside>