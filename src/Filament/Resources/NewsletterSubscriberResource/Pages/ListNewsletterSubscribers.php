<?php

namespace Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Webfloo\Filament\Exports\NewsletterSubscriberExporter;
use Webfloo\Filament\Resources\NewsletterSubscriberResource;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(NewsletterSubscriberExporter::class)
                ->label('Eksportuj CSV')
                // PII export — requires the dedicated permission, which
                // ShieldRolesSeeder grants only to super_admin.
                ->visible(fn (): bool => auth()->user()?->can(webfloo_permission('export', 'newsletter_subscriber')) === true),
            CreateAction::make(),
        ];
    }
}
