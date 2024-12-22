<?php

namespace App\Filament\Guest\Widgets;

use App\Enums\StreamerStatus;
use App\Models\Streamer;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
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
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('twitch_username')
                    ->label(__('Twitch Username'))
                    ->url(fn (Streamer $streamer) => 'https://www.twitch.tv/'.$streamer->twitch_username)
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_live')
                    ->label(__('Live'))
                    ->boolean(),
            ])->headerActions([
                Action::make('Submit a Streamer')
                    ->label(__('Submit a Streamer'))
                    ->modalWidth(MaxWidth::Medium)
                    ->icon('heroicon-o-microphone')
                    ->form([
                        Section::make()
                            ->icon('heroicon-o-microphone')
                            ->heading(__('Streamer Information'))
                            ->description(__('Enter the streamer information'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required(),
                                TextInput::make('twitch_username')
                                    ->label(__('Twitch Username'))
                                    ->required()
                                    ->unique(
                                        'streamers',
                                        'twitch_username',
                                    ),
                            ]),

                    ])->action(function (array $data) {
                        Streamer::create([
                            ...$data,
                            'status' => StreamerStatus::PendingApproval,
                            'is_live' => false,
                        ]);
                    })->after(function () {
                        Notification::make()
                            ->title(__('Your streamer has been submitted for approval.'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
