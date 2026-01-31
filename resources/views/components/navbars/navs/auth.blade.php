@props(['titlePage'])

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        
        {{-- 1. BREADCRUMB & JUDUL HALAMAN --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $titlePage ?? 'Dashboard' }}</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">{{ $titlePage ?? 'Dashboard' }}</h6>
        </nav>
        
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            
            {{-- Spacer agar elemen berikutnya terdorong ke kanan --}}
            <div class="ms-md-auto pe-md-3 d-flex align-items-center"></div>

            {{-- 2. JAM DIGITAL & TANGGAL (Hanya tampil di Desktop) --}}
            <div class="me-3 d-flex flex-column align-items-end d-none d-md-flex text-end">
                <h6 class="mb-0 font-weight-bold text-dark" id="clock-time" style="line-height: 1.2;">00:00:00</h6>
                <span class="text-xs text-secondary" id="clock-date">Memuat tanggal...</span>
            </div>

            <ul class="navbar-nav justify-content-end">
                
                {{-- 3. LINK PROFIL (ICON USER) --}}
                <li class="nav-item d-flex align-items-center">
                    <a href="{{ route('profile') }}" class="nav-link text-body font-weight-bold px-0">
                        <i class="fa fa-user me-sm-1"></i>
                        <span class="d-sm-inline d-none">{{ auth()->user()->name ?? 'User' }}</span>
                    </a>
                </li>

                {{-- 4. SIDEBAR TOGGLER (Hanya tampil di Mobile/Tablet) --}}
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

{{-- SCRIPT JAM DIGITAL --}}
<script>
    function updateClock() {
        const now = new Date();
        
        // Format Waktu (24 Jam)
        const timeString = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        }).replace(/\./g, ':'); // Pastikan pemisah adalah titik dua

        // Format Tanggal (Senin, 1 Januari 2024)
        const dateString = now.toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const timeElement = document.getElementById('clock-time');
        const dateElement = document.getElementById('clock-date');

        if(timeElement) timeElement.textContent = timeString;
        if(dateElement) dateElement.textContent = dateString;
    }

    // Jalankan setiap detik
    setInterval(updateClock, 1000);
    // Jalankan segera saat load agar tidak ada delay 1 detik pertama
    document.addEventListener('DOMContentLoaded', updateClock);
</script>