<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\Lead;

/**
 * @extends Factory<Lead>
 */
final class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'message' => $this->faker->sentence(),
            'source' => Lead::SOURCE_CONTACT_FORM,
            'status' => Lead::STATUS_NEW,
            'assigned_to' => null,
            'estimated_value' => null,
            'currency' => 'PLN',
            'consent_at' => now(),
        ];
    }

    public function contacted(): static
    {
        return $this->state(['status' => Lead::STATUS_CONTACTED, 'last_contacted_at' => now()]);
    }

    public function qualified(): static
    {
        return $this->state(['status' => Lead::STATUS_QUALIFIED]);
    }

    public function converted(): static
    {
        return $this->state(['status' => Lead::STATUS_CONVERTED, 'converted_at' => now()]);
    }

    public function lost(): static
    {
        return $this->state(['status' => Lead::STATUS_LOST]);
    }
}
