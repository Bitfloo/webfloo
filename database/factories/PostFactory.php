<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webfloo\Models\Post;

/**
 * @extends Factory<Post>
 */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $titlePl = $this->faker->unique()->sentence(4);

        return [
            'title' => ['pl' => $titlePl, 'en' => $this->faker->sentence(4)],
            'slug' => Str::slug($titlePl),
            'excerpt' => ['pl' => $this->faker->sentence(), 'en' => $this->faker->sentence()],
            'content' => ['pl' => $this->faker->paragraph(), 'en' => $this->faker->paragraph()],
            'featured_image' => null,
            'post_category_id' => null,
            'author_id' => null,
            'reading_time' => $this->faker->numberBetween(1, 15),
            'meta_title' => ['pl' => $titlePl, 'en' => $titlePl],
            'meta_description' => ['pl' => $this->faker->sentence(), 'en' => $this->faker->sentence()],
            'meta_image' => null,
            'no_index' => false,
            'status' => 'draft',
            'published_at' => null,
            'is_featured' => false,
            'sort_order' => 0,
            'views_count' => 0,
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

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
