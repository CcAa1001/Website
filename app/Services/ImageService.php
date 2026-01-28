<?php

namespace App\Services;

use App\Models\ProductImage;
use App\Models\ImageSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // or Imagick\Driver

class ImageService
{
    /**
     * Get Image Manager instance (v3)
     */
    private static function getImageManager(): ImageManager
    {
        // Use GD driver (or change to Imagick\Driver if you have Imagick)
        return new ImageManager(new Driver());
    }

    /**
     * Upload and process a product image
     */
    public static function uploadProductImage(
        string $productId,
        UploadedFile $file,
        array $options = []
    ): ProductImage {
        // Get tenant ID from product
        $product = \App\Models\Product::find($productId);
        $tenantId = $product->tenant_id;

        // Validate file
        self::validateUpload($file, $tenantId);

        // Generate unique filename
        $filename = self::generateFilename($file);
        $directory = "products/{$productId}";

        // Store original
        $originalPath = $file->storeAs($directory, $filename, 'public');

        // Get image dimensions
        $imageInfo = getimagesize($file->getRealPath());
        
        // Create product image record
        $productImage = ProductImage::create([
            'product_id' => $productId,
            'original_path' => $originalPath,
            'alt_text' => $options['alt_text'] ?? $product->name,
            'caption' => $options['caption'] ?? null,
            'sort_order' => $options['sort_order'] ?? self::getNextSortOrder($productId),
            'is_primary' => $options['is_primary'] ?? false,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
        ]);

        // Generate variants asynchronously (or immediately)
        self::generateVariants($productImage, $tenantId);

        // Set as primary if requested or if it's the first image
        if ($options['is_primary'] ?? false) {
            $productImage->setPrimary();
        } elseif (ProductImage::where('product_id', $productId)->count() === 1) {
            $productImage->setPrimary();
        }

        return $productImage;
    }

    /**
     * Generate image variants (thumbnail, medium, large, webp)
     * Updated for Intervention Image v3
     */
    public static function generateVariants(ProductImage $productImage, ?string $tenantId = null): void
    {
        $originalPath = Storage::disk('public')->path($productImage->original_path);
        
        if (!file_exists($originalPath)) {
            return;
        }

        // Get settings
        $thumbSize = ImageSetting::thumbnailSize($tenantId);
        $mediumSize = ImageSetting::mediumSize($tenantId);
        $largeSize = ImageSetting::largeSize($tenantId);
        $jpegQuality = ImageSetting::jpegQuality($tenantId);
        $webpQuality = ImageSetting::webpQuality($tenantId);
        $enableWebp = ImageSetting::webpEnabled($tenantId);

        $directory = dirname($productImage->original_path);

        try {
            $manager = self::getImageManager();

            // Generate thumbnail
            $thumbnailPath = self::resizeImage(
                $manager,
                $originalPath,
                $directory,
                'thumb',
                $thumbSize['width'],
                $thumbSize['height'],
                $jpegQuality
            );

            // Generate medium
            $mediumPath = self::resizeImage(
                $manager,
                $originalPath,
                $directory,
                'medium',
                $mediumSize['width'],
                $mediumSize['height'],
                $jpegQuality
            );

            // Generate large
            $largePath = self::resizeImage(
                $manager,
                $originalPath,
                $directory,
                'large',
                $largeSize['width'],
                $largeSize['height'],
                $jpegQuality
            );

            // Generate WebP
            $webpPath = null;
            if ($enableWebp) {
                $webpPath = self::convertToWebp($manager, $originalPath, $directory, $webpQuality);
            }

            // Update product image record
            $productImage->update([
                'thumbnail_path' => $thumbnailPath,
                'medium_path' => $mediumPath,
                'large_path' => $largePath,
                'webp_path' => $webpPath,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate image variants: ' . $e->getMessage());
        }
    }

    /**
     * Resize image to specific dimensions
     * Updated for Intervention Image v3 syntax
     */
    private static function resizeImage(
        ImageManager $manager,
        string $sourcePath,
        string $directory,
        string $sizeName,
        int $width,
        int $height,
        int $quality
    ): string {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $newFilename = "{$filename}_{$sizeName}.{$extension}";
        $newPath = "{$directory}/{$newFilename}";

        // V3 SYNTAX: Read image
        $image = $manager->read($sourcePath);
        
        // V3 SYNTAX: Scale down (maintains aspect ratio, doesn't upscale)
        $image->scale(width: $width, height: $height);

        // V3 SYNTAX: Encode based on extension
        $fullPath = Storage::disk('public')->path($newPath);
        
        if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
            $encoded = $image->toJpeg(quality: $quality);
        } elseif (strtolower($extension) === 'png') {
            $encoded = $image->toPng();
        } else {
            $encoded = $image->toJpeg(quality: $quality);
        }

        // Save to file
        file_put_contents($fullPath, $encoded);

        return $newPath;
    }

    /**
     * Convert image to WebP format
     * Updated for Intervention Image v3 syntax
     */
    private static function convertToWebp(
        ImageManager $manager,
        string $sourcePath,
        string $directory,
        int $quality
    ): string {
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $newFilename = "{$filename}.webp";
        $newPath = "{$directory}/{$newFilename}";

        // V3 SYNTAX: Read and convert to WebP
        $image = $manager->read($sourcePath);
        $encoded = $image->toWebp(quality: $quality);

        // Save to file
        $fullPath = Storage::disk('public')->path($newPath);
        file_put_contents($fullPath, $encoded);

        return $newPath;
    }

    /**
     * Validate file upload
     */
    private static function validateUpload(UploadedFile $file, ?string $tenantId = null): void
    {
        // Check file size
        $maxSize = ImageSetting::maxUploadSize($tenantId) * 1024; // Convert MB to KB
        if ($file->getSize() > ($maxSize * 1024)) { // Convert to bytes
            throw new \Exception("File size exceeds maximum allowed ({$maxSize}MB)");
        }

        // Check file type
        $allowedFormats = ImageSetting::allowedFormats($tenantId);
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedFormats)) {
            throw new \Exception("File type not allowed. Allowed: " . implode(', ', $allowedFormats));
        }

