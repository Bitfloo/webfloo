<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Webfloo\Tests\TestCase;

class SchedulerTest extends TestCase
{
    /**
     * @return list<string>
     */
    protected function scheduledCommands(): array
    {
        $schedule = $this->app?->make(Schedule::class);
        assert($schedule instanceof Schedule);

        return array_values(array_filter(array_map(
            static fn ($event): string => is_string($event->command) ? $event->command : '',
            $schedule->events(),
        )));
    }

    public function test_sitemap_command_is_scheduled(): void
    {
        $commands = implode(' ', $this->scheduledCommands());

        $this->assertStringContainsString('sitemap:generate', $commands);
    }

    public function test_lead_reminders_command_is_scheduled(): void
    {
        $commands = implode(' ', $this->scheduledCommands());

        $this->assertStringContainsString('leads:send-reminders', $commands);
    }
}
