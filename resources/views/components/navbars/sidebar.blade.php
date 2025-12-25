<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'dashboard' ? ' active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
            <i class="material-icons opacity-10">dashboard</i> <span class="ms-1">Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'inventory' ? ' active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
            <i class="material-icons opacity-10">inventory_2</i> <span class="ms-1">Katalog</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'customers' ? ' active bg-gradient-primary' : '' }}" href="{{ route('customers') }}">
            <i class="material-icons opacity-10">groups</i> <span class="ms-1">Pelanggan</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'reports' ? ' active bg-gradient-primary' : '' }}" href="{{ route('reports') }}">
            <i class="material-icons opacity-10">assessment</i> <span class="ms-1">Laporan</span>
        </a>
    </li>

    <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Point of Sale</h6></li>
    
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'pos' ? ' active bg-gradient-success' : '' }}" href="{{ route('pos') }}">
            <i class="material-icons opacity-10">shopping_cart</i> <span class="ms-1">Buka Kasir</span>
        </a>
    </li>
</ul>