<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white">BAKERY ADMIN</span>
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
                    <span class="nav-link-text ms-1">POS System</span>
                </a>
            </li>
            <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Data Master</h6></li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'products' ? ' active' : '' }}" href="{{ route('products') }}">
                    <i class="material-icons opacity-10">inventory_2</i>
                    <span class="nav-link-text ms-1">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'categories' ? ' active' : '' }}" href="{{ route('categories') }}">
                    <i class="material-icons opacity-10">category</i>
                    <span class="nav-link-text ms-1">Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white theme-link {{ Route::currentRouteName() == 'customers' ? ' active' : '' }}" href="{{ route('customers') }}">
                    <i class="material-icons opacity-10">groups</i>
                    <span class="nav-link-text ms-1">Customers</span>
                </a>
            </li>
            <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Settings</h6></li>
            <li class="nav-item">
              
            </li>
        </ul>
    </div>
</aside>