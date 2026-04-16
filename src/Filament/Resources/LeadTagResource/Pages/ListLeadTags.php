<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadTagResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webfloo\Filament\Resources\LeadTagResource;

class ListLeadTags extends ListRecords
{
    protected static string $resource = LeadTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
