<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\Testimonial;

/**
 * @extends Factory<Testimonial>
 */
final class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'content' => ['pl' => $this->faker->paragraph(), 'en' => $this->faker->paragraph()],
            'author' => $this->faker->name(),
            'role' => ['pl' => 'Dyrektor', 'en' => 'Director'],
            'company' => ['pl' => $this->faker->company(), 'en' => $this->faker->company()],
            'avatar' => null,
            'rating' => 5,
            'is_active' => true,
            'is_featured' => false,
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

    public function withRating(int $rating): static
    {
        return $this->state(['rating' => $rating]);
    }
}
