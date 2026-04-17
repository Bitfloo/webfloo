<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webfloo\Models\Page;

/**
 * @extends Factory<Page>
 */
final class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $titlePl = $this->faker->unique()->sentence(3);

        return [
            'title' => ['pl' => $titlePl, 'en' => $this->faker->sentence(3)],
            'slug' => Str::slug($titlePl),
            'content' => [],
            'template' => 'default',
            'parent_id' => null,
            'meta_title' => ['pl' => $titlePl, 'en' => $titlePl],
            'meta_description' => ['pl' => $this->faker->sentence(), 'en' => $this->faker->sentence()],
            'meta_image' => null,
            'status' => 'draft',
            'published_at' => null,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()]);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }
}
