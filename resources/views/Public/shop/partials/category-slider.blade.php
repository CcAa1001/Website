{{--
    Category Slider Partial (Enhanced Design - Livewire Compatible)
    Modern, appealing design with smooth animations
--}}

@php
    $selectedCategory = $selectedCategory ?? null;
@endphp

@if($sliderCategories->count() > 0)
<section class="fp_category_slider_section">
    <div class="container">
        <div class="fp_category_slider_wrapper">
            {{-- Left Scroll Arrow --}}
            <button type="button" class="slider_arrow slider_arrow_left" onclick="scrollSlider(-200)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="fp_category_slider" id="categorySlider">
                
                {{-- "All" Category --}}
                <button type="button" 
                        wire:click="selectCategory(null)"
                        class="fp_category_item {{ !$selectedCategory ? 'active' : '' }}">
                    <div class="fp_cat_icon_box">
                        <div class="icon_inner">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <div class="pulse_ring"></div>
                    </div>
                    <span class="fp_cat_name">All</span>
                    <span class="cat_underline"></span>
                </button>

                @foreach($sliderCategories as $sliderCategory)
                    <button type="button"
                            wire:click="selectCategory('{{ $sliderCategory->slug }}')"
                            class="fp_category_item {{ $selectedCategory === $sliderCategory->slug ? 'active' : '' }}">
                        <div class="fp_cat_icon_box">
                            <div class="icon_inner">
                                @if($sliderCategory->image_url)
                                    <img src="{{ $sliderCategory->image }}" 
                                         alt="{{ $sliderCategory->name }}" 
                                         loading="lazy">
                                @else
                                    @switch($sliderCategory->slug)
                                        @case('makanan-utama')
                                            <i class="fas fa-utensils"></i>
                                            @break
                                        @case('minuman')
                                            <i class="fas fa-coffee"></i>
                                            @break
                                        @case('snack-appetizer')
                                            <i class="fas fa-cookie-bite"></i>
                                            @break
                                        @case('dessert')
                                            <i class="fas fa-ice-cream"></i>
                                            @break
                                        @case('paket-hemat')
                                            <i class="fas fa-box"></i>
                                            @break
                                        @case('nasi')
                                            <i class="fas fa-bowl-rice"></i>
                                            @break
                                        @case('mie')
                                            <i class="fas fa-bowl-food"></i>
                                            @break
                                        @case('ayam')
                                            <i class="fas fa-drumstick-bite"></i>
                                            @break
                                        @case('seafood')
                                            <i class="fas fa-fish"></i>
                                            @break
                                        @case('kopi')
                                            <i class="fas fa-mug-hot"></i>
                                            @break
                                        @case('teh')
                                            <i class="fas fa-mug-saucer"></i>
                                            @break
                                        @case('jus')
                                            <i class="fas fa-glass-water"></i>
                                            @break
                                        @case('smoothie')
                                            <i class="fas fa-blender"></i>
                                            @break
                                        @case('gorengan')
                                            <i class="fas fa-fire"></i>
                                            @break
                                        @case('dimsum')
                                            <i class="fas fa-bowl-food"></i>
                                            @break
                                        @case('es-krim')
                                            <i class="fas fa-ice-cream"></i>
                                            @break
                                        @case('kue')
                                            <i class="fas fa-cake-candles"></i>
                                            @break
                                        @default
                                            <i class="fas fa-tag"></i>
                                    @endswitch
                                @endif
                            </div>
                            <div class="pulse_ring"></div>
                        </div>
                        <span class="fp_cat_name">{{ Str::limit($sliderCategory->name, 12) }}</span>
                        <span class="cat_underline"></span>
                    </button>
                @endforeach

            </div>

            {{-- Right Scroll Arrow --}}
            <button type="button" class="slider_arrow slider_arrow_right" onclick="scrollSlider(200)">
                <i class="fas fa-chevron-right"></i>
            </button>

            {{-- Gradient Fades --}}
            <div class="slider_fade slider_fade_left"></div>
            <div class="slider_fade slider_fade_right"></div>
        </div>
    </div>
</section>
@endif

@push('styles')
<style>
/* ===========================
    CATEGORY SLIDER
   =========================== */

section.fp_category_slider_section {
    padding: 20px 0 15px;
    background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    position: relative;
    
}

