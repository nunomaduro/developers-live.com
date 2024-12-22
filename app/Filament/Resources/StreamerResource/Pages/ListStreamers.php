<?php

namespace App\Filament\Resources\StreamerResource\Pages;

use App\Filament\Resources\StreamerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListStreamers extends ListRecords
{
    protected static string $resource = StreamerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::Medium)
                ->mutateFormDataUsing(function (array $data) {
                    return array_merge($data, [
                        'is_live' => false,
                    ]);
                }),
        ];
    }
}
