<?php

declare(strict_types=1);

namespace Webfloo\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Stage 1 media tooling: WebP variants for stored raster images via GD.
 * No trait, no DB — hosts (or future upload hooks) call convertToWebp()
 * after storing an original. Degrades to null when ext-gd is missing
 * (composer suggest) or the source is not a supported raster format.
 */
class MediaService
{
    private const WEBP_QUALITY = 80;

    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * Create (or refresh) the WebP variant next to the original.
     * Returns the variant path, or null when conversion is impossible.
     */
    public function convertToWebp(string $path, string $disk = 'public'): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, self::SUPPORTED_EXTENSIONS, true)) {
            return null;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return null;
        }

        $contents = $storage->get($path);

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        // Preserve PNG/GIF transparency in the WebP output.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        $encoded = imagewebp($image, null, self::WEBP_QUALITY);
        $webp = ob_get_clean();
        imagedestroy($image);

        if ($encoded === false || ! is_string($webp) || $webp === '') {
            return null;
        }

        $variant = $this->webpVariantPath($path);
        $storage->put($variant, $webp);

        return $variant;
    }

    /**
     * Naming contract for variants: {dir}/{basename}_webp.webp.
     */
    public function webpVariantPath(string $path): string
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $base = pathinfo($path, PATHINFO_FILENAME);
        $prefix = in_array($dir, ['.', ''], true) ? '' : $dir.'/';

        return "{$prefix}{$base}_webp.webp";
    }
}