.fp_category_slider_wrapper {
    position: relative;
    padding: 0 0px;
}

.fp_category_slider {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 10px 16px 15px;
}

.fp_category_slider::-webkit-scrollbar {
    display: none;
}

/* Scroll Arrows */
.slider_arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    transition: all 0.2s ease;
}

.slider_arrow:hover {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    transform: translateY(-50%) scale(1.1);
}

.slider_arrow_left { left: 0; }
.slider_arrow_right { right: 0; }

/* Gradient Fades */
.slider_fade {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 60px;
    pointer-events: none;
    z-index: 5;
}

.slider_fade_left {
    left: 35px;
    background: linear-gradient(90deg, rgba(250,250,250,1) 0%, rgba(250,250,250,0) 100%);
}

.slider_fade_right {
    right: 35px;
    background: linear-gradient(-90deg, rgba(250,250,250,1) 0%, rgba(250,250,250,0) 100%);
}

/* Category Item */
button.fp_category_item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 0 0 85px;
    width: 85px;
    padding: 5px;
    background: transparent;
    border: none;
    cursor: pointer;
    position: relative;
    transition: transform 0.2s ease;
}

button.fp_category_item:hover {
    transform: translateY(-3px);
}

button.fp_category_item:active {
    transform: scale(0.95);
}

