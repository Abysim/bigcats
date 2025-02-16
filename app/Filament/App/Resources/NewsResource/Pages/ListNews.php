<?php

namespace App\Filament\App\Resources\NewsResource\Pages;

use App\Filament\App\Resources\NewsResource;
use Filament\Resources\Pages\ListRecords;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
