<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Webfloo\Tests\TestCase;

class SchedulerDisabledTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.schedule.enabled', false);
    }

    public function test_no_webfloo_commands_scheduled_when_schedule_disabled(): void
    {
        $schedule = $this->app?->make(Schedule::class);
        assert($schedule instanceof Schedule);

        $commands = implode(' ', array_map(
            static fn ($event): string => is_string($event->command) ? $event->command : '',
            $schedule->events(),
        ));

        $this->assertStringNotContainsString('sitemap:generate', $commands);
        $this->assertStringNotContainsString('leads:send-reminders', $commands);
    }
}
