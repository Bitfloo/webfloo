<?php

declare(strict_types=1);

namespace Webfloo\Console\Commands;

use Illuminate\Console\Command;
use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Models\Project;

class GenerateSitemap extends Command
{
    /** @var string */
    protected $signature = 'sitemap:generate';

    /** @var string */
    protected $description = 'Generate sitemap.xml with PL and EN URLs';

    private const LOCALES_PER_URL = 2;

    private string $baseUrl = '';

    public function handle(): int
    {
        $this->baseUrl = is_string($url = config('app.url')) ? rtrim($url, '/') : 'http://localhost';

        $path = public_path('sitemap.xml');
        $handle = fopen($path, 'w');

        if ($handle === false) {
            $this->error("Cannot open {$path} for writing");

            return self::FAILURE;
        }

        fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
        fwrite($handle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n");
        fwrite($handle, '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n");

        $urlCount = 0;

        $this->writeEntry($handle, '/', '1.0', 'weekly', null);
        $this->writeEntry($handle, '/portfolio', '0.8', 'weekly', null);
        $urlCount += self::LOCALES_PER_URL * 2;

        foreach (Project::active()->ordered()->cursor() as $project) {
            $this->writeEntry(
                $handle,
                '/portfolio/'.$project->slug,
                '0.7',
                'monthly',
                $project->updated_at->toW3cString()
            );
            $urlCount += self::LOCALES_PER_URL;
        }

        $postsQuery = Post::query()
            ->published()
            ->where('no_index', false)
            ->orderByDesc('published_at');

        foreach ($postsQuery->cursor() as $post) {
            $this->writeEntry(
                $handle,
                '/blog/'.$post->slug,
                '0.6',
                'monthly',
                $post->updated_at->toW3cString()
            );
            $urlCount += self::LOCALES_PER_URL;
        }

        $pagesQuery = Page::published()
            ->withParentChain()
            ->ordered();

        foreach ($pagesQuery->cursor() as $page) {
            $this->writeEntry(
                $handle,
                $page->url,
                '0.5',
                'monthly',
                $page->updated_at->toW3cString()
            );
            $urlCount += self::LOCALES_PER_URL;
        }

        fwrite($handle, "</urlset>\n");
        fclose($handle);

        $this->info("Sitemap generated: {$urlCount} URLs written to {$path}");

        return self::SUCCESS;
    }

    /** @param  resource  $handle */
    private function writeEntry($handle, string $loc, string $priority, string $changefreq, ?string $lastmod): void
    {
        $plUrl = $this->baseUrl.$loc;
        $enUrl = $this->baseUrl.'/en'.$loc;

        foreach ([$plUrl, $enUrl] as $url) {
            $xml = "  <url>\n";
            $xml .= "    <loc>{$url}</loc>\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="pl" href="'.$plUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="en" href="'.$enUrl.'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.$plUrl.'" />'."\n";

            if ($lastmod !== null) {
                $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            }

            $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";

            fwrite($handle, $xml);
        }
    }
}
