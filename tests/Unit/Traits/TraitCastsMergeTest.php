<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Traits;

use Webfloo\Models\Page;
use Webfloo\Tests\TestCase;

/**
 * Regression guard for the trait cast-merge invariant.
 *
 * HasSeo and Publishable inject casts by mutating the $casts property
 * (via initialize* hooks), while Page also defines a casts() method.
 * Eloquent's getCasts() must merge both sources. Page::casts() does NOT
 * declare no_index, so that key proves the property-injected cast survives
 * alongside the method-declared casts on the current framework version.
 */
class TraitCastsMergeTest extends TestCase
{
    public function test_haseo_property_cast_merges_into_get_casts(): void
    {
        $casts = (new Page)->getCasts();

        $this->assertArrayHasKey('no_index', $casts);
        $this->assertSame('boolean', $casts['no_index']);
    }

    public function test_method_and_trait_casts_coexist(): void
    {
        $casts = (new Page)->getCasts();

        // From Page::casts()
        $this->assertSame('array', $casts['content'] ?? null);
        $this->assertSame('integer', $casts['sort_order'] ?? null);
        // From Publishable + HasSeo property injection
        $this->assertSame('datetime', $casts['published_at'] ?? null);
        $this->assertSame('boolean', $casts['no_index'] ?? null);
    }
}
