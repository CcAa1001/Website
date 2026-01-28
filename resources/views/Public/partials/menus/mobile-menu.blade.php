{{-- Mobile Menu --}}
<div class="mobile_menu_area">
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fal fa-times"></i>
        </button>
        <div class="offcanvas-body">
            {{-- Language & Currency Selectors --}}
            <ul class="mobile_currency">
                <li>
                    <select class="select_js language">
                        <option>English</option>
                        <option>Indonesian</option>
                        <option>Chinese</option>
                    </select>
                </li>

            </ul>


            {{-- Mobile Search --}}
            <form class="mobile_menu_search" action="{{ route('shop.search') }}" method="GET">
                <input type="text" name="q" placeholder="Search" value="{{ request('q') }}">
                <button type="submit"><i class="far fa-search"></i></button>
            </form>

            {{-- Mobile Menu Tabs --}}
            <div class="mobile_menu_item_area">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" 
                            data-bs-target="#pills-home" type="button" role="tab" 
                            aria-controls="pills-home" aria-selected="true">Categories</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" 
                            data-bs-target="#pills-profile" type="button" role="tab" 
                            aria-controls="pills-profile" aria-selected="false">Menu</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    {{-- Categories Tab --}}
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" 
                        aria-labelledby="pills-home-tab" tabindex="0">
                        <ul class="mobile-menu-categories main_mobile_menu">
                            @include('partials.menus.mobile-menu-category-list')
                        </ul>
                    </div>
                    
                    {{-- Menu Tab --}}
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel"
                        aria-labelledby="pills-profile-tab" tabindex="0">
                        <ul class="main_mobile_menu">
                            @include('partials.menus.mobile-menu-item-list')
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
