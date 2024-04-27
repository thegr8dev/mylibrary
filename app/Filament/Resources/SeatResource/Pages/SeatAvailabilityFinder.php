<?php

namespace App\Filament\Resources\SeatResource\Pages;

use App\Filament\Resources\SeatResource;
use Faker\Core\File;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class SeatAvailabilityFinder extends ListRecords
{
    protected static string $resource = SeatResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seat_no')
                    ->badge()
                    ->color(Color::Fuchsia)
                    ->formatStateUsing(fn ($state) => config('seatprefix.pre') . $state),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Active',
                        0 => 'Deactive'
                    })
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        0 => 'danger'
                    })
            ])
            ->filters([
                Filter::make('dateRange')
                    ->form([
                        DatePicker::make('start_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->default(now())
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $get('start_date') > $get('end_date') ? $set('end_date', '') : ""),
                        DatePicker::make('end_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->minDate(fn (Get $get) => $get('start_date'))
                            ->default(now())->live(),
                    ])
                    ->columns(2)
                    ->columnSpan(3)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->whereDoesntHave('subscription', function ($q) use ($data) {
                                return $q->whereStatus('active')
                                    ->when($data['start_date'], function ($q, $date) {
                                        return $q->whereDate('end_date', '>=', $date);
                                    })
                                    ->when($data['end_date'], function ($q, $date) {
                                        return $q->whereDate('start_date', '<=', $date);
                                    });
                            });
                    }),

                TernaryFilter::make('status')->trueLabel('Active')->falseLabel('Deactive')->default('1')

            ], layout: FiltersLayout::AboveContent);
    }

    public function getTitle(): string | Htmlable
    {
        return __("Seat Availability Locator");
    }

    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery()
            ->whereDoesntHave('subscription', function ($q) {
                $q
                    ->where('status', '=', 'active')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            });
    }
}
