@props(['activePage'])

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex text-wrap align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-2 font-weight-bold text-white">POS System Pro</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto  max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            
            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'dashboard' ? 'active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- SECTION: OPERATIONAL --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Operational</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'pos' ? 'active bg-gradient-primary' : '' }}" href="{{ route('pos') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">point_of_sale</i>
                    </div>
                    <span class="nav-link-text ms-1">Kasir (POS)</span>
                </a>
            </li>

            {{-- [ORDER LIST DIHAPUS SESUAI REQUEST] --}}

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'tables' ? 'active bg-gradient-primary' : '' }}" href="{{ route('tables') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_restaurant</i>
                    </div>
                    <span class="nav-link-text ms-1">Meja & QR Code</span>
                </a>
            </li>

            {{-- SECTION: CATALOG --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Menu & Stock</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'products' ? 'active bg-gradient-primary' : '' }}" href="{{ route('products') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">fastfood</i>
                    </div>
                    <span class="nav-link-text ms-1">Produk Jual</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'inventory' ? 'active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Bahan Baku</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'categories' ? 'active bg-gradient-primary' : '' }}" href="{{ route('categories') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">category</i>
                    </div>
                    <span class="nav-link-text ms-1">Kategori</span>
                </a>
            </li>

            {{-- SECTION: LAPORAN --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Laporan</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'reports' ? 'active bg-gradient-primary' : '' }}" href="{{ route('reports') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">assessment</i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Penjualan</span>
                </a>
            </li>

            {{-- SECTION: ADMIN (Hanya tampil jika Admin) --}}
            @if(auth()->check() && (auth()->user()->role->slug == 'admin' || auth()->user()->role->slug == 'super_admin'))
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Admin</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'user-management' ? 'active bg-gradient-primary' : '' }}" href="{{ route('user-management') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">manage_accounts</i>
                    </div>
                    <span class="nav-link-text ms-1">Kelola Karyawan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'store-settings' ? 'active bg-gradient-primary' : '' }}" href="{{ route('store-settings') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">store</i>
                    </div>
                    <span class="nav-link-text ms-1">Setting Toko</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Akun & Sistem</h6>
            </li>

            {{-- Notifications (Baru) --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'notifications' ? 'active bg-gradient-primary' : '' }}" href="#">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">notifications</i>
                    </div>
                    <span class="nav-link-text ms-1">Notifikasi</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'user-profile' ? 'active bg-gradient-primary' : '' }}" href="{{ route('profile') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">person</i>
                    </div>
                    <span class="nav-link-text ms-1">Profile Saya</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                    @csrf
                </form>
                <a class="nav-link text-white" href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">logout</i>
                    </div>
                    <span class="nav-link-text ms-1">Keluar (Sign Out)</span>
                </a>
            </li>
        </ul>
    </div>
</aside>