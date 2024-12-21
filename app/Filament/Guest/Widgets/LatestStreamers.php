<?php

namespace App\Filament\Guest\Widgets;

use App\Enums\StreamerStatus;
use App\Models\Streamer;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStreamers extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Streamer::query()
                    ->where('status', StreamerStatus::Approved)
                    ->orderByDesc('is_live')
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('twitch_username')
                    ->url(fn (Streamer $streamer) => 'https://www.twitch.tv/'.$streamer->twitch_username)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_live')
                    ->boolean(),
            ])->headerActions([
                Action::make('Submit a Streamer')
                    ->form([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('twitch_username')
                            ->required()
                            ->unique(
                                'streamers',
                                'twitch_username',
                            ),
                    ])->action(function (array $data) {
                        Streamer::create([
                            ...$data,
                            'status' => StreamerStatus::PendingApproval,
                            'is_live' => false,
                        ]);
                    })->after(function () {
                        Notification::make()
                            ->title('Your streamer has been submitted for approval.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
