<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ImageSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'image_settings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'setting_key',
        'setting_value',
        'description',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get a setting value for current tenant
     */
    public static function get(string $key, $default = null, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        
        if (!$tenantId) {
            return $default;
        }

        $cacheKey = "image_setting_{$tenantId}_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($tenantId, $key, $default) {
            $setting = self::where('tenant_id', $tenantId)
                ->where('setting_key', $key)
                ->first();

            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * Set a setting value for current tenant
     */
    public static function set(string $key, $value, ?string $tenantId = null): void
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        
        if (!$tenantId) {
            return;
        }

        self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'setting_key' => $key,
            ],
            [
                'setting_value' => $value,
            ]
        );

        // Clear cache
        Cache::forget("image_setting_{$tenantId}_{$key}");
    }

    /**
     * Get all settings for a tenant as array
     */
    public static function getAll(?string $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        
        if (!$tenantId) {
            return [];
        }

        return self::where('tenant_id', $tenantId)
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }

    /**
     * Get default values
     */
    public static function defaults(): array
    {
        return [
            'product_placeholder_url' => '/assets/images/product-placeholder.png',
            'max_upload_size_mb' => 2,
            'allowed_formats' => 'jpg,jpeg,png,webp',
            'thumbnail_width' => 150,
            'thumbnail_height' => 150,
            'medium_width' => 600,
            'medium_height' => 600,
            'large_width' => 1200,
            'large_height' => 1200,
            'jpeg_quality' => 85,
            'webp_quality' => 80,
            'enable_webp' => true,
        ];
    }

    // ==========================================
    // SPECIFIC GETTERS
    // ==========================================

    public static function placeholderUrl(?string $tenantId = null): string
    {
        return self::get('product_placeholder_url', '/assets/images/product-placeholder.png', $tenantId);
    }

    public static function maxUploadSize(?string $tenantId = null): int
    {
        return (int) self::get('max_upload_size_mb', 2, $tenantId);
    }

    public static function allowedFormats(?string $tenantId = null): array
    {
        $formats = self::get('allowed_formats', 'jpg,jpeg,png,webp', $tenantId);
        return explode(',', $formats);
    }

    public static function thumbnailSize(?string $tenantId = null): array
    {
        return [
            'width' => (int) self::get('thumbnail_width', 150, $tenantId),
            'height' => (int) self::get('thumbnail_height', 150, $tenantId),
        ];
    }

    public static function mediumSize(?string $tenantId = null): array
    {
        return [
            'width' => (int) self::get('medium_width', 600, $tenantId),
            'height' => (int) self::get('medium_height', 600, $tenantId),
        ];
    }

    public static function largeSize(?string $tenantId = null): array
    {
        return [
            'width' => (int) self::get('large_width', 1200, $tenantId),
            'height' => (int) self::get('large_height', 1200, $tenantId),
        ];
    }

    public static function jpegQuality(?string $tenantId = null): int
    {
        return (int) self::get('jpeg_quality', 85, $tenantId);
    }

    public static function webpQuality(?string $tenantId = null): int
    {
        return (int) self::get('webp_quality', 80, $tenantId);
    }

    public static function webpEnabled(?string $tenantId = null): bool
    {
        return (bool) self::get('enable_webp', true, $tenantId);
    }
}