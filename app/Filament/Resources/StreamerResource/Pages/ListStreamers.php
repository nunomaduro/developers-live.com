<?php

namespace App\Filament\Resources\StreamerResource\Pages;

use App\Enums\StreamerStatus;
use App\Filament\Resources\StreamerResource;
use App\Models\Streamer;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListStreamers extends ListRecords
{
    protected static string $resource = StreamerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::ScreenMedium)
                ->mutateFormDataUsing(function (array $data) {
                    return array_merge($data, [
                        'is_live' => false,
                    ]);
                }),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(),
        ];

        foreach (StreamerStatus::cases() as $status) {
            $key = Str::headline($status->name);

            $tabs[$key] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status))
                ->badge(Streamer::query()->where('status', $status)->count());
        }

        return $tabs;
    }
}
