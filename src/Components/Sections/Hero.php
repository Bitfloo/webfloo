<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Hero extends Component
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $description = null,
        public ?string $ctaText = null,
        public ?string $ctaHref = null,
        public ?string $secondaryCtaText = null,
        public ?string $secondaryCtaHref = null,
        public ?string $image = null,
        public string $align = 'left',
        public bool $showDotMatrix = false,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.hero');
    }
}
