<?php

namespace Webfloo\Components\Layouts;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;
use Webfloo\Models\MenuItem;
use Webfloo\Services\ThemeService;

/**
 * Base layout for the public Blade frontend (x-webfloo-layout).
 *
 * Self-contained: injects theme CSS variables, SEO head block, header
 * navigation from MenuItem, favicon from settings and admin-authored
 * custom CSS/JS. Hosts override via the webfloo-views publish tag.
 */
class Frontend extends Component
{
    /**
     * @param  array{title?: string|null, description?: string|null, image?: string|null, no_index?: bool}  $seo
     */
    public function __construct(
        protected ThemeService $theme,
        public array $seo = [],
        public ?string $canonical = null,
        public string $seoType = 'website',
        public string $bodyClass = '',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.layouts.frontend');
    }

    public function baseTheme(): string
    {
        return $this->theme->getBaseTheme();
    }

    public function cssVariables(): string
    {
        return $this->theme->generateCssVariables();
    }

    public function customCss(): string
    {
        return $this->theme->getCustomCss();
    }

    public function customJs(): string
    {
        return $this->theme->getCustomJs();
    }

    public function faviconUrl(): ?string
    {
        $favicon = setting('favicon');

        return is_string($favicon) && $favicon !== '' ? Storage::url($favicon) : null;
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    public function headerNavigation(): array
    {
        return MenuItem::getForLocation(MenuItem::LOCATION_HEADER)
            ->map(fn (MenuItem $item): array => [
                'label' => (string) $item->label,
                'href' => (string) $item->href,
            ])
            ->values()
            ->all();
    }
}
