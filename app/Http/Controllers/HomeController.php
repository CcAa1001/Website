<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page.
     * Using static data for design/prototype purposes.
     */
    public function index(): View
    {
        // Banner Slides
        $bannerSlides = [
            [
                'image' => 'assets/images/slider_1.jpg',
                'subtitle' => 'New Arrivals of 2025',
                'title' => 'Where Fashion Meets Individuality',
                'link' => '#',
            ],
            [
                'image' => 'assets/images/slider_2.jpg',
                'subtitle' => 'Trending This Month',
                'title' => 'Make Your Fashion Look More Changing',
                'link' => '#',
            ],
            [
                'image' => 'assets/images/slider_3.jpg',
                'subtitle' => 'Best Selling of 2025',
                'title' => 'Discover Your Best Fitting Clothes',
                'link' => '#',
            ],
        ];

        // Features Section
        $features = [
            [
                'icon' => 'assets/images/feature-icon_1.svg',
                'title' => 'Return & Refund',
                'description' => 'Money back guarantee',
                'color' => 'purple',
            ],
            [
                'icon' => 'assets/images/feature-icon_3.svg',
                'title' => 'Quality Support',
                'description' => 'Always online 24/7',
                'color' => 'green',
            ],
            [
                'icon' => 'assets/images/feature-icon_2.svg',
                'title' => 'Secure Payment',
                'description' => '100% secure payment',
                'color' => 'orange',
            ],
            [
                'icon' => 'assets/images/feature-icon_4.svg',
                'title' => 'Daily Offers',
                'description' => '20% off by subscribing',
                'color' => '',
            ],
        ];

        // Static Categories (for design)
        $categories = collect([
            (object)['id' => 1, 'name' => "Men's Fashion", 'slug' => 'mens-fashion', 'icon' => 'assets/images/category_list_icon_1.png'],
            (object)['id' => 2, 'name' => "Women's Fashion", 'slug' => 'womens-fashion', 'icon' => 'assets/images/category_list_icon_2.png'],
            (object)['id' => 3, 'name' => "Kid's Fashion", 'slug' => 'kids-fashion', 'icon' => 'assets/images/category_list_icon_3.png'],
            (object)['id' => 4, 'name' => 'Denim Collection', 'slug' => 'denim', 'icon' => 'assets/images/category_list_icon_4.png'],
            (object)['id' => 5, 'name' => 'Western Wear', 'slug' => 'western-wear', 'icon' => 'assets/images/category_list_icon_5.png'],
            (object)['id' => 6, 'name' => 'Sport Wear', 'slug' => 'sport-wear', 'icon' => 'assets/images/category_list_icon_6.png'],
        ]);

        // Static Products (for design)
        $flashSaleProducts = collect([
            (object)[
                'id' => 1,
                'name' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-tshirt',
                'price' => 150000,
                'sale_price' => 99000,
                'image' => 'assets/images/product_1.png',
                'rating' => 4.5,
            ],
            (object)[
                'id' => 2,
                'name' => 'Slim Fit Denim Jeans',
                'slug' => 'slim-fit-denim-jeans',
                'price' => 350000,
                'sale_price' => 249000,
                'image' => 'assets/images/product_2.png',
                'rating' => 4.8,
            ],
            (object)[
                'id' => 3,
                'name' => 'Casual Sneakers',
                'slug' => 'casual-sneakers',
                'price' => 450000,
                'sale_price' => 299000,
                'image' => 'assets/images/product_3.png',
                'rating' => 4.2,
            ],
            (object)[
                'id' => 4,
                'name' => 'Summer Floral Dress',
                'slug' => 'summer-floral-dress',
                'price' => 275000,
                'sale_price' => 199000,
                'image' => 'assets/images/product_4.png',
                'rating' => 4.7,
            ],
        ]);

        $flashSaleEnds = now()->addDays(3);

        // Featured Products
        $featuredProducts = collect([
            (object)[
                'id' => 5,
                'name' => 'Premium Leather Jacket',
                'slug' => 'premium-leather-jacket',
                'price' => 850000,
                'sale_price' => null,
                'image' => 'assets/images/product_5.png',
                'rating' => 4.9,
            ],
            (object)[
                'id' => 6,
                'name' => 'Elegant Watch',
                'slug' => 'elegant-watch',
                'price' => 1250000,
                'sale_price' => null,
                'image' => 'assets/images/product_6.png',
                'rating' => 4.6,
            ],
            (object)[
                'id' => 7,
                'name' => 'Designer Handbag',
                'slug' => 'designer-handbag',
                'price' => 750000,
                'sale_price' => null,
                'image' => 'assets/images/product_7.png',
                'rating' => 4.4,
            ],
            (object)[
                'id' => 8,
                'name' => 'Running Shoes Pro',
                'slug' => 'running-shoes-pro',
                'price' => 550000,
                'sale_price' => null,
                'image' => 'assets/images/product_8.png',
                'rating' => 4.8,
            ],
        ]);

        // Latest Blog Posts
        $latestPosts = collect([
            (object)[
                'id' => 1,
                'title' => 'How to Plop Hair for Bouncy, Beautiful Curls',
                'slug' => 'how-to-plop-hair',
                'image' => 'assets/images/blog_img_1.png',
                'author' => (object)['name' => 'Adnan Alvi'],
                'comments_count' => 15,
                'created_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 2,
                'title' => 'Fast Fashion: How Clothes Are Linked to Climate Change',
                'slug' => 'fast-fashion-climate',
                'image' => 'assets/images/blog_img_2.png',
                'author' => (object)['name' => 'Hasib Sing'],
                'comments_count' => 42,
                'created_at' => now()->subDays(10),
            ],
            (object)[
                'id' => 3,
                'title' => 'Which Foundation Formula Is Right for Your Skin?',
                'slug' => 'foundation-formula-skin',
                'image' => 'assets/images/blog_img_3.png',
                'author' => (object)['name' => 'Smith John'],
                'comments_count' => 36,
                'created_at' => now()->subDays(15),
            ],
            (object)[
                'id' => 4,
                'title' => 'How To Choose The Right Sofa for Your Home',
                'slug' => 'choose-right-sofa',
                'image' => 'assets/images/blog_img_4.png',
                'author' => (object)['name' => 'John Doe'],
                'comments_count' => 15,
                'created_at' => now()->subDays(20),
            ],
        ]);

        return view('home', compact(
            'bannerSlides',
            'features',
            'categories',
            'flashSaleProducts',
            'flashSaleEnds',
            'featuredProducts',
            'latestPosts'
        ));
    }
}
