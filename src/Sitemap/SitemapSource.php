<?php

declare(strict_types=1);

namespace Webfloo\Sitemap;

/**
 * A sitemap URL provider. Hosts register additional sources (or replace
 * the defaults) via config webfloo.sitemap.providers.
 */
interface SitemapSource
{
    /**
     * @return iterable<array{loc: string, priority: string, changefreq: string, lastmod: string|null}>
     */
    public function urls(): iterable;
}
