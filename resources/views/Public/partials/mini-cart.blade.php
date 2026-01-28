{{-- Mini Cart Offcanvas --}}
<div class="mini_cart">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">My Cart <span>({{ $cartCount ?? 5 }})</span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="far fa-times"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <ul>
                {{-- Static cart items for design --}}
                <li>
                    <a href="{{ route('shop.index') }}" class="cart_img">
                        <img src="{{ asset('assets/images/product_1.png') }}" alt="product" class="img-fluid w-100">
                    </a>
                    <div class="cart_text">
                        <a class="cart_title" href="{{ route('shop.index') }}">Men's Fashionable Hoodie</a>
                        <p>$140 <del>$150</del></p>
                        <span><b>Color:</b> Red</span>
                        <span><b>Size:</b> XL (Extra Large)</span>
                    </div>
                    <a class="del_icon" href="#"><i class="fal fa-times"></i></a>
                </li>
                <li>
                    <a href="{{ route('shop.index') }}" class="cart_img">
                        <img src="{{ asset('assets/images/product_2.png') }}" alt="product" class="img-fluid w-100">
                    </a>
                    <div class="cart_text">
                        <a class="cart_title" href="{{ route('shop.index') }}">Kids Cotton Combo Set</a>
                        <p>$130 <del>$160</del></p>
                        <span><b>Color:</b> Orange</span>
                        <span><b>Size:</b> M (Medium)</span>
                    </div>
                    <a class="del_icon" href="#"><i class="fal fa-times"></i></a>
                </li>
                <li>
                    <a href="{{ route('shop.index') }}" class="cart_img">
                        <img src="{{ asset('assets/images/product_3.png') }}" alt="product" class="img-fluid w-100">
                    </a>
                    <div class="cart_text">
                        <a class="cart_title" href="{{ route('shop.index') }}">Women's Western Party Dress</a>
                        <p>$90 <del>$100</del></p>
                        <span><b>Color:</b> Purple</span>
                        <span><b>Size:</b> S (Small)</span>
                    </div>
                    <a class="del_icon" href="#"><i class="fal fa-times"></i></a>
                </li>
                <li>
                    <a href="{{ route('shop.index') }}" class="cart_img">
                        <img src="{{ asset('assets/images/product_4.png') }}" alt="product" class="img-fluid w-100">
                    </a>
                    <div class="cart_text">
                        <a class="cart_title" href="{{ route('shop.index') }}">Men's Trendy Formal Shoes</a>
                        <p>$140</p>
                        <span><b>Color:</b> Blue</span>
                        <span><b>Size:</b> XL (Extra Large)</span>
                    </div>
                    <a class="del_icon" href="#"><i class="fal fa-times"></i></a>
                </li>
                <li>
                    <a href="{{ route('shop.index') }}" class="cart_img">
                        <img src="{{ asset('assets/images/product_5.png') }}" alt="product" class="img-fluid w-100">
                    </a>
                    <div class="cart_text">
                        <a class="cart_title" href="{{ route('shop.index') }}">Kid's Western Party Dress</a>
                        <p>$99.00</p>
                        <span><b>Color:</b> Black</span>
                        <span><b>Size:</b> L (Large)</span>
                    </div>
                    <a class="del_icon" href="#"><i class="fal fa-times"></i></a>
                </li>
            </ul>
            <h5>Sub Total <span>${{ number_format($cartTotal ?? 429, 2) }}</span></h5>
            <div class="minicart_btn_area">
                <a class="common_btn" href="{{ route('cart.index') }}">View Cart</a>
            </div>
        </div>
    </div>
</div>
