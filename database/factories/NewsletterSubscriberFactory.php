<?php

declare(strict_types=1);

namespace Webfloo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webfloo\Models\NewsletterSubscriber;

/**
 * @extends Factory<NewsletterSubscriber>
 */
final class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    public function definition(): array
    {
        return [
            'email'           => $this->faker->unique()->safeEmail(),
            'name'            => $this->faker->name(),
            'is_active'       => true,
            'subscribed_at'   => now(),
            'unsubscribed_at' => null,
            'ip_address'      => $this->faker->ipv4(),
            'source'          => 'footer',
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true, 'unsubscribed_at' => null]);
    }

    public function unsubscribed(): static
    {
        return $this->state(['is_active' => false, 'unsubscribed_at' => now()]);
    }
}
