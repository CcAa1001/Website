@php
    $user = auth()->user();
    
    $permissions = [];
    if ($user && $user->role) {
        if (is_array($user->role->permissions)) {
            $permissions = $user->role->permissions;
        } else {
            $permissions = json_decode($user->role->permissions, true) ?? [];
        }
    }

    $roleName = strtolower($user->role->name ?? '');
    $roleSlug = strtolower($user->role->slug ?? '');
    $isAdmin = in_array($roleName, ['admin', 'super admin', 'super_admin']) || 
               in_array($roleSlug, ['admin', 'super_admin']);
    
    $can = function($feature) use ($permissions, $isAdmin, $user) {
        if (!$user) return false;
        if ($isAdmin) return true;
        if (in_array('*', $permissions)) return true;
        return in_array($feature, $permissions);
    };
@endphp

@auth
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0 d-flex text-wrap align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">

                <span class="ms-2 font-weight-bold text-white">Nibble</span>
            </a>
    </div>
    
    <hr class="horizontal light mt-0 mb-2">
    
    <div class="collapse navbar-collapse w-auto h-auto max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            
            {{-- 1. DASHBOARD --}}
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

            {{-- 2. OPERATIONS (Collapsible) --}}
            <li class="nav-item mt-2">
                <a data-bs-toggle="collapse" href="#opsMenu" class="nav-link text-white" aria-controls="opsMenu" role="button" aria-expanded="{{ request()->routeIs(['pos', 'tables', 'orders*']) ? 'true' : 'false' }}">
                    {{-- Ikon Kiri (Ditambahkan) --}}
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">grid_view</i>
                    </div>
                    <span class="nav-link-text ms-1">Operations</span>
                    {{-- Panah Kanan MANUAL DIHAPUS agar tidak double --}}
                </a>
                
                <div class="collapse {{ request()->routeIs(['pos', 'tables', 'orders*']) ? 'show' : '' }}" id="opsMenu">
                    <ul class="nav flex-column ms-3">
                        @if($can('pos'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('pos') ? 'active bg-gradient-primary' : '' }}" href="{{ route('pos') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">point_of_sale</i>
                                </div>
                                <span class="nav-link-text ms-1">POS System</span>
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
                                <span class="nav-link-text ms-1">Pesanan Aktif</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            {{-- 3. INVENTORY (Collapsible) --}}
            <li class="nav-item mt-2">
                <a data-bs-toggle="collapse" href="#prodMenu" class="nav-link text-white" aria-controls="prodMenu" role="button" aria-expanded="{{ request()->routeIs(['products', 'inventory', 'categories']) ? 'true' : 'false' }}">
                    {{-- Ikon Kiri (Ditambahkan) --}}
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Inventory</span>
                </a>

                <div class="collapse {{ request()->routeIs(['products', 'inventory', 'categories']) ? 'show' : '' }}" id="prodMenu">
                    <ul class="nav flex-column ms-3">
                        @if($can('products'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('products') ? 'active bg-gradient-primary' : '' }}" href="{{ route('products') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">fastfood</i>
                                </div>
                                <span class="nav-link-text ms-1">Products</span>
                            </a>
                        </li>
                        @endif

                        @if($can('inventory'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('inventory') ? 'active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">shelves</i>
                                </div>
                                <span class="nav-link-text ms-1">Stocks</span>
                            </a>
                        </li>
                        @endif

                        @if($can('products') || $can('categories')) 
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('categories') ? 'active bg-gradient-primary' : '' }}" href="{{ route('categories') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">category</i>
                                </div>
                                <span class="nav-link-text ms-1">Category</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            {{-- 4. REPORTS (Collapsible) --}}
            <li class="nav-item mt-2">
                <a data-bs-toggle="collapse" href="#reportMenu" class="nav-link text-white" aria-controls="reportMenu" role="button" aria-expanded="{{ request()->routeIs(['transactions']) ? 'true' : 'false' }}">
                    {{-- Ikon Kiri (Ditambahkan) --}}
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">analytics</i>
                    </div>
                    <span class="nav-link-text ms-1">Reports</span>
                </a>

                <div class="collapse {{ request()->routeIs(['transactions']) ? 'show' : '' }}" id="reportMenu">
                    <ul class="nav flex-column ms-3">
                        @if($can('transactions'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('transactions') ? 'active bg-gradient-primary' : '' }}" href="{{ route('transactions') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">assessment</i>
                                </div>
                                <span class="nav-link-text ms-1">Laporan</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            {{-- 5. ADMINISTRATION (Collapsible) --}}
            @if($isAdmin || $can('users') || $can('settings') || $can('roles'))
            <li class="nav-item mt-2">
                <a data-bs-toggle="collapse" href="#adminMenu" class="nav-link text-white" aria-controls="adminMenu" role="button" aria-expanded="{{ request()->routeIs(['user-management', 'roles', 'store-settings']) ? 'true' : 'false' }}">
                    {{-- Ikon Kiri (Ditambahkan) --}}
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">admin_panel_settings</i>
                    </div>
                    <span class="nav-link-text ms-1">Administration</span>
                </a>

                <div class="collapse {{ request()->routeIs(['user-management', 'roles', 'store-settings']) ? 'show' : '' }}" id="adminMenu">
                    <ul class="nav flex-column ms-3">
                        @if($can('users'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ Route::currentRouteName() == 'user-management' ? 'active bg-gradient-primary' : '' }}" 
                            href="{{ route('user-management') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">people</i>
                                </div>
                                <span class="nav-link-text ms-1">User Management</span>
                            </a>
                        </li>
                        @endif

                        @if($can('roles'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ Route::currentRouteName() == 'roles' ? 'active bg-gradient-primary' : '' }}" 
                            href="{{ route('roles') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">badge</i>
                                </div>
                                <span class="nav-link-text ms-1">Role Management</span>
                            </a>
                        </li>
                        @endif

                        @if($can('settings'))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('store-settings') ? 'active bg-gradient-primary' : '' }}" href="{{ route('store-settings') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">settings</i>
                                </div>
                                <span class="nav-link-text ms-1">Detail Setting</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            
            {{-- 6. ACCOUNT (Collapsible) --}}
            <li class="nav-item mt-2">
                <a data-bs-toggle="collapse" href="#accountMenu" class="nav-link text-white" aria-controls="accountMenu" role="button" aria-expanded="{{ request()->routeIs(['profile']) ? 'true' : 'false' }}">
                    {{-- Ikon Kiri (Ditambahkan) --}}
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">account_circle</i>
                    </div>
                    <span class="nav-link-text ms-1">Account</span>
                </a>

                <div class="collapse {{ request()->routeIs(['profile']) ? 'show' : '' }}" id="accountMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('profile') ? 'active bg-gradient-primary' : '' }}" href="{{ route('profile') }}">
                                <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="material-icons opacity-10">person</i>
                                </div>
                                <span class="nav-link-text ms-1">Profile</span>
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
                                <span class="nav-link-text ms-1">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
    </div>
</aside>
@endauth