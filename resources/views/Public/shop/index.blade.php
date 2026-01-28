{{--
    Shop Page - Livewire Version
    No page reloads when changing categories/filters
--}}

@extends('layouts.public')

@section('title', 'Shop')

@section('content')

    {{-- Page Banner (Desktop only) --}}
    <div class="d-none d-md-block">
        @include('public.partials.page-banner', [
            'title' => 'Shop',
            'breadcrumbs' => ['Home' => route('home'), 'Shop' => '']
        ])
    </div>

    {{-- Category Slider (GrabFood Style) --}}
    @include('public.shop.partials.category-slider', [
        'sliderCategories' => $sliderCategories ?? collect(),
        'category' => $category ?? null
    ])


    {{-- Livewire Shop Component --}}
    <livewire:public.shop-products />

@endsection
