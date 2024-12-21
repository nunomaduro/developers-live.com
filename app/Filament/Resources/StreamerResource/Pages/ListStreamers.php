<?php

namespace App\Filament\Resources\StreamerResource\Pages;

use App\Filament\Resources\StreamerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStreamers extends ListRecords
{
    protected static string $resource = StreamerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
