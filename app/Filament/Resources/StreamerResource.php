<?php

namespace App\Filament\Resources;

use App\Enums\StreamerStatus;
use App\Filament\Resources\StreamerResource\Pages;
use App\Models\Streamer;
use App\Services\TwitchService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class StreamerResource extends Resource
{
    protected static ?string $model = Streamer::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->icon('heroicon-o-microphone')
                    ->heading(__('Streamer Information'))
                    ->description(__('Enter the streamer information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                        Forms\Components\TextInput::make('twitch_username')
                            ->label(__('Twitch Username'))
                            ->required()
                            ->unique(
                                'streamers',
                                'twitch_username',
                                ignoreRecord: true,
                            ),
                        Forms\Components\ToggleButtons::make('status')
                            ->label(__('Status'))
                            ->inline()
                            ->required()
                            ->grouped()
                            ->options(StreamerStatus::class)
                            ->default(StreamerStatus::PendingApproval),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(__('Avatar'))
                    ->circular()
                    ->defaultImageUrl(fn (Streamer $record): string => "https://ui-avatars.com/api/?name={$record->name}&color=FFFFFF&background=09090b"),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('twitch_username')
                    ->label(__('Twitch Username'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Streamer $streamer) => 'https://www.twitch.tv/'.$streamer->twitch_username, shouldOpenInNewTab: true),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(StreamerStatus::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Medium)
                    ->after(function (Streamer $record, TwitchService $twitchService) {

                        if (Arr::get($record->getChanges(), 'status') === 'approved') {

                            $profileData = $twitchService->getProfileData($record->twitch_username);
                            $record->update($profileData);
                        }
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\Action::make('approve')
                            ->label(__('Approve'))
                            ->visible(fn (Streamer $streamer) => $streamer->status !== StreamerStatus::Approved)
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function (Streamer $record, TwitchService $twitchService) {

                                $data = $twitchService->getProfileData($record->twitch_username);
                                $data['status'] = StreamerStatus::Approved;
                                $record->update($data);

                            }),
                        Tables\Actions\Action::make('reject')
                            ->label(__('Reject'))
                            ->visible(fn (Streamer $streamer) => $streamer->status !== StreamerStatus::Rejected)
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(fn (Streamer $record) => $record->update([
                                'status' => StreamerStatus::Rejected,
                            ])),
                        Tables\Actions\ViewAction::make(),

                    ])->dropdown(false),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('Approve'))
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Streamer $record, Collection $selectedRecords) {
                            $selectedRecords->each(
                                fn (Streamer $selectedRecord) => $selectedRecord->update([
                                    'status' => StreamerStatus::Approved,
                                ]),
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('updateProfile')
                        ->label(__('Update Profile'))
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->action(function (Streamer $record, Collection $selectedRecords, TwitchService $twitchService) {
                            $selectedRecords->each(
                                fn (Streamer $selectedRecord) => $selectedRecord->update(
                                    $twitchService->getProfileData($selectedRecord->twitch_username),
                                ),
                            );
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(self::getLabel().' Details')
                    ->icon(self::$navigationIcon)
                    ->schema([
                        Group::make([
                            ImageEntry::make('avatar_url')
                                ->label(__('Avatar'))
                                ->hiddenLabel()
                                ->size('sm')
                                ->circular()
                                ->width('100px')
                                ->defaultImageUrl(fn (Streamer $record): string => "https://ui-avatars.com/api/?name={$record->name}&color=FFFFFF&background=09090b"),
                            TextEntry::make('name')
                                ->label(__('Name')),
                            TextEntry::make('twitch_username')
                                ->label(__('Twitch Username'))
                                ->icon('heroicon-o-arrow-top-right-on-square')
                                ->url(fn (Streamer $streamer) => 'https://www.twitch.tv/'.$streamer->twitch_username, shouldOpenInNewTab: true),
                        ])
                            ->columnSpanFull()
                            ->columns(3),

                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->prose()
                            ->columnSpanFull(),

                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label(__('Created At'))
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('updated_at')
                            ->label(__('Updated At'))
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns([
                        'xl' => 2,
                        '2xl' => 2,
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStreamers::route('/'),
        ];
    }
}