/* Icon Box */
.fp_cat_icon_box {
    position: relative;
    width: 58px;
    height: 58px;
    margin-bottom: 8px;
    border : 5px solid !important;
    color : var(--primary-color, #ff6b6b) !important;
}

.icon_inner {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, #ffffff, #f0f0f0);
    border-radius: 16px;
    box-shadow: 
        4px 4px 10px rgba(0,0,0,0.08),
        -2px -2px 8px rgba(255,255,255,0.9);
    color: #666;
    font-size: 22px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.icon_inner img {
    width: 34px;
    height: 34px;
    object-fit: contain;
    border-radius: 8px;
}

/* Pulse Ring (hidden by default) */
.pulse_ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    border-radius: 16px;
    border: 2px solid var(--primary-color, #ff6b6b);
    opacity: 0;
    z-index: 1;
}

/* Hover State */
button.fp_category_item:hover .icon_inner {
    background: linear-gradient(145deg, #fff5f5, #ffe8e8);
    color: var(--primary-color, #ff6b6b);
    box-shadow: 
        4px 4px 15px rgba(255,107,107,0.15),
        -2px -2px 8px rgba(255,255,255,0.9);
}

/* Active State */
button.fp_category_item.active .icon_inner {
    background: linear-gradient(145deg, var(--primary-color, #ff6b6b), #ff5252);
    color: #fff;
    box-shadow: 
        0 6px 20px rgba(255,107,107,0.4),
        inset 0 1px 0 rgba(255,255,255,0.2);
}

button.fp_category_item.active .pulse_ring {
    animation: pulse_animation 2s ease-out infinite;
}

@keyframes pulse_animation {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.6;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 0;
    }
}

/* Category Name */
.fp_cat_name {
    font-size: 12px;
    font-weight: 500;
    color: #555;
    text-align: center;
    line-height: 1.3;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: all 0.2s ease;
}

button.fp_category_item:hover .fp_cat_name {
    color: var(--primary-color, #ff6b6b);
}

button.fp_category_item.active .fp_cat_name {
    color: var(--primary-color, #ff6b6b);
    font-weight: 600;
}

/* Underline */
.cat_underline {
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color, #ff6b6b), #ff8a8a);
    border-radius: 2px;
    margin-top: 5px;
    transition: width 0.3s ease;
}

button.fp_category_item.active .cat_underline {
    width: 30px;
}

button.fp_category_item:hover .cat_underline {
    width: 20px;
}

/* ===========================
   RESPONSIVE BREAKPOINTS
   =========================== */

/* Extra Small Mobile (< 360px) */
@media (max-width: 359.98px) {
    .fp_category_slider_wrapper {
        padding: 0 5px;
    }
    
    .slider_arrow {
        display: none;
    }
    
    .slider_fade {
        width: 20px;
    }
    
    .slider_fade_left { left: 0; }
    .slider_fade_right { right: 0; }
    
    button.fp_category_item {
        flex: 0 0 70px !important;
        width: 70px !important;
    }
    
    .fp_cat_icon_box {
        width: 50px !important;
        height: 50px !important;
    }
    
    .icon_inner {
        border-radius: 12px !important;
        font-size: 20px !important;
    }
    
    .icon_inner img {
        width: 32px !important;
        height: 32px !important;
    }
    
    .fp_cat_name {
        font-size: 10px !important;
    }
    
    .cat_underline {
        height: 2px !important;
    }
}

/* Small Mobile (360px - 413px) */
@media (min-width: 360px) and (max-width: 413.98px) {
    .fp_category_slider_wrapper {
        padding: 0px;
    }
    
    .slider_arrow {
        display: none;
    }
    
    .slider_fade {
        width: 25px;
    }
    
    .slider_fade_left { left: 3px; }
    .slider_fade_right { right: 3px; }
    
    button.fp_category_item {
        flex: 0 0 78px !important;
        width: 78px !important;
    }
    
    .fp_cat_icon_box {
        width: 100px !important;
        height: 100px !important;
    }
    
    .icon_inner {
        border-radius: 14px !important;
        font-size: 22px !important;
    }
    
    .icon_inner img {
        width: 36px !important;
        height: 36px !important;
    }
    
    .fp_cat_name {
        font-size: 10px !important;
    }
    .fp_category_slider {
        gap: 50px!important;
    }
}

/* Medium Mobile (414px - 575px) - iPhone Plus, etc */
@media (min-width: 414px) and (max-width: 575.98px) {
    .fp_category_slider_wrapper {
        padding: 0 10px;
    }
    
    .slider_arrow {
        display: none;
    }
    
    .slider_fade {
        width: 30px;
    }
    
    .slider_fade_left { left: 5px; }
    .slider_fade_right { right: 5px; }
    
    button.fp_category_item {
        flex: 0 0 85px !important;
        width: 85px !important;
    }
    
    .fp_cat_icon_box {
        width: 100px !important;
        height: 100px !important;
    }
    
    .icon_inner {
        border-radius: 16px !important;
        font-size: 24px !important;
    }
    
    .icon_inner img {
        width: 40px !important;
        height: 40px !important;
    }
    
    .fp_cat_name {
        font-size: 11px !important;
    }
    .fp_category_slider {
        gap: 50px!important;
    }
}

/* Large Mobile / Small Tablet (576px - 767px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .fp_category_slider_wrapper {
        padding: 0 15px;
    }
    
    .slider_arrow {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
    
    .slider_arrow_left { left: -5px; }
    .slider_arrow_right { right: -5px; }
    
    .slider_fade {
        width: 40px;
    }
    
    .slider_fade_left { left: 10px; }
    .slider_fade_right { right: 10px; }
    
    button.fp_category_item {
        flex: 0 0 90px !important;
        width: 90px !important;
    }
    
    .fp_cat_icon_box {
        width: 65px !important;
        height: 65px !important;
    }
    
    .icon_inner {
        border-radius: 16px !important;
        font-size: 26px !important;
    }
    
    .icon_inner img {
        width: 42px !important;
        height: 42px !important;
    }
    
    .fp_cat_name {
        font-size: 12px !important;
    }
}

/* Tablet Portrait (768px - 991px) */
@media (min-width: 768px) and (max-width: 991.98px) {
    section.fp_category_slider_section {
        padding: 22px 0 18px;
    }
    
    .fp_category_slider_wrapper {
        padding: 0 45px;
    }
    
    .fp_category_slider {
        gap: 10px;
    }
    
    .slider_arrow {
        width: 34px;
        height: 34px;
        font-size: 13px;
    }
    
    .slider_fade {
        width: 50px;
    }
    
    .slider_fade_left { left: 40px; }
    .slider_fade_right { right: 40px; }
    
    button.fp_category_item {
        flex: 0 0 95px !important;
        width: 95px !important;
    }
    
    .fp_cat_icon_box {
        width: 68px !important;
        height: 68px !important;
    }
    
    .icon_inner {
        border-radius: 18px !important;
        font-size: 28px !important;
    }
    
    .icon_inner img {
        width: 44px !important;
        height: 44px !important;
    }
    
    .fp_cat_name {
        font-size: 12px !important;
    }
    
    .cat_underline {
        margin-top: 6px;
    }
}

/* Desktop (992px - 1199px) */
@media (min-width: 992px) and (max-width: 1199.98px) {
    section.fp_category_slider_section {
        padding: 25px 0 20px;
    }
    
    .fp_category_slider_wrapper {
        padding: 0 50px;
    }
    
    .fp_category_slider {
        justify-content: center;
        gap: 12px;
    }
    
    .slider_arrow {
        width: 36px;
        height: 36px;
    }
    
    .slider_fade {
        width: 60px;
    }
    
    button.fp_category_item {
        flex: 0 0 100px !important;
        width: 100px !important;
    }
    
    .fp_cat_icon_box {
        width: 70px !important;
        height: 70px !important;
    }
    
    .icon_inner {
        border-radius: 18px !important;
        font-size: 28px !important;
    }
    
    .icon_inner img {
        width: 46px !important;
        height: 46px !important;
    }
    
    .fp_cat_name {
        font-size: 13px !important;
    }
}

/* Large Desktop (1200px - 1399px) */
@media (min-width: 1200px) and (max-width: 1399.98px) {
    section.fp_category_slider_section {
        padding: 28px 0 22px;
    }
    
    .fp_category_slider {
        justify-content: center;
        gap: 15px;
    }
    
    button.fp_category_item {
        flex: 0 0 110px !important;
        width: 110px !important;
    }
    
    .fp_cat_icon_box {
        width: 75px !important;
        height: 75px !important;
    }
    
    .icon_inner {
        border-radius: 20px !important;
        font-size: 30px !important;
    }
    
    .icon_inner img {
        width: 50px !important;
        height: 50px !important;
    }
    
    .fp_cat_name {
        font-size: 13px !important;
    }
}

/* Extra Large Desktop (1400px+) */
@media (min-width: 1400px) {
    section.fp_category_slider_section {
        padding: 30px 0 25px;
    }
    
    .fp_category_slider {
        justify-content: center;
        gap: 18px;
    }
    
    button.fp_category_item {
        flex: 0 0 120px !important;
        width: 120px !important;
    }
    
    .fp_cat_icon_box {
        width: 80px !important;
        height: 80px !important;
    }
    
    .icon_inner {
        border-radius: 22px !important;
        font-size: 32px !important;
    }
    
    .icon_inner img {
        width: 54px !important;
        height: 54px !important;
    }
    
    .fp_cat_name {
        font-size: 14px !important;
    }
    
    .cat_underline {
        height: 4px;
        margin-top: 8px;
    }
    
    button.fp_category_item.active .cat_underline {
        width: 40px;
    }
}

</style>
@endpush

@push('scripts')
<script>
function scrollSlider(amount) {
    const slider = document.getElementById('categorySlider');
    if (slider) {
        slider.scrollBy({ left: amount, behavior: 'smooth' });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    scrollActiveCategory();
    updateArrowVisibility();
    
    const slider = document.getElementById('categorySlider');
    if (slider) {
        slider.addEventListener('scroll', updateArrowVisibility);
    }
});

document.addEventListener('livewire:navigated', function() {
    scrollActiveCategory();
});

function scrollActiveCategory() {
    const slider = document.getElementById('categorySlider');
    if (!slider) return;
    
    const activeItem = slider.querySelector('.fp_category_item.active');
    if (activeItem) {
        setTimeout(() => {
            const sliderRect = slider.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            const scrollLeft = itemRect.left - sliderRect.left - (sliderRect.width / 2) + (itemRect.width / 2);
            slider.scrollBy({ left: scrollLeft, behavior: 'smooth' });
        }, 100);
    }
}

function updateArrowVisibility() {
    const slider = document.getElementById('categorySlider');
    const leftArrow = document.querySelector('.slider_arrow_left');
    const rightArrow = document.querySelector('.slider_arrow_right');
    
    if (!slider || !leftArrow || !rightArrow) return;
    
    leftArrow.style.opacity = slider.scrollLeft > 10 ? '1' : '0.3';
    rightArrow.style.opacity = 
        slider.scrollLeft < (slider.scrollWidth - slider.clientWidth - 10) ? '1' : '0.3';
}
</script>
@endpush