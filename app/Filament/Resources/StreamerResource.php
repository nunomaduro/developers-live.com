<?php

namespace App\Filament\Resources;

use App\Enums\StreamerStatus;
use App\Filament\Resources\StreamerResource\Pages;
use App\Models\Streamer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class StreamerResource extends Resource
{
    protected static ?string $model = Streamer::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->icon('heroicon-o-microphone')
                    ->heading('Streamer Information')
                    ->description('Enter the streamer information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('twitch_username')
                            ->required()
                            ->unique(
                                'streamers',
                                'twitch_username',
                                ignoreRecord: true,
                            ),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'category_name'),
                        Forms\Components\ToggleButtons::make('status')
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
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('twitch_username')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Streamer $streamer) => 'https://www.twitch.tv/'.$streamer->twitch_username)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Category type')
                    ->searchable()
                    ->sortable()
                    ->url(
                        fn (Streamer $streamer) => $streamer->category
                            ? 'https://twitch.tv/directory/category/'.$streamer->category->dashedCategoryName()
                            : null
                    )
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options(StreamerStatus::class),
                Tables\Filters\SelectFilter::make('Category type')
                    ->relationship('category', 'category_name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\Action::make('approve')
                            ->visible(fn (Streamer $streamer) => $streamer->status !== StreamerStatus::Approved)
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(fn (Streamer $record) => $record->update([
                                'status' => StreamerStatus::Approved,
                            ])),
                        Tables\Actions\Action::make('reject')
                            ->visible(fn (Streamer $streamer) => $streamer->status !== StreamerStatus::Rejected)
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(fn (Streamer $record) => $record->update([
                                'status' => StreamerStatus::Rejected,
                            ])),
                        Tables\Actions\EditAction::make()
                            ->modalWidth(MaxWidth::Medium),
                    ])->dropdown(false),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
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
                    Tables\Actions\DeleteBulkAction::make(),
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
