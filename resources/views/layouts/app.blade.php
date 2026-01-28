<x-layouts.base>
    <x-navbars.sidebar :activePage="$activePage ?? 'dashboard'"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth :titlePage="$titlePage ?? 'Page'"></x-navbars.navs.auth>
        {{ $slot }}
        
    </main>
    <x-plugins></x-plugins>

    @push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
    
    {{-- 🔥 REAL-TIME: Pusher + Laravel Echo from CDN --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    
    <script>
        // Initialize Laravel Echo with Pusher
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });

        console.log('✅ Laravel Echo initialized');
    </script>
    
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