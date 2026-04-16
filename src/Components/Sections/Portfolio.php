<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Portfolio extends Component
{
    /**
     * Projects array with Blog5 style support.
     *
     * @var array<int, array{
     *     title: string,
     *     slug?: string,
     *     excerpt?: string,
     *     image?: string,
     *     category?: string,
     *     technologies?: array<int, string>,
     *     url?: string,
     *     client?: string,
     *     client_logo?: string,
     *     author?: string,
     *     author_avatar?: string
     * }>
     */
    public array $projects;

    /**
     * @var array<int, string>|null
     */
    public ?array $categories;

    /**
     * @param  array<int, array{
     *     title: string,
     *     slug?: string,
     *     excerpt?: string,
     *     image?: string,
     *     category?: string,
     *     technologies?: array<int, string>,
     *     url?: string,
     *     client?: string,
     *     client_logo?: string,
     *     author?: string,
     *     author_avatar?: string
     * }>|Collection<int, array{
     *     title: string,
     *     slug?: string,
     *     excerpt?: string,
     *     image?: string,
     *     category?: string,
     *     technologies?: array<int, string>,
     *     url?: string,
     *     client?: string,
     *     client_logo?: string,
     *     author?: string,
     *     author_avatar?: string
     * }>  $projects
     * @param  array<int, string>|null  $categories
     */
    public function __construct(
        public ?string $title = 'Our Work',
        public ?string $subtitle = null,
        public ?string $description = null,
        array|Collection $projects = [],
        ?array $categories = null,
        public int $columns = 3,
        public bool $showFilters = true,
        public int $limit = 6,
        public ?string $viewAllUrl = null,
        public bool $staggeredAnimation = false,
        public string $animationVariant = 'wave',
    ) {
        // Convert Collection to array if needed
        if ($projects instanceof Collection) {
            /** @var array<int, array{title: string, slug?: string, excerpt?: string, image?: string, category?: string, technologies?: array<int, string>, url?: string}> $projects */
            $projects = $projects->toArray();
        }

        // Apply limit
        if ($this->limit > 0 && count($projects) > $this->limit) {
            $projects = array_slice($projects, 0, $this->limit);
        }

        $this->projects = $projects;
        $this->categories = $categories;

        // Auto-extract categories from projects if not provided
        if ($this->categories === null && $this->showFilters) {
            $this->categories = $this->extractCategories();
        }
    }

    public function render(): View
    {
        return view('webfloo::components.sections.portfolio');
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

    /**
     * @return array<int, string>
     */
    protected function extractCategories(): array
    {
        $categories = [];
        foreach ($this->projects as $project) {
            $category = $project['category'] ?? null;
            if ($category !== null && ! in_array($category, $categories, true)) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public function hasFilters(): bool
    {
        return $this->showFilters && ! empty($this->categories) && count($this->categories) > 1;
    }
}
