<?php

declare(strict_types=1);

namespace Webfloo\Console\Commands;

use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Models\Project;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /** @var string */
    protected $signature = 'sitemap:generate';

    /** @var string */
    protected $description = 'Generate sitemap.xml with PL and EN URLs';

    public function handle(): int
    {
        $baseUrl = is_string($url = config('app.url')) ? rtrim($url, '/') : 'http://localhost';

        $urls = [];

        // Static pages
        $urls[] = ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => '/portfolio', 'priority' => '0.8', 'changefreq' => 'weekly'];

        // Published projects
        $projects = Project::active()->ordered()->get();
        foreach ($projects as $project) {
            $urls[] = [
                'loc' => '/portfolio/'.$project->slug,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $project->updated_at->toW3cString(),
            ];
        }

        // Published blog posts (exclude no_index)
        $posts = Post::query()
            ->published()
            ->where('no_index', false)
            ->orderByDesc('published_at')
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => '/blog/'.$post->slug,
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $post->updated_at->toW3cString(),
            ];
        }

        // Published dynamic pages (eager-load parent chain for URL building, exclude no_index)
        $pages = Page::published()->withParentChain()->where('no_index', false)->ordered()->get();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $page->url,
                'priority' => '0.5',
                'changefreq' => 'monthly',
                'lastmod' => $page->updated_at->toW3cString(),
            ];
        }

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $entry) {
            $plUrl = $baseUrl.$entry['loc'];
            $enUrl = $baseUrl.'/en'.$entry['loc'];

            $xml .= "  <url>\n";
            $xml .= "    <loc>{$plUrl}</loc>\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="pl" href="'.$plUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="en" href="'.$enUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.$plUrl.'" />'."\n";

            if (isset($entry['lastmod'])) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }

            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";

            // EN variant
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$enUrl}</loc>\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="pl" href="'.$plUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="en" href="'.$enUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.$plUrl.'" />'."\n";

            if (isset($entry['lastmod'])) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }

            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        $path = public_path('sitemap.xml');
        file_put_contents($path, $xml);

        $urlCount = count($urls) * 2;
        $this->info("Sitemap generated: {$urlCount} URLs written to {$path}");

        return self::SUCCESS;
    }
}
