<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_images';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'original_path',
        'thumbnail_path',
        'medium_path',
        'large_path',
        'webp_path',
        'alt_text',
        'caption',
        'sort_order',
        'is_primary',
        'original_filename',
        'file_size',
        'mime_type',
        'width',
        'height',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the full URL for original image
     */
    public function getOriginalUrlAttribute(): string
    {
        return $this->getImageUrl($this->original_path);
    }

    /**
     * Get the full URL for thumbnail
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->getImageUrl($this->thumbnail_path ?? $this->original_path);
    }

    /**
     * Get the full URL for medium image
     */
    public function getMediumUrlAttribute(): string
    {
        return $this->getImageUrl($this->medium_path ?? $this->original_path);
    }

    /**
     * Get the full URL for large image
     */
    public function getLargeUrlAttribute(): string
    {
        return $this->getImageUrl($this->large_path ?? $this->original_path);
    }

    /**
     * Get the full URL for WebP version
     */
    public function getWebpUrlAttribute(): ?string
    {
        if (!$this->webp_path) {
            return null;
        }
        return $this->getImageUrl($this->webp_path);
    }

    /**
     * Get image URL by size
     */
    public function getUrl(string $size = 'medium'): string
    {
        return match($size) {
            'thumbnail', 'thumb' => $this->thumbnail_url,
            'medium' => $this->medium_url,
            'large' => $this->large_url,
            'original' => $this->original_url,
            default => $this->medium_url,
        };
    }

    /**
     * Helper to get full image URL
     */
    private function getImageUrl(?string $path): string
    {
        if (!$path) {
            return ImageSetting::placeholderUrl($this->product->tenant_id ?? null);
        }

        // Check if it's already a full URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Check if file exists
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Return placeholder if file not found
        return ImageSetting::placeholderUrl($this->product->tenant_id ?? null);
    }

    /**
     * Get srcset for responsive images
     */
    public function getSrcsetAttribute(): string
    {
        $srcset = [];
        
        if ($this->thumbnail_path) {
            $srcset[] = $this->thumbnail_url . ' 150w';
        }
        if ($this->medium_path) {
            $srcset[] = $this->medium_url . ' 600w';
        }
        if ($this->large_path) {
            $srcset[] = $this->large_url . ' 1200w';
        }
        
        return implode(', ', $srcset);
    }

    /**
     * Get file size in human-readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to get only primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Set this image as primary (unset others)
     */
    public function setPrimary(): void
    {
        // Unset other primary images for this product
        self::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Delete image files from storage
     */
    public function deleteFiles(): void
    {
        $paths = array_filter([
            $this->original_path,
            $this->thumbnail_path,
            $this->medium_path,
            $this->large_path,
            $this->webp_path,
        ]);

        foreach ($paths as $path) {
            if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-delete files when model is deleted
        static::deleting(function ($image) {
            $image->deleteFiles();
        });
    }
}