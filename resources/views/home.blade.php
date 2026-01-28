{{--
    Home Page View
    This is a placeholder - replace with your actual home page content
--}}

@extends('layouts.public')

@section('title', 'Home')

@section('content')

    {{-- ========================= --}}
    {{-- BANNER / HERO SECTION --}}
    {{-- ========================= --}}
    <section class="banner_2">
        <div class="container">
            <div class="row">
                {{-- Category Sidebar - HIDDEN on mobile/tablet, visible on XXL only --}}
                <div class="col-xl-2 d-none d-xxl-block">
                    <ul class="menu_cat_item">
                        @foreach($categories ?? [] as $category)
                            <li>
                                <a href="{{ route('shop.index') }}">
                                    <span>
                                        <img src="{{ asset($category->icon) }}" alt="{{ $category->name }}">
                                    </span>
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                        <li class="all_category">
                            <a href="{{ route('shop.index') }}">View All Categories <i class="far fa-arrow-right"></i></a>
                        </li>
                    </ul>
                </div>

                {{-- Main Banner Slider --}}
                <div class="col-xxl-7 col-lg-8">
                    <div class="banner_content">
                        <div class="row banner_2_slider">
                            @foreach($bannerSlides ?? [] as $slide)
                                <div class="col-xl-12">
                                    <div class="banner_slider_2 wow fadeInUp" 
                                         style="background: url('{{ asset($slide['image']) }}');">
                                        <div class="banner_slider_2_text">
                                            <h3>{{ $slide['subtitle'] }}</h3>
                                            <h1>{{ $slide['title'] }}</h1>
                                            <a class="common_btn" href="{{ $slide['link'] }}">
                                                Browse Menu <i class="fas fa-long-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Side Banner Ad --}}
                <div class="col-xxl-3 col-lg-4 col-sm-12 col-md-12">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="banner_2_add wow fadeInUp" 
                                 style="background: url('{{ asset('assets/images/banner 2.jpeg') }}');">
                                <div class="text">
                                    <h4>Summer Offer</h4>
                                    <h2>Make Your Fashion Story Unique Every Day</h2>
                                    <a class="common_btn" href="{{ route('shop.index') }}">
                                        Browse Menu <i class="fas fa-long-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



     <!--============================
        FLASH SELL START
    ==============================-->
    <section class="flash_sell_2 flash_sell mt_95">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xxl-6 col-md-3 col-xl-4">
                    <div class="section_heading_2 section_heading">
                        <h3><span>Flash</span> Sale
                    </div>
                </div>
                <div class="col-xxl-6 col-md-9 col-xl-8">
                    <div class="d-flex flex-wrap justify-content-end">
                       
                        <div class="view_all_btn_area">
                            <a class="view_all_btn" href="flash-deals.php">View all</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt_25 flash_sell_2_slider">
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_1.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 75%</li>
                                <li class="new"> new</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">Full Sleeve Hoodie Jacket</a>
                            <p class="price">$40.00 <del>$48.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(20 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#DB4437"></li>
                                <li style="background:#638C34"></li>
                                <li style="background:#1C58F2"></li>
                                <li style="background:#ffa500"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_24.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 45%</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">Denim casual blazer for men</a>
                            <p class="price">$120.00 <del>$99.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(17 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#DB4437"></li>
                                <li style="background:#638C34"></li>
                                <li style="background:#ffa500"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_3.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 15%</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">Women's Western Party Dress</a>
                            <p class="price">$50.00 <del>$40.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(22 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#638C34"></li>
                                <li style="background:#1C58F2"></li>
                                <li style="background:#ffa500"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_26.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 75%</li>
                                <li class="new"> new</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">tops pant beautiful dress</a>
                            <p class="price">$75.00 <del>$69.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                <span>(58 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#DB4437"></li>
                                <li style="background:#638C34"></li>
                                <li style="background:#1C58F2"></li>
                                <li style="background:#ffa500"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_8.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 49%</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">Kid's Western Party Dress</a>
                            <p class="price">$49.00 <del>$39.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                <span>(44 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#DB4437"></li>
                                <li style="background:#638C34"></li>
                                <li style="background:#1C58F2"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-1-5 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="assets/images/product_19.png" alt="Product" class="img-fluid w-100">
                            <ul class="discount_list">
                                <li class="discount"> <b>-</b> 62%</li>
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title" href="shop-details.php">Men's premium formal shirt</a>
                            <p class="price">$41.00 <del>$59.00</del></p>
                            <p class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                <span>(98 reviews)</span>
                            </p>
                            <ul class="color">
                                <li class="active" style="background:#DB4437"></li>
                                <li style="background:#638C34"></li>
                                <li style="background:#1C58F2"></li>
                                <li style="background:#ffa500"></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ========================= --}}
    {{-- NEWSLETTER SUBSCRIPTION --}}
    {{-- ========================= --}}
    <section class="subscription_2 mt_50 xs_mt_60" style="background: url('{{ asset('assets/images/subscribe_2_bg.jpg') }}');">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-6 col-lg-8 wow fadeInUp">
                    <div class="subscription_2_text">
                        <h2>Get Up to <span>70%</span> Off Discount Coupon</h2>
                        <p>By subscribing to our newsletter</p>
                        <form action="#" method="POST">
                            @csrf
                            <input type="email" name="email" placeholder="Your email" required>
                            <button type="submit" class="common_btn">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
