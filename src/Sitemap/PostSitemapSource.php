<?php

declare(strict_types=1);

namespace Webfloo\Sitemap;

use Webfloo\Models\Post;
use Webfloo\Support\ModuleRegistry;

class PostSitemapSource implements SitemapSource
{
    public function urls(): iterable
    {
        if (! ModuleRegistry::isEnabled('blog')) {
            return;
        }

        $posts = Post::query()
            ->published()
            ->where('no_index', false)
            ->orderByDesc('published_at');

        foreach ($posts->cursor() as $post) {
            yield [
                'loc' => $post->url,
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $post->updated_at->toW3cString(),
            ];
        }
    }
}
