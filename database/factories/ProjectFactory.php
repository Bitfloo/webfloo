<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webfloo\Models\Project;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $titlePl = $this->faker->unique()->sentence(3);
        $trans = fn () => ['pl' => $this->faker->sentence(), 'en' => $this->faker->sentence()];

        return [
            'title' => ['pl' => $titlePl, 'en' => $this->faker->sentence(3)],
            'slug' => Str::slug($titlePl),
            'excerpt' => $trans(),
            'description' => $trans(),
            'challenge' => $trans(),
            'solution' => $trans(),
            'results' => $trans(),
            'image' => null,
            'gallery' => null,
            'category' => $this->faker->word(),
            'industry' => $this->faker->word(),
            'technologies' => ['Laravel', 'PHP'],
            'metrics' => null,
            'achievements' => null,
            'testimonial_quote' => $trans(),
            'testimonial_author' => $this->faker->name(),
            'testimonial_role' => 'CEO',
            'testimonial_company' => $this->faker->company(),
            'testimonial_avatar' => null,
            'client' => $this->faker->company(),
            'url' => null,
            'video_url' => null,
            'duration' => '3 months',
            'team_size' => $this->faker->numberBetween(2, 10),
            'is_featured' => false,
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

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
