@php
    // 1. Cek apakah user login. Jika tidak, set variabel default agar tidak error.
    $user = auth()->user();
    $permissions = [];
    $isAdmin = false;

    if ($user && $user->role) {
        // Ambil permissions (Handle Array atau JSON String)
        if (is_array($user->role->permissions)) {
            $permissions = $user->role->permissions;
        } else {
            $permissions = json_decode($user->role->permissions, true) ?? [];
        }

        // Cek Admin berdasarkan Nama atau Slug (Lebih aman)
        // Ambil slug dari role jika ada, atau gunakan nama
        $roleName = strtolower($user->role->name ?? '');
        $roleSlug = strtolower($user->role->slug ?? '');
        
        $isAdmin = in_array($roleName, ['admin', 'super admin', 'super_admin']) || 
                   in_array($roleSlug, ['admin', 'super_admin']);
    }

    // 2. Helper function untuk cek akses menu
    $can = function($feature) use ($permissions, $isAdmin, $user) {
        // Jika user belum login, tolak semua
        if (!$user) return false;
        
        // Jika Admin, izinkan semua
        if ($isAdmin) return true;

        // Jika punya permission Wildcard (*), izinkan semua
        if (in_array('*', $permissions)) return true;

        // Cek permission spesifik
        return in_array($feature, $permissions);
    };
@endphp

{{-- HANYA TAMPILKAN SIDEBAR JIKA USER SUDAH LOGIN --}}
@auth
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
            @if($can('dashboard'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            @endif

            {{-- OPERASIONAL --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Operasional</h6>
            </li>
            
            @if($can('pos'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('pos') ? 'active bg-gradient-primary' : '' }}" href="{{ route('pos') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">point_of_sale</i>
                    </div>
                    <span class="nav-link-text ms-1">Kasir (POS)</span>
                </a>
            </li>
            @endif

            @if($can('tables'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('tables') ? 'active bg-gradient-primary' : '' }}" href="{{ route('tables') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_restaurant</i>
                    </div>
                    <span class="nav-link-text ms-1">Meja & QR</span>
                </a>
            </li>
            @endif

            @if($can('orders'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('orders*') ? 'active bg-gradient-primary' : '' }}" href="{{ route('orders') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">receipt</i>
                    </div>
                    <span class="nav-link-text ms-1">Pesanan Aktif (KDS)</span>
                </a>
            </li>
            @endif

            {{-- MANAJEMEN PRODUK --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Manajemen Produk</h6>
            </li>

            @if($can('products'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('products') ? 'active bg-gradient-primary' : '' }}" href="{{ route('products') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">fastfood</i>
                    </div>
                    <span class="nav-link-text ms-1">Daftar Produk</span>
                </a>
            </li>
            @endif

            @if($can('inventory'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('inventory') ? 'active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Stok Bahan</span>
                </a>
            </li>
            @endif

            @if($can('products') || $can('categories')) 
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('categories') ? 'active bg-gradient-primary' : '' }}" href="{{ route('categories') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">category</i>
                    </div>
                    <span class="nav-link-text ms-1">Kategori</span>
                </a>
            </li>
            @endif

            {{-- LAPORAN --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Laporan</h6>
            </li>

            @if($can('transactions'))
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('transactions') ? 'active bg-gradient-primary' : '' }}" href="{{ route('transactions') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">assessment</i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan & Transaksi</span>
                </a>
            </li>
            @endif

            {{-- ADMIN AREA --}}
            @if($isAdmin || $can('users') || $can('settings') || $can('roles'))
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Administrasi</h6>
            </li>
            @endif
                        
            @if($can('users'))
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'user-management' ? 'active bg-gradient-primary' : '' }}" 
                   href="{{ route('user-management') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">people</i>
                    </div>
                    <span class="nav-link-text ms-1">Kelola Karyawan</span>
                </a>
            </li>
            @endif

            @if($can('roles'))
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'roles' ? 'active bg-gradient-primary' : '' }}" 
                   href="{{ route('roles') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">admin_panel_settings</i>
                    </div>
                    <span class="nav-link-text ms-1">Kelola Role & Akses</span>
                </a>
            </li>
            @endif

            @if($can('settings'))
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
@endauth