{{-- Menu Item List - Navigation Links --}}
<li>
    <a class="{{ request()->routeIs('home*') ? 'active' : '' }}" href="#">Home <i class="fas fa-chevron-down"></i></a>
    <ul class="menu_droapdown">
        <li>
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Clothing Fashion 01</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('home.v2') ? 'active' : '' }}" href="{{ route('home') }}">Clothing Fashion 02</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('home.grocery') ? 'active' : '' }}" href="{{ route('home') }}">Grocery Store</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('home.beauty') ? 'active' : '' }}" href="{{ route('home') }}">Beauty & Cosmetics</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('home.gadgets') ? 'active' : '' }}" href="{{ route('home') }}">Gadgets Shop</a>
        </li>
    </ul>
</li>
<li>
    <a class="{{ request()->routeIs('shop*') ? 'active' : '' }}" href="#">Shop <i class="fas fa-chevron-down"></i></a>
    <ul class="menu_droapdown">
        <li>
            <a class="{{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}">Shop</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('shop.show') ? 'active' : '' }}" href="{{ route('shop.index') }}">Shop Details</a>
        </li>
    </ul>
</li>
<li>
    <a class="{{ request()->routeIs('vendor*') ? 'active' : '' }}" href="#">Vendor <i class="fas fa-chevron-down"></i></a>
    <ul class="menu_droapdown">
        <li>
            <a class="{{ request()->routeIs('vendors.index') ? 'active' : '' }}" href="{{ route('vendors.index') }}">Vendor</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('vendors.show') ? 'active' : '' }}" href="{{ route('vendors.index') }}">Vendor Details</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('vendor.register') ? 'active' : '' }}" href="{{ route('vendor.register') }}">Become a Vendor</a>
        </li>
    </ul>
</li>
<li>
    <a class="{{ request()->routeIs('flash-deals') ? 'active' : '' }}" href="{{ route('flash-deals') }}">Flash Deals</a>
</li>
<li>
    <a class="{{ request()->routeIs('pages*') ? 'active' : '' }}" href="#">Pages <i class="fas fa-chevron-down"></i></a>
    <ul class="menu_droapdown">
        <li>
            <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About us</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">Category</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('brands.index') ? 'active' : '' }}" href="{{ route('brands.index') }}">Brand</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('cart.index') ? 'active' : '' }}" href="{{ route('cart.index') }}">Cart view</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('wishlist') ? 'active' : '' }}" href="{{ route('wishlist') }}">Wishlist</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('compare') ? 'active' : '' }}" href="{{ route('compare') }}">Compare</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('checkout') ? 'active' : '' }}" href="{{ route('checkout') }}">Checkout</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('payment.success') ? 'active' : '' }}" href="{{ route('payment.success') }}">Payment success</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('payment.cancel') ? 'active' : '' }}" href="{{ route('payment.cancel') }}">Payment Cancel</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('track-order') ? 'active' : '' }}" href="{{ route('track-order') }}">Track order</a>
        </li>
        <li>
            <a href="{{ route('error') }}">Error/404</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">FAQ's</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('privacy') ? 'active' : '' }}" href="{{ route('privacy') }}">Privacy Policy</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('terms') ? 'active' : '' }}" href="{{ route('terms') }}">Terms and conditions</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('return-policy') ? 'active' : '' }}" href="{{ route('return-policy') }}">Return policy</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Sign in</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Sign up</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('password.request') ? 'active' : '' }}" href="{{ route('password.request') }}">Forgot password</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
        </li>
    </ul>
</li>
<li>
    <a class="{{ request()->routeIs('blog*') ? 'active' : '' }}" href="#">Blog <i class="fas fa-chevron-down"></i></a>
    <ul class="menu_droapdown">
        <li>
            <a class="{{ request()->routeIs('blog.index') ? 'active' : '' }}" href="{{ route('blog.index') }}">Blog classic</a>
        </li>
        <li>
            <a href="{{ route('blog.index') }}">Blog right sidebar</a>
        </li>
        <li>
            <a href="{{ route('blog.index') }}">Blog left sidebar</a>
        </li>
        <li>
            <a class="{{ request()->routeIs('blog.show') ? 'active' : '' }}" href="{{ route('blog.index') }}">Blog details</a>
        </li>
    </ul>
</li>
<li>
    <a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
</li>
