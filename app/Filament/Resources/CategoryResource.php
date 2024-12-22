<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->icon('heroicon-o-tag')
                    ->heading('Category Information')
                    ->description('Enter a Twitch category')
                    ->schema([
                        Forms\Components\TextInput::make('category_name')
                            ->label('Category Name')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->icon('heroicon-s-tag')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('game_id')
                    ->icon('heroicon-s-identification')
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\EditAction::make()
                            ->modalWidth(MaxWidth::Medium),
                    ])->dropdown(false),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListCategories::route('/'),
        ];
    }
}

