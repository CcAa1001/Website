{{-- Header --}}
<header class="header_2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-2">
                <div class="header_logo_area">
                    <a href="{{ route('home') }}" class="header_logo">
                        <img src="{{ asset('assets/images/logo_png') }}" alt="{{ config('app.name', 'Zenis') }}" class="img-fluid w-100">
                    </a>
                    <div class="mobile_menu_icon d-block d-lg-none" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
                        <span class="mobile_menu_icon"><i class="far fa-stream menu_icon_bar"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-5 col-lg-5 d-none d-lg-block">
                {{-- Search form --}}
                <form action="{{ route('shop.search') }}" method="GET">
                    <select class="select_2" name="category">
                        <option value="">All Categories</option>
                        @foreach($headerCategories ?? [] as $category)
                            <option value="{{ $category->slug }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="input">
                        <input type="text" name="q" placeholder="Search your product..." value="{{ request('q') }}">
                        <button type="submit"><i class="far fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="col-xxl-4 col-xl-5 col-lg-5 d-none d-lg-flex">
                <div class="header_support_user d-flex flex-wrap">
                    <div class="header_support">
                        <span class="icon">
                            <i class="far fa-phone-alt"></i>
                        </span>
                        <h3>
                            Hotline:
                            <a href="tel:{{ config('site.phone', '+4027632824') }}">
                                <span>{{ config('site.phone_display', '+(62)xxx-xxxx-xxx') }}</span>
                            </a>
                        </h3>
                    </div>
                </div>
                <div class="topbar_right d-flex flex-wrap align-items-center justify-content-end">
                    <select class="select_js language">
                        <option>English</option>
                        <option>Chinese</option>
                        <option>Indonesian</option>
                    </select>
                    
                </div>
            </div>
        </div>
    </div>
</header>
