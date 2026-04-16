<?php

namespace Webfloo\Components\Organisms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    /**
     * @param  array<int, array{label: string, href: string}>  $navigation
     */
    public function __construct(
        public array $navigation = [],
        public ?string $ctaText = null,
        public ?string $ctaHref = null,
        public bool $sticky = true,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.organisms.header');
    }
}
