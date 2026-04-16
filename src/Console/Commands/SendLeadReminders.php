<?php

declare(strict_types=1);

namespace Webfloo\Console\Commands;

use Webfloo\Models\LeadReminder;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendLeadReminders extends Command
{
    protected $signature = 'leads:send-reminders';

    protected $description = 'Send notifications for due lead reminders';

    public function handle(): int
    {
        $reminders = LeadReminder::query()
            ->pending()
            ->where('due_at', '<=', now())
            ->where('notification_sent', false)
            ->with(['lead', 'user'])
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No due reminders to send.');

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($reminders as $reminder) {
            $user = $reminder->user;
            if ($user === null) {
                continue;
            }

            $body = "Lead: {$reminder->lead->name}";
            if ($reminder->description) {
                $body .= "\n{$reminder->description}";
            }

            $notification = Notification::make()
                ->title('Przypomnienie: '.$reminder->title)
                ->body($body)
                ->icon('heroicon-o-bell');

            // Set color based on priority
            match ($reminder->priority) {
                LeadReminder::PRIORITY_URGENT => $notification->danger(),
                LeadReminder::PRIORITY_HIGH => $notification->warning(),
                default => $notification->info(),
            };

            $notification->sendToDatabase($user);

            $reminder->update(['notification_sent' => true]);
            $sentCount++;
        }

        $this->info("Sent {$sentCount} reminder notification(s).");

        return self::SUCCESS;
    }
}
