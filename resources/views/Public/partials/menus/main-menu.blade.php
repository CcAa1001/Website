{{-- Main Navigation Menu - Desktop Only --}}
<nav class="main_menu_2 main_menu d-none d-lg-block">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex flex-wrap">
                <div class="main_menu_area">
                    <div class="menu_category_area">
                        <a href="{{ route('home') }}" class="menu_logo d-none">
                            <img src="{{ asset('assets/images/logo_2.png') }}" alt="{{ config('app.name', 'Zenis') }}" class="img-fluid w-100">
                        </a>
                        <div class="menu_category_bar">
                            <p>
                                <span>
                                    <img src="{{ asset('assets/images/bar_icon_white.svg') }}" alt="category icon">
                                </span>
                                Browse Categories
                            </p>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <ul class="menu_cat_item">
                            @include('partials.menus.menu-category-list')
                        </ul>
                    </div>
                    <ul class="menu_item">
                        @include('partials.menus.menu-item-list')
                    </ul>
                    <ul class="menu_icon">
                        @include('partials.menus.menu-icons', ['hasAccountDropdown' => true])
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
