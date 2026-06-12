<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Services;

use Illuminate\Support\Facades\Storage;
use Webfloo\Services\MediaService;
use Webfloo\Tests\TestCase;

class MediaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('ext-gd with WebP support is not available (see composer suggest).');
        }

        Storage::fake('public');
    }

    private function storeImage(string $path, string $format): void
    {
        $image = imagecreatetruecolor(10, 10);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, 200, 50, 50));

        ob_start();
        $format === 'png' ? imagepng($image) : imagejpeg($image);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($path, $bytes);
    }

    public function test_converts_png_to_webp_variant_alongside_original(): void
    {
        $this->storeImage('images/photo.png', 'png');

        $variant = app(MediaService::class)->convertToWebp('images/photo.png');

        $this->assertSame('images/photo_webp.webp', $variant);
        Storage::disk('public')->assertExists('images/photo_webp.webp');

        $bytes = Storage::disk('public')->get('images/photo_webp.webp');
        $this->assertIsString($bytes);
        $this->assertStringStartsWith('RIFF', $bytes);
        $this->assertSame('WEBP', substr($bytes, 8, 4));
    }

    public function test_converts_jpeg_in_root_directory(): void
    {
        $this->storeImage('cover.jpg', 'jpeg');

        $variant = app(MediaService::class)->convertToWebp('cover.jpg');

        $this->assertSame('cover_webp.webp', $variant);
        Storage::disk('public')->assertExists('cover_webp.webp');
    }

    public function test_returns_null_for_missing_file(): void
    {
        $this->assertNull(app(MediaService::class)->convertToWebp('images/nope.png'));
    }

    public function test_returns_null_for_unsupported_extension(): void
    {
        Storage::disk('public')->put('images/vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->assertNull(app(MediaService::class)->convertToWebp('images/vector.svg'));
    }

    public function test_conversion_is_idempotent(): void
    {
        $this->storeImage('images/photo.png', 'png');

        $service = app(MediaService::class);

        $this->assertSame($service->convertToWebp('images/photo.png'), $service->convertToWebp('images/photo.png'));
    }

    public function test_webp_variant_path_is_the_naming_contract(): void
    {
        $service = app(MediaService::class);

        $this->assertSame('images/photo_webp.webp', $service->webpVariantPath('images/photo.png'));
        $this->assertSame('cover_webp.webp', $service->webpVariantPath('cover.jpg'));
        $this->assertSame('a/b/img_webp.webp', $service->webpVariantPath('a/b/img.jpeg'));
    }
}
