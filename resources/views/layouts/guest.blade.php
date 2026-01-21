<x-layouts.base>
    
    <main class="main-content mt-0">
        <div class="page-header min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            
            {{-- Login Form --}}
            {{ $slot }}
            
            {{-- Footer (Commented out for debugging) --}}
            {{-- <x-footers.guest></x-footers.guest> --}}
            
        </div>
    </main>

</x-layouts.base>