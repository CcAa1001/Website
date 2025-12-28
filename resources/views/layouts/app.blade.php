<x-layouts.base>
    @if (in_array(request()->route()->getName(),['static-sign-in', 'static-sign-up', 'register', 'login', 'forgot-password', 'reset-password']))
        {{ $slot }}
    {{-- Perbaikan penutupan tag Virtual Reality --}}
    @elseif (in_array(request()->route()->getName(),['virtual-reality']))
        <div class="virtual-reality">
            <x-navbars.navs.auth></x-navbars.navs.auth>
            <div class="border-radius-xl mx-2 mx-md-3 position-relative"
                style="background-image: url('{{ asset('assets') }}/img/vr-bg.jpg'); background-size: cover;">
                <x-navbars.sidebar></x-navbars.sidebar>
                <main class="main-content border-radius-lg h-100">
                    {{ $slot }}
                </main>
            </div>
            <x-footers.auth></x-footers.auth>
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

    {{-- Script Sinkronisasi Warna Konfigurator --}}
    @push('js')
    <script>
        function applySavedTheme() {
            const savedColor = localStorage.getItem('material-theme-color') || 'primary';
            const sidebarType = localStorage.getItem('material-sidebar-type') || 'bg-gradient-dark';
            const isDarkMode = localStorage.getItem('material-dark-mode') === 'true';

            // Terapkan Warna Aktif Sidebar
            const activeLink = document.querySelector('.nav-link.active');
            if (activeLink) {
                // Hapus class gradient lama
                activeLink.classList.forEach(className => {
                    if (className.startsWith('bg-gradient-')) activeLink.classList.remove(className);
                });
                activeLink.classList.add('bg-gradient-' + savedColor);
            }

            // Terapkan Dark Mode
            if (isDarkMode) {
                document.body.classList.add('dark-version');
                const darkBtn = document.getElementById('dark-version');
                if (darkBtn) darkBtn.checked = true;
            }
        }

        // Jalankan saat load dan saat Livewire melakukan update DOM
        document.addEventListener('DOMContentLoaded', applySavedTheme);
        window.addEventListener('popstate', applySavedTheme);
        
        // Listener untuk menangkap perubahan dari Configurator (x-plugins)
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target.hasAttribute('data-color')) {
                localStorage.setItem('material-theme-color', target.getAttribute('data-color'));
            }
            if (target.id === 'dark-version') {
                setTimeout(() => {
                    localStorage.setItem('material-dark-mode', document.body.classList.contains('dark-version'));
                }, 100);
            }
        });
    </script>
    @endpush
</x-layouts.base>