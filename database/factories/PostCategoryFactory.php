<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webfloo\Models\PostCategory;

/**
 * @extends Factory<PostCategory>
 */
final class PostCategoryFactory extends Factory
{
    protected $model = PostCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ['pl' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'description' => ['pl' => $this->faker->sentence(), 'en' => $this->faker->sentence()],
            'icon' => null,
            'color' => 'primary',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
