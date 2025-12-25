<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'dashboard' ? ' active bg-gradient-primary' : '' }}" href="{{ route('dashboard') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">dashboard</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'inventory' ? ' active bg-gradient-primary' : '' }}" href="{{ route('inventory') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">inventory_2</i>
            </div>
            <span class="nav-link-text ms-1">Katalog Produk</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'customers' ? ' active bg-gradient-primary' : '' }}" href="{{ route('customers') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">groups</i>
            </div>
            <span class="nav-link-text ms-1">Pelanggan</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'reports' ? ' active bg-gradient-primary' : '' }}" href="{{ route('reports') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">assessment</i>
            </div>
            <span class="nav-link-text ms-1">Laporan Penjualan</span>
        </a>
    </li>

    <li class="nav-item mt-3"><h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Transaksi</h6></li>
    
    <li class="nav-item">
        <a class="nav-link text-white {{ Route::currentRouteName() == 'pos' ? ' active bg-gradient-success' : '' }}" href="{{ route('pos') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">shopping_cart</i>
            </div>
            <span class="nav-link-text ms-1">Buka Kasir (POS)</span>
        </a>
    </li>
</ul>