<?php

namespace App\Filament\Resources\StreamerResource\Pages;

use App\Filament\Resources\StreamerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStreamer extends EditRecord
{
    protected static string $resource = StreamerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
