<x-layouts.base>
    <x-navbars.sidebar></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth></x-navbars.navs.auth>
        {{ $slot }}
        <x-footers.auth></x-footers.auth>
    </main>
    <x-plugins></x-plugins>

    @push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
    
    <script>
        function bootProject() {
            const color = localStorage.getItem('material-theme-color') || 'primary';
            
            // 1. Theme Sync
            document.querySelectorAll('.theme-card-header, .theme-btn').forEach(el => {
                el.classList.forEach(c => { if(c.startsWith('bg-gradient-')) el.classList.remove(c); });
                el.classList.add('bg-gradient-' + color);
            });

            // 2. QR Re-render (Mencegah QR Hilang)
            document.querySelectorAll('.qr-code-canvas').forEach(div => {
                if (div.innerHTML === "") {
                    new QRCode(div, { text: div.getAttribute('data-url'), width: 120, height: 120 });
                }
            });

            // 3. Chart Re-render
            if(typeof renderDashboardCharts === 'function') { renderDashboardCharts(); }
        }

        document.addEventListener('DOMContentLoaded', bootProject);
        document.addEventListener('livewire:navigated', bootProject);
        window.addEventListener('livewire:load', bootProject);
        
        // Listener Klik Configurator
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-color')) {
                localStorage.setItem('material-theme-color', e.target.getAttribute('data-color'));
                setTimeout(bootProject, 150);
            }
        });
    </script>
    @endpush
</x-layouts.base>