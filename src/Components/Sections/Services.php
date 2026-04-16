<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Services extends Component
{
    /**
     * @param  array<int, array{title: string, description?: string, icon?: string, href?: string}>  $services
     */
    public function __construct(
        public ?string $title = 'Nasze usługi',
        public ?string $subtitle = null,
        public ?string $description = null,
        public array $services = [],
        public int $columns = 3,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.services');
    }

    public function gridClasses(): string
    {
        return match ($this->columns) {
            2 => 'md:grid-cols-2',
            3 => 'md:grid-cols-2 lg:grid-cols-3',
            4 => 'md:grid-cols-2 lg:grid-cols-4',
            default => 'md:grid-cols-2 lg:grid-cols-3',
        };
    }
}
