<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\LeadTag;

/**
 * @extends Factory<LeadTag>
 */
final class LeadTagFactory extends Factory
{
    protected $model = LeadTag::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->unique()->word(),
            'color' => $this->faker->randomElement(array_keys(LeadTag::getColorOptions())),
        ];
    }
}
