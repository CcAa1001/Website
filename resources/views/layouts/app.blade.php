<x-layouts.base>
    @if (in_array(request()->route()->getName(),['static-sign-in', 'static-sign-up', 'register', 'login','password.forgot','reset-password']))
        <div class="container position-sticky z-index-sticky top-0">
            <div class="row">
                <div class="col-12">
                    <x-navbars.navs.guest></x-navbars.navs.guest>
                </div>
            </div>
        </div>
        @if (in_array(request()->route()->getName(),['static-sign-in', 'login','password.forgot','reset-password']))
        <main class="main-content  mt-0">
            <div class="page-header page-header-bg align-items-start min-vh-100">
                    <span class="mask bg-gradient-dark opacity-6"></span>
            {{ $slot }}
            <x-footers.guest></x-footers.guest>
             </div>
        </main>
        @else
        {{ $slot }}
        @endif

    @elseif (in_array(request()->route()->getName(),['rtl']))
    {{ $slot }}
    @elseif (in_array(request()->route()->getName(),['virtual-reality']))
    <div class="virtual-reality">
        <x-navbars.navs.auth></x-navbars.navs.auth>
        <div class="border-radius-xl mx-2 mx-md-3 position-relative"
            style="background-image: url('{{ asset('assets') }}/img/vr-bg.jpg'); background-size: cover;">
            <x-navbars.sidebar></x-navbars.sidebar>
            <main class="main-content border-radius-lg h-100">
                {{  $slot }}

        </div>
        <x-footers.auth></x-footers.auth>
        </main>
        <x-plugins></x-plugins>
    </div>
    @else
    <x-navbars.sidebar></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth></x-navbars.navs.auth>

        {{ $slot }}

        <x-footers.auth></x-footers.auth>
    </main>
    <x-plugins></x-plugins>
    @endif
    <script>
        // Fungsi untuk menerapkan tema yang tersimpan
        function applySavedTheme() {
            const savedColor = localStorage.getItem('theme-color') || 'primary';
            const isDark = localStorage.getItem('dark-mode') === 'true';

            // 1. Terapkan Warna Sidebar
            const activeLink = document.querySelector(".nav-link.active");
            if (activeLink) {
                // Hapus class gradient lama
                activeLink.className = activeLink.className.replace(/\bbg-gradient-\w+/g, '');
                activeLink.classList.add('bg-gradient-' + savedColor);
            }

            // 2. Terapkan Dark Mode
            if (isDark) {
                document.body.classList.add('dark-version');
                const darkBtn = document.getElementById('dark-version');
                if (darkBtn) darkBtn.setAttribute("checked", "true");
            }
        }

        // Monitor klik pada pilihan warna di Configurator
        document.addEventListener('click', function (e) {
            if (e.target.hasAttribute('onclick') && e.target.getAttribute('onclick').includes('sidebarColor')) {
                const color = e.target.getAttribute('data-color');
                localStorage.setItem('theme-color', color);
            }
            
            if (e.target.id === 'dark-version') {
                setTimeout(() => {
                    const isDark = document.body.classList.contains('dark-version');
                    localStorage.setItem('dark-mode', isDark);
                }, 100);
            }
        });

        // Jalankan saat halaman dimuat
        window.addEventListener('load', applySavedTheme);
        document.addEventListener('livewire:load', applySavedTheme); // Untuk kompatibilitas Livewire
    </script>
</x-layouts.base>