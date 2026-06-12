<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature;

use Illuminate\Support\Facades\File;
use Webfloo\Tests\TestCase;

class AssetsTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('vendor/webfloo'));

        parent::tearDown();
    }

    public function test_dist_assets_are_built_and_substantial(): void
    {
        $css = __DIR__.'/../../dist/webfloo.css';
        $js = __DIR__.'/../../dist/webfloo.js';

        $this->assertFileExists($css);
        $this->assertFileExists($js);
        $this->assertGreaterThan(50_000, (int) filesize($css), 'dist/webfloo.css looks truncated - rebuild with npm run build');
        $this->assertGreaterThan(10_000, (int) filesize($js), 'dist/webfloo.js looks truncated - rebuild with npm run build');
    }

    public function test_assets_publish_tag_copies_files_to_public_vendor(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'webfloo-assets'])->assertSuccessful();

        $this->assertFileExists(public_path('vendor/webfloo/webfloo.css'));
        $this->assertFileExists(public_path('vendor/webfloo/webfloo.js'));
    }
}