        // Validate it's actually an image
        if (!getimagesize($file->getRealPath())) {
            throw new \Exception("File is not a valid image");
        }
    }

    /**
     * Generate unique filename
     */
    private static function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::random(20) . '_' . time() . '.' . $extension;
    }

    /**
     * Get next sort order for product images
     */
    private static function getNextSortOrder(string $productId): int
    {
        $maxOrder = ProductImage::where('product_id', $productId)->max('sort_order');
        return ($maxOrder ?? -1) + 1;
    }

    /**
     * Delete product image and all its variants
     */
    public static function deleteProductImage(ProductImage $productImage): void
    {
        $productImage->deleteFiles();
        $productImage->delete();
    }

    /**
     * Reorder product images
     */
    public static function reorderImages(string $productId, array $imageIds): void
    {
        foreach ($imageIds as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $productId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Regenerate variants for existing image
     */
    public static function regenerateVariants(ProductImage $productImage): void
    {
        $product = $productImage->product;
        self::generateVariants($productImage, $product->tenant_id);
    }

    /**
     * Crop and resize image (useful for thumbnails)
     * V3 syntax
     */
    public static function cropAndResize(
        string $sourcePath,
        string $outputPath,
        int $width,
        int $height,
        int $quality = 85
    ): void {
        $manager = self::getImageManager();
        $image = $manager->read($sourcePath);
        
        // Cover: Scale and crop to exact dimensions
        $image->cover($width, $height);
        
        // Encode and save
        $encoded = $image->toJpeg(quality: $quality);
        file_put_contents($outputPath, $encoded);
    }

    /**
     * Add watermark to image
     * V3 syntax
     */
    public static function addWatermark(
        string $sourcePath,
        string $watermarkPath,
        string $position = 'bottom-right',
        int $opacity = 50
    ): void {
        $manager = self::getImageManager();
        $image = $manager->read($sourcePath);
        $watermark = $manager->read($watermarkPath);
        
        // Make watermark semi-transparent
        $watermark->opacity($opacity);
        
        // Place watermark (position: top-left, top-right, bottom-left, bottom-right, center)
        $image->place($watermark, position: $position);
        
        // Save
        $encoded = $image->toJpeg(quality: 90);
        file_put_contents($sourcePath, $encoded);
    }
}