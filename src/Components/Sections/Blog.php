<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Webfloo\Models\Post;

class Blog extends Component
{
    /**
     * @var Collection<int, Post>
     */
    public Collection $posts;

    /**
     * @param  Collection<int, Post>|null  $posts
     */
    public function __construct(
        public ?string $title = 'Blog',
        public ?string $subtitle = null,
        public ?string $description = 'Najnowsze artykuły z naszego bloga',
        ?Collection $posts = null,
        public int $limit = 3,
        public int $columns = 3,
        public ?string $viewAllUrl = '/blog',
        public string $viewAllText = 'Zobacz wszystkie wpisy',
    ) {
        // Fetch posts if not provided
        if ($posts === null) {
            $this->posts = Post::query()
                ->published()
                ->with('category')
                ->orderByDesc('published_at')
                ->limit($this->limit)
                ->get();
        } else {
            $this->posts = $posts->take($this->limit);
        }
    }

    public function render(): View
    {
        return view('webfloo::components.sections.blog');
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

    public function hasPosts(): bool
    {
        return $this->posts->isNotEmpty();
    }
}
