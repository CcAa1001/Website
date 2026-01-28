@props([
    'product',
    'size' => 'medium', // thumbnail, medium, large
    'class' => '',
    'alt' => null,
    'loading' => 'lazy', // lazy, eager
    'webp' => true, // use WebP with fallback
])

@php
    $image = $product->primaryImage ?? $product->images->first();
    $altText = $alt ?? $product->name;
    
    // Get appropriate image URLs
    $imageUrl = $image ? $image->getUrl($size) : \App\Models\ImageSetting::placeholderUrl($product->tenant_id);
    $webpUrl = ($webp && $image && $image->webp_path) ? $image->webp_url : null;
    
    // Srcset for responsive images
    $srcset = $image ? $image->srcset : null;
@endphp

@if($webpUrl)
    {{-- WebP with fallback --}}
    <picture>
        <source srcset="{{ $webpUrl }}" type="image/webp">
        <img src="{{ $imageUrl }}" 
             alt="{{ $altText }}"
             @if($srcset) srcset="{{ $srcset }}" @endif
             loading="{{ $loading }}"
             class="{{ $class }}"
             {{ $attributes }}>
    </picture>
@else
    {{-- Regular image --}}
    <img src="{{ $imageUrl }}" 
         alt="{{ $altText }}"
         @if($srcset) srcset="{{ $srcset }}" @endif
         loading="{{ $loading }}"
         class="{{ $class }}"
         {{ $attributes }}>
@endif

