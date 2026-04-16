<?php

namespace Webfloo\Filament\Exports;

use Webfloo\Models\NewsletterSubscriber;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class NewsletterSubscriberExporter extends Exporter
{
    protected static ?string $model = NewsletterSubscriber::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('email')
                ->label('Email'),

            ExportColumn::make('name')
                ->label('Imię'),

            ExportColumn::make('is_active')
                ->label('Aktywny')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Tak' : 'Nie'),

            ExportColumn::make('source')
                ->label('Źródło'),

            ExportColumn::make('subscribed_at')
                ->label('Data zapisania'),

            ExportColumn::make('unsubscribed_at')
                ->label('Data wypisania'),

            ExportColumn::make('ip_address')
                ->label('Adres IP'),

            ExportColumn::make('created_at')
                ->label('Utworzono'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Eksport subskrybentów newslettera zakończony. Wyeksportowano '.number_format($export->successful_rows).' rekordów.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' rekordów nie udało się wyeksportować.';
        }

        return $body;
    }
}
