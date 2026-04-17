<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\Service;

/**
 * @extends Factory<Service>
 */
final class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $icons = array_keys(Service::getIconOptions());

        return [
            'title'      => ['pl' => $this->faker->sentence(3), 'en' => $this->faker->sentence(3)],
            'description' => ['pl' => $this->faker->paragraph(), 'en' => $this->faker->paragraph()],
            'icon'       => $this->faker->randomElement($icons),
            'href'       => null,
            'is_active'  => true,
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
}
