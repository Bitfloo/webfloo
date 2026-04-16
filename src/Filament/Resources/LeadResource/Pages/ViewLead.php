<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadResource\Pages;

use Webfloo\Filament\Resources\LeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
