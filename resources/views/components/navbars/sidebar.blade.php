<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex text-wrap align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-2 font-weight-bold text-white">POS System</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto h-auto max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            
            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- OPERASIONAL --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Operasional</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('pos') ? 'active bg-gradient-primary' : '' }}" href="{{ route('pos') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">point_of_sale</i>
                    </div>
                    <span class="nav-link-text ms-1">Kasir (POS)</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('tables') ? 'active bg-gradient-primary' : '' }}" href="{{ route('tables') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_restaurant</i>
                    </div>
                    <span class="nav-link-text ms-1">Meja & QR</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('orders*') ? 'active bg-gradient-primary' : '' }}" href="{{ route('orders') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">receipt</i>
                    </div>
                    <span class="nav-link-text ms-1">Pesanan Aktif</span>
                </a>
            </li>

            {{-- PRODUK & STOK --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Manajemen Produk</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('products') ? 'active bg-gradient-primary' : '' }}" href="{{ route('products') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">fastfood</i>
                    </div>
                    <span class="nav-link-text ms-1">Daftar Produk</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('inventory') ? 'active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Stok Bahan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('categories') ? 'active bg-gradient-primary' : '' }}" href="{{ route('categories') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">category</i>
                    </div>
                    <span class="nav-link-text ms-1">Kategori</span>
                </a>
            </li>

            {{-- LAPORAN --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Laporan</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('reports') ? 'active bg-gradient-primary' : '' }}" href="{{ route('reports') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">assessment</i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Penjualan</span>
                </a>
            </li>

            {{-- ADMIN AREA --}}
            @if(auth()->check() && (auth()->user()->role->slug == 'admin' || auth()->user()->role->slug == 'super_admin'))
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Administrasi</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('user-management') ? 'active bg-gradient-primary' : '' }}" href="{{ route('user-management') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">people</i>
                    </div>
                    <span class="nav-link-text ms-1">Kelola Karyawan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('store-settings') ? 'active bg-gradient-primary' : '' }}" href="{{ route('store-settings') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">settings</i>
                    </div>
                    <span class="nav-link-text ms-1">Pengaturan Toko</span>
                </a>
            </li>
            @endif
            
            {{-- AKUN --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Akun</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('profile') ? 'active bg-gradient-primary' : '' }}" href="{{ route('profile') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">person</i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>

            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                    @csrf
                </form>
                <a class="nav-link text-white" href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">logout</i>
                    </div>
                    <span class="nav-link-text ms-1">Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</aside>