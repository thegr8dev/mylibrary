<?php

namespace App\Filament\Pages;

use App\Models\Seat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class SeatAvailablity extends Page implements HasTable
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Seating Management';

    protected static string $view = 'filament.pages.seat-availablity';

    protected static ?string $slug = 'seats/finder';

    protected static ?string $navigationLabel = 'Seat Locator';

    protected static ?int $navigationSort = 2;

    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Seat::query())
            ->columns([
                TextColumn::make('seat_no')
                    ->badge()
                    ->color(Color::Fuchsia)
                    ->formatStateUsing(fn ($state) => config('seatprefix.pre').$state),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Active',
                        0 => 'Deactive'
                    })
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        0 => 'danger'
                    }),
            ])
            ->filters([
                Filter::make('dateRange')
                    ->form([
                        DatePicker::make('start_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->default(now()->format('d-m-Y'))
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) => $get('start_date') > $get('end_date') ? $set('end_date', '') : ''
                            ),
                        DatePicker::make('end_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->minDate(fn (Get $get) => $get('start_date'))
                            ->default(now()->format('d-m-Y'))
                            ->live(),
                    ])
                    ->columns(2)
                    ->columnSpan(3)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->whereDoesntHave('subscription', function ($q) use ($data) {

                                // if ($data['start_date'] && $data['end_date']) {

                                return $q->active($data['start_date'], $data['end_date']);
                                // }
                            });
                    }),

                TernaryFilter::make('status')->trueLabel('Active')->falseLabel('Deactive')->default('1'),

            ], layout: FiltersLayout::AboveContent);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Seat Availability Locator');
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
