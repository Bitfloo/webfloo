<?php

namespace Webfloo\Components\Molecules;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ServiceCard extends Component
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $icon = null,
        public ?string $href = null,
        public string $linkText = 'Dowiedz się więcej',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.molecules.service-card');
    }
}
