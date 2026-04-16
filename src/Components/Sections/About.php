<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class About extends Component
{
    /**
     * @param  array<int, array{value: string, label: string}>  $stats
     * @param  array<int, string>  $features
     * @param  array<int, array{name: string, logo: string, url?: string}>  $partners
     */
    public function __construct(
        public ?string $title = 'O nas',
        public ?string $subtitle = null,
        public ?string $description = null,
        public ?string $image = null,
        public array $stats = [],
        public array $features = [],
        public string $imagePosition = 'right',
        public bool $showAmbientBg = false,
        public string $ambientVariant = 'coalesce',
        public array $partners = [],
        public ?string $partnersTitle = 'Zaufali nam',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.about');
    }
}
