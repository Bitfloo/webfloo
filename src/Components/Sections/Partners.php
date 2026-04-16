<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Partners extends Component
{
    /**
     * @param  array<int, array{name: string, logo: string, url?: string}>  $partners
     */
    public function __construct(
        public ?string $title = 'Zaufali nam',
        public ?string $subtitle = null,
        public array $partners = [],
        public string $variant = 'default',
        public int $speed = 30,
        public bool $pauseOnHover = true,
        public string $direction = 'left',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.partners');
    }

    public function animationDuration(): string
    {
        $count = count($this->partners);
        $duration = max(10, $count * $this->speed / 10);

        return $duration.'s';
    }
}
