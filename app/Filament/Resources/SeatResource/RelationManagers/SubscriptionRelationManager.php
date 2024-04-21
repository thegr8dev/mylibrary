<?php

namespace App\Filament\Resources\SeatResource\RelationManagers;

use App\Filament\Resources\SubscriptionResource\Pages\EditSubscription;
use App\Filament\Resources\SubscriptionResource\Pages\ViewSubscription;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRelationManager extends RelationManager
{
    protected static string $relationship = 'subscription';

    protected static ?string $title = 'Subscriptions';

    protected static ?string $inverseRelationship = 'seat';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                TextColumn::make('uuid')
                    ->label('Subscription ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('subscriber.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => ucwords($state))
                    ->icons([
                        'heroicon-o-rocket-launch' => 'active',
                        'heroicon-o-exclamation-triangle' => 'deactive',
                        'heroicon-o-x-mark' => 'cancelled',
                        'heroicon-o-no-symbol' => 'expired',
                    ])
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'deactive' => 'info',
                        'cancelled' => 'warning',
                        'expired' => 'danger',
                    }),
                TextColumn::make('payment_method')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucwords($state))
                    ->color(fn (string $state) => match ($state) {
                        'cash' => 'success',
                        'online' => Color::Amber,
                    })
                    ->icons([
                        'heroicon-o-banknotes' => 'cash',
                        'heroicon-o-device-phone-mobile' => 'online',
                    ])
                    ->sortable(),
                TextColumn::make('txn_id')
                    ->label('Transcation Id')
                    ->searchable(),
                TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('By Status')
                    ->searchable()
                    ->options([
                        'active' => 'Active',
                        'deactive' => 'Deactive',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('subscriber')
                    ->label('By Subscriber')
                    ->searchable()
                    ->preload()
                    ->relationship('subscriber', 'name')
                    ->preload(),
                SelectFilter::make('payment_method')
                    ->label('By Payment Method')
                    ->searchable()
                    ->indicator('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'online' => 'Online',
                    ]),
                Filter::make('start_date')
                    ->form([
                        DatePicker::make('start_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            );
                    }),
                Filter::make('end_date')
                    ->form([
                        DatePicker::make('end_date')
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->where('end_date', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                // Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(fn (Subscription $record): string => route(ViewSubscription::getRouteName(), $record)),
                    Action::make('edit')
                        ->icon('heroicon-o-pencil-square')
                        ->color('success')
                        ->url(fn (Subscription $record): string => route(EditSubscription::getRouteName(), $record)),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
