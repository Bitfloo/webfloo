<?php

declare(strict_types=1);

namespace Webfloo\Sitemap;

use Webfloo\Models\Page;

class PageSitemapSource implements SitemapSource
{
    public function urls(): iterable
    {
        $pages = Page::published()
            ->where('no_index', false)
            ->withParentChain()
            ->ordered();

        foreach ($pages->cursor() as $page) {
            yield [
                'loc' => $page->url,
                'priority' => '0.5',
                'changefreq' => 'monthly',
                'lastmod' => $page->updated_at->toW3cString(),
            ];
        }
    }
}
