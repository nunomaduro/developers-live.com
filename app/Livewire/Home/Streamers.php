<?php

namespace App\Livewire\Home;

use App\Enums\StreamerStatus;
use App\Models\Streamer;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Streamers extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->paginationPageOptions([12, 24, 48, 'all'])
            ->defaultSort(fn (Builder $query, string $direction) => $query
                ->orderBy('is_live', 'desc')
                ->orderBy('name', 'asc')
            )
            ->query(
                Streamer::query()
                    ->whereStatus(StreamerStatus::Approved)
            )
            ->columns([
                Tables\Columns\Layout\View::make('streamers')
                    ->view('filament.tables.columns.streamers-column')
                    ->components([
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\Layout\Stack::make([
                                Tables\Columns\TextColumn::make('name')
                                    ->label(__('Name'))
                                    ->searchable()
                                    ->sortable()
                                    ->color('primary')
                                    ->weight('bold'),
                                Tables\Columns\TextColumn::make('twitch_username')
                                    ->label(__('Twitch Username'))
                                    ->searchable()
                                    ->sortable()
                                    ->color('gray'),
                            ]),
                        ]),
                    ]),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_live')
                    ->label(__('Live')),
            ]);
    }

    public function twitchViewAction(): Action
    {
        return Action::make('twitchView')
            ->label(__('View'))
            ->icon('hugeicons-arrow-up-right-01')
            ->outlined()
            ->openUrlInNewTab()
            ->color(fn (array $arguments) => $arguments['live'] ? 'primary' : 'gray')
            ->url(fn (array $arguments) => 'https://www.twitch.tv/'.$arguments['username']);
    }

    public function newStreamerAction(): Action
    {
        return Action::make('newStreamer')
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
            });
    }

    public function render(): View
    {
        return view('livewire.home.streamers');
    }
}
