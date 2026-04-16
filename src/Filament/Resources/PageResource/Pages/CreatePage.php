<?php

namespace Webfloo\Filament\Resources\PageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webfloo\Filament\Resources\PageResource;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getRedirectUrl(): string
    {
        /** @var string */
        return $this->getResource()::getUrl('index');
    }
}
