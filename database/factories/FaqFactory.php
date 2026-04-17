<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\Faq;

/**
 * @extends Factory<Faq>
 */
final class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'question'   => ['pl' => $this->faker->sentence().'?', 'en' => $this->faker->sentence().'?'],
            'answer'     => ['pl' => $this->faker->paragraph(), 'en' => $this->faker->paragraph()],
            'icon'       => null,
            'category'   => null,
            'is_active'  => true,
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
