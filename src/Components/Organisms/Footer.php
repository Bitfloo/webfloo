<?php

namespace Webfloo\Components\Organisms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Webfloo\Models\MenuItem;

class Footer extends Component
{
    /**
     * @param  array<int, array{title: string, links: array<int, array{label: string, href: string}>}>  $navigation
     * @param  array<int, array{icon: string, href: string, label: string}>  $social
     */
    public function __construct(
        public array $navigation = [],
        public array $social = [],
        public ?string $copyright = null,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.organisms.footer');
    }

    public function getCopyright(): string
    {
        $siteName = setting('site_name', 'Bitfloo');

        return $this->copyright ?? '© '.date('Y').' '.(is_string($siteName) ? $siteName : 'Bitfloo').'. Wszelkie prawa zastrzeżone.';
    }

    /**
     * @return array<int, array{icon: string, href: string, label: string}>
     */
    public function getSocialLinks(): array
    {
        if (! empty($this->social)) {
            return $this->social;
        }

        $links = [];
        $fb = setting('social_facebook');
        if (is_string($fb) && $fb !== '') {
            $links[] = ['icon' => 'facebook', 'href' => $fb, 'label' => 'Facebook'];
        }
        $li = setting('social_linkedin');
        if (is_string($li) && $li !== '') {
            $links[] = ['icon' => 'linkedin', 'href' => $li, 'label' => 'LinkedIn'];
        }
        $ig = setting('social_instagram');
        if (is_string($ig) && $ig !== '') {
            $links[] = ['icon' => 'instagram', 'href' => $ig, 'label' => 'Instagram'];
        }
        $gh = setting('social_github');
        if (is_string($gh) && $gh !== '') {
            $links[] = ['icon' => 'github', 'href' => $gh, 'label' => 'GitHub'];
        }

        return $links;
    }

    /**
     * Get footer navigation sections from database.
     *
     * @return array<int, array{title: string, links: array<int, array{label: string, href: string}>}>
     */
    public function getNavigation(): array
    {
        if (! empty($this->navigation)) {
            return $this->navigation;
        }

        $sections = [];
        $locationLabels = MenuItem::getLocationOptions();

        foreach (['footer_company', 'footer_services', 'footer_legal'] as $location) {
            $items = MenuItem::getForLocation($location);

            if ($items->isNotEmpty()) {
                /** @var array<int, array{label: string, href: string}> $links */
                $links = $items->map(fn (MenuItem $item): array => [
                    'label' => $item->label,
                    'href' => $item->href ?? '#',
                ])->values()->all();

                $sections[] = [
                    'title' => str_replace('Footer - ', '', $locationLabels[$location] ?? $location),
                    'links' => $links,
                ];
            }
        }

        return $sections;
    }
}
