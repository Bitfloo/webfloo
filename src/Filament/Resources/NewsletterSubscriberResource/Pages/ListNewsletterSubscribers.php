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
                ->label('Eksportuj CSV'),
            CreateAction::make(),
        ];
    }
}
