<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Contact extends Component
{
    public function __construct(
        public ?string $title = 'Skontaktuj się z nami',
        public ?string $subtitle = null,
        public ?string $description = null,
        public bool $showMap = false,
        public bool $showForm = true,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.contact');
    }
}
