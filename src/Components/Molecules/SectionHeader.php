<?php

namespace Webfloo\Components\Molecules;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SectionHeader extends Component
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $description = null,
        public string $align = 'center',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.molecules.section-header');
    }

    public function alignmentClasses(): string
    {
        return match ($this->align) {
            'left' => 'text-left',
            'right' => 'text-right',
            default => 'text-center mx-auto',
        };
    }
}
