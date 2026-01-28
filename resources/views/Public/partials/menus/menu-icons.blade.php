{{-- Menu Icons - Compare, Wishlist, Cart, User --}}
<li>
    <a href="{{ route('compare') }}">
        <b>
            <img src="{{ asset('assets/images/compare_black.svg') }}" alt="Compare" class="img-fluid">
        </b>
        <span>{{ $compareCount ?? 2 }}</span>
    </a>
</li>
<li>
    <a href="{{ route('wishlist') }}">
        <b>
            <img src="{{ asset('assets/images/love_black.svg') }}" alt="Wishlist" class="img-fluid">
        </b>
        <span>{{ $wishlistCount ?? 5 }}</span>
    </a>
</li>
<li>
    <a data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
        <b>
            <img src="{{ asset('assets/images/cart_black.svg') }}" alt="Cart" class="img-fluid">
        </b>
        <span>{{ $cartCount ?? 3 }}</span>
    </a>
</li>

@if(isset($hasAccountDropdown) && $hasAccountDropdown)
    <li>
        @auth
            <a class="user" href="{{ route('dashboard') }}">
                <b>
                    <img src="{{ asset('assets/images/user_icon_black.svg') }}" alt="User" class="img-fluid">
                </b>
                <h5>{{ auth()->user()->name }}</h5>
            </a>
            <ul class="user_dropdown">
                <li>
                    <a href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.profile') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        My Account
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.orders') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        My Order
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.wishlist') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        Wishlist
                    </a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        @else
            <a class="user" href="{{ route('login') }}">
                <b>
                    <img src="{{ asset('assets/images/user_icon_black.svg') }}" alt="User" class="img-fluid">
                </b>
                <h5>Login</h5>
            </a>
            <ul class="user_dropdown">
                <li>
                    <a href="{{ route('login') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Sign In
                    </a>
                </li>
                <li>
                    <a href="{{ route('register') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        Sign Up
                    </a>
                </li>
            </ul>
        @endauth
    </li>
@endif
