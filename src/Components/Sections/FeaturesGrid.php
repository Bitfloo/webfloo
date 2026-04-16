<?php

declare(strict_types=1);

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FeaturesGrid extends Component
{
    /**
     * @param  array<int, array{icon?: string, title: string, description: string}>  $features
     */
    public function __construct(
        public string $title = 'Nasze rozwiązania',
        public ?string $subtitle = null,
        public ?string $description = null,
        public array $features = [],
        public ?string $ctaText = null,
        public ?string $ctaHref = null,
        public ?string $image = null,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.features-grid');
    }
}
