<!DOCTYPE html>
<html>
<head>
    <title>Component Test</title>
</head>
<body>
    <h1>Component Test</h1>
    
    <h2>Product Info:</h2>
    <pre>{{ print_r($product->toArray(), true) }}</pre>
    
    <h2>Product Images:</h2>
    <pre>{{ print_r($product->images->toArray(), true) }}</pre>
    
    <h2>Primary Image:</h2>
    <pre>{{ print_r($product->primaryImage?->toArray(), true) }}</pre>
    
    <h2>Accessors Test:</h2>
    <ul>
        <li>thumbnail: {{ $product->thumbnail }}</li>
        <li>medium_image: {{ $product->medium_image }}</li>
        <li>large_image: {{ $product->large_image }}</li>
        <li>has_images: {{ $product->has_images ? 'true' : 'false' }}</li>
        <li>image_count: {{ $product->image_count }}</li>
    </ul>
    
    <h2>Component Output:</h2>
    <x-product-image 
        :product="$product" 
        size="medium"
        class="img-fluid w-100"
        loading="lazy" 
    />
    
    <h2>Component Source:</h2>
    <pre>{{ file_get_contents(resource_path('views/components/product-image.blade.php')) }}</pre>
</body>
</html>