<?php

declare(strict_types=1);

namespace Webfloo\Sitemap;

use Webfloo\Models\Project;
use Webfloo\Support\ModuleRegistry;

class ProjectSitemapSource implements SitemapSource
{
    public function urls(): iterable
    {
        if (! ModuleRegistry::isEnabled('portfolio')) {
            return;
        }

        foreach (Project::active()->ordered()->cursor() as $project) {
            yield [
                'loc' => $project->public_url,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $project->updated_at->toW3cString(),
            ];
        }
    }
}
