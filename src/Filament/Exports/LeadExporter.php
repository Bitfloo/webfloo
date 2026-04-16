<?php

declare(strict_types=1);

namespace Webfloo\Filament\Exports;

use Webfloo\Models\Lead;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LeadExporter extends Exporter
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('name')
                ->label('Imię i nazwisko'),

            ExportColumn::make('email')
                ->label('Email'),

            ExportColumn::make('phone')
                ->label('Telefon'),

            ExportColumn::make('company')
                ->label('Firma'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (string $state): string => Lead::getStatusOptions()[$state] ?? $state),

            ExportColumn::make('source')
                ->label('Źródło')
                ->formatStateUsing(fn (string $state): string => Lead::getSourceOptions()[$state] ?? $state),

            ExportColumn::make('estimated_value')
                ->label('Szacowana wartość'),

            ExportColumn::make('currency')
                ->label('Waluta'),

            ExportColumn::make('assignee.name')
                ->label('Przypisany do'),

            ExportColumn::make('created_at')
                ->label('Data utworzenia'),

            ExportColumn::make('last_contacted_at')
                ->label('Ostatni kontakt'),

            ExportColumn::make('converted_at')
                ->label('Data konwersji'),

            ExportColumn::make('message')
                ->label('Wiadomość'),

            ExportColumn::make('notes')
                ->label('Notatki'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Eksport leadów zakończony. Wyeksportowano '.number_format($export->successful_rows).' '.self::getRowsWord($export->successful_rows).'.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.self::getRowsWord($failedRowsCount).' nie udało się wyeksportować.';
        }

        return $body;
    }

    protected static function getRowsWord(int $count): string
    {
        if ($count === 1) {
            return 'wiersz';
        }

        $lastDigit = $count % 10;
        $lastTwoDigits = $count % 100;

        if ($lastTwoDigits >= 12 && $lastTwoDigits <= 14) {
            return 'wierszy';
        }

        if ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'wiersze';
        }

        return 'wierszy';
    }
}
