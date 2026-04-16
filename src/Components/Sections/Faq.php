<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Faq extends Component
{
    /**
     * @param  array<int, array{question: string, answer: string, icon?: string, subtitle?: string}>  $items
     */
    public function __construct(
        public ?string $title = 'Często zadawane pytania',
        public ?string $subtitle = null,
        public ?string $description = null,
        public array $items = [],
        public bool $allowMultiple = false,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.faq');
    }
}
