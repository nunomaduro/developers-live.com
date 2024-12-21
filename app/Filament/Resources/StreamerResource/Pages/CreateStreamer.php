<?php

namespace App\Filament\Resources\StreamerResource\Pages;

use App\Filament\Resources\StreamerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStreamer extends CreateRecord
{
    protected static string $resource = StreamerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_merge($data, [
            'is_live' => false,
        ]);
    }
}
