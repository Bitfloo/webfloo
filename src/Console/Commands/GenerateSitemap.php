<?php

declare(strict_types=1);

namespace Webfloo\Console\Commands;

use Illuminate\Console\Command;
use Webfloo\Sitemap\SitemapSource;

class GenerateSitemap extends Command
{
    /** @var string */
    protected $signature = 'sitemap:generate';

    /** @var string */
    protected $description = 'Generate sitemap.xml from configured sources (webfloo.sitemap)';

    private string $baseUrl = '';

    /** @var list<string> */
    private array $locales = [];

    public function handle(): int
    {
        $this->baseUrl = is_string($url = config('app.url')) ? rtrim($url, '/') : 'http://localhost';
        $this->locales = $this->configuredLocales();

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

        foreach ($this->entries() as $entry) {
            $this->writeEntry(
                $handle,
                $entry['loc'],
                $entry['priority'],
                $entry['changefreq'],
                $entry['lastmod'],
            );
            $urlCount += count($this->locales);
        }

        fwrite($handle, "</urlset>\n");
        fclose($handle);

        $this->info("Sitemap generated: {$urlCount} URLs written to {$path}");

        return self::SUCCESS;
    }

    /**
     * Static entries from config first, then every configured source.
     *
     * @return iterable<array{loc: string, priority: string, changefreq: string, lastmod: string|null}>
     */
    private function entries(): iterable
    {
        $statics = config('webfloo.sitemap.static_urls', []);

        foreach (is_array($statics) ? $statics : [] as $static) {
            if (! is_array($static) || ! is_string($static['loc'] ?? null)) {
                continue;
            }

            yield [
                'loc' => $static['loc'],
                'priority' => is_string($static['priority'] ?? null) ? $static['priority'] : '0.5',
                'changefreq' => is_string($static['changefreq'] ?? null) ? $static['changefreq'] : 'monthly',
                'lastmod' => null,
            ];
        }

        $providers = config('webfloo.sitemap.providers', []);

        foreach (is_array($providers) ? $providers : [] as $class) {
            if (! is_string($class) || ! class_exists($class)) {
                continue;
            }

            $source = app($class);

            if (! $source instanceof SitemapSource) {
                continue;
            }

            yield from $source->urls();
        }
    }

    /**
     * @return list<string>
     */
    private function configuredLocales(): array
    {
        $locales = config('webfloo.sitemap.locales', ['pl', 'en']);
        $locales = is_array($locales) ? array_values(array_filter($locales, 'is_string')) : [];

        return $locales === [] ? ['pl'] : $locales;
    }

    /** @param  resource  $handle */
    private function writeEntry($handle, string $loc, string $priority, string $changefreq, ?string $lastmod): void
    {
        // First locale serves unprefixed (and as x-default), the rest under
        // /{locale}. With a single locale no alternates are emitted at all.
        $localeUrls = [];
        foreach ($this->locales as $index => $locale) {
            $localeUrls[$locale] = $index === 0 ? $this->baseUrl.$loc : $this->baseUrl.'/'.$locale.$loc;
        }

        $alternates = '';
        if (count($localeUrls) > 1) {
            foreach ($localeUrls as $locale => $url) {
                $alternates .= '    <xhtml:link rel="alternate" hreflang="'.$locale.'" href="'.$url.'" />'."\n";
            }
            $default = reset($localeUrls);
            $alternates .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.$default.'" />'."\n";
        }

        foreach ($localeUrls as $url) {
            $xml = "  <url>\n";
            $xml .= "    <loc>{$url}</loc>\n";
            $xml .= $alternates;

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
