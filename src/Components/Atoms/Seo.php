<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * Renders the SEO head block (title, meta, Open Graph, canonical)
 * from a HasSeo::getSeoData() array. With no data it falls back to
 * site-wide defaults from settings.
 */
class Seo extends Component
{
    /**
     * @param  array{title?: string|null, description?: string|null, image?: string|null, no_index?: bool}  $data
     */
    public function __construct(
        public array $data = [],
        public ?string $siteName = null,
        public ?string $canonical = null,
        public string $type = 'website',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.seo');
    }

    public function siteName(): string
    {
        if (is_string($this->siteName) && $this->siteName !== '') {
            return $this->siteName;
        }

        $siteName = setting('site_name', config('app.name'));

        return is_string($siteName) && $siteName !== '' ? $siteName : 'Site';
    }

    public function fullTitle(): string
    {
        $siteName = $this->siteName();
        $title = $this->data['title'] ?? null;

        if (! is_string($title) || $title === '' || $title === $siteName) {
            return $siteName;
        }

        return "{$title} | {$siteName}";
    }

    public function description(): ?string
    {
        $description = $this->data['description'] ?? null;

        if (is_string($description) && $description !== '') {
            return $description;
        }

        $siteDescription = setting('site_description');

        return is_string($siteDescription) && $siteDescription !== '' ? $siteDescription : null;
    }

    public function robots(): string
    {
        return ($this->data['no_index'] ?? false) ? 'noindex,nofollow' : 'index,follow';
    }

    public function imageUrl(): ?string
    {
        $image = $this->data['image'] ?? null;

        if (! is_string($image) || $image === '') {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return url(Str::startsWith($image, '/') ? $image : Storage::url($image));
    }

    public function ogUrl(): string
    {
        return $this->canonical ?? url()->current();
    }
}
