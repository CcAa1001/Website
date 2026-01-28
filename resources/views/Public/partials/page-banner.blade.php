{{--
    Page Banner / Breadcrumb Partial
    Displays page title and breadcrumb navigation
--}}

<section class="page_banner" style="background: url('{{ asset('assets/images/page-banner-bg.jpg') }}');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="banner_content text-center">
                    <h1>{{ $title }}</h1>
                    
                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            @foreach($breadcrumbs as $name => $url)
                                @if($url)
                                    <li class="breadcrumb-item">
                                        <a href="{{ $url }}">{{ $name }}</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $name }}
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

@pushOnce('styles')
<style>
.page_banner {
    padding: 60px 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
}

.page_banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}

.page_banner .container {
    position: relative;
    z-index: 1;
}

.page_banner h1 {
    color: #fff;
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 15px;
}

.page_banner .breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.page_banner .breadcrumb-item {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.page_banner .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.2s;
}

.page_banner .breadcrumb-item a:hover {
    color: #fff;
}

.page_banner .breadcrumb-item.active {
    color: #fff;
}

.page_banner .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.5);
    content: "›";
}

@media (max-width: 767px) {
    .page_banner {
        padding: 40px 0;
    }
    
    .page_banner h1 {
        font-size: 24px;
    }
}
</style>
@endPushOnce
