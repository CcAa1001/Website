<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Menu') | {{ config('app.name') }}</title>
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Frontend Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/cart.css') }}">
    
    {{-- Page Specific Styles --}}
    @stack('styles')
    
    {{-- Livewire Styles --}}
    @livewireStyles
    
    <style>
    /* Base Styles */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: #f8f9fa;
        color: #333;
    }
    
    /* Toast Container */
    .toast_container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .toast_item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #333;
        color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        min-width: 250px;
        max-width: 350px;
    }
    
    .toast_icon {
        width: 24px;
        height: 24px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .toast_icon i { font-size: 12px; color: #fff; }
    .toast_content { flex: 1; }
    .toast_message { font-size: 13px; font-weight: 500; }
    
    .toast_close {
        width: 24px;
        height: 24px;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 10px;
        cursor: pointer;
        opacity: 0.6;
    }
    
    .toast_close:hover { opacity: 1; background: rgba(255, 255, 255, 0.2); }
    
    /* Cart Button Pulse */
    .cart_floating_btn.pulse {
        animation: cart-pulse 0.6s ease;
    }
    
    @keyframes cart-pulse {
        0% { transform: scale(1); }
        30% { transform: scale(1.15); }
        60% { transform: scale(0.95); }
        100% { transform: scale(1); }
    }
    
    /* Mobile Toast */
    @media (max-width: 575px) {
        .toast_container {
            top: auto;
            bottom: 90px;
            left: 15px;
            right: 15px;
        }
        .toast_item { min-width: auto; max-width: none; }
    }
    
    /* Hide Alpine cloak */
    [x-cloak] { display: none !important; }
    </style>
</head>

<body class="public-layout">

    {{-- Main Content --}}
    @yield('content')

    {{-- Cart Component (Floating + Sidebar) --}}
    @livewire('public.cart')

    {{-- Toast Notifications --}}
    <div 
        x-data="toastNotification()" 
        x-on:cart-updated.window="show($event.detail)"
        x-on:cart-item-added.window="pulseCart()"
        class="toast_container"
    >
        <template x-for="(toast, index) in toasts" :key="index">
            <div 
                class="toast_item"
                :class="{ 'show': toast.visible }"
                x-show="toast.visible"
                x-transition
            >
                <div class="toast_icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="toast_content">
                    <span class="toast_message" x-text="toast.message"></span>
                </div>
                <button type="button" class="toast_close" @click="dismiss(index)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </template>
    </div>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Livewire Scripts --}}
    @livewireScripts
    
    {{-- Page Specific Scripts --}}
    @stack('scripts')

    {{-- Toast Notification Script --}}
    <script>
    function toastNotification() {
        return {
            toasts: [],
            
            show(detail) {
                const toast = {
                    message: detail.message || 'Item updated',
                    visible: true
                };
                
                this.toasts.push(toast);
                const index = this.toasts.length - 1;
                
                setTimeout(() => {
                    this.dismiss(index);
                }, 3000);
            },
            
            dismiss(index) {
                if (this.toasts[index]) {
                    this.toasts[index].visible = false;
                    setTimeout(() => {
                        this.toasts.splice(index, 1);
                    }, 300);
                }
            },
            
            pulseCart() {
                const cartBtn = document.querySelector('.cart_floating_btn');
                if (cartBtn) {
                    cartBtn.classList.add('pulse');
                    setTimeout(() => cartBtn.classList.remove('pulse'), 600);
                }
            }
        }
    }
    </script>

</body>
</html>