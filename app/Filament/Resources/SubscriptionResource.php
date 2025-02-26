<?php

/*
 - Copyright (c) 2024 @thegr8dev
 -
 - This source code is licensed under the MIT license found in the
 - LICENSE file in the root directory of this source tree.
 -
 - Made in India.
 */

namespace App\Filament\Resources;

use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Seat;
use App\Models\Subscription;
use App\Models\User;
use App\Settings\SiteSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Subscription';

    protected static ?string $navigationLabel = 'Manage Subscriptions';

    protected static ?string $recordTitleAttribute = 'uuid';

    protected static int $globalSearchResultsLimit = 10;

    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return [
            'Subscriber' => $record->subscriber->name,
            'Seat' => config('seatprefix.pre').$record->seat->seat_no,
            'Status' => ucwords($record->status),
            'Start Date' => date('d/m/Y', strtotime($record->start_date)),
            'End Date' => date('d/m/Y', strtotime($record->end_date)),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['uuid', 'payment_method', 'txn_id', 'status', 'subscriber.name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        /** Excluding Super Admin from search */
        return parent::getGlobalSearchEloquentQuery()->with(['subscriber:id,name', 'seat:id,seat_no']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()->schema([
                    Select::make('user_id')
                        ->relationship('subscriber', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('status', 1))
                        ->preload()
                        ->createOptionForm([
                            Section::make('Create new user')->schema([
                                Group::make()->schema([
                                    FileUpload::make('profile_pic')
                                        ->label('User Profile Picture')
                                        ->avatar()
                                        ->disk('public')
                                        ->directory('profile-pictures')
                                        ->imageEditor()
                                        ->circleCropper()
                                        ->extraAttributes(['class' => 'pt-4'])
                                        ->alignment(Alignment::Center),
                                ])->columnSpan(1)->extraAttributes(['style' => 'text-align:center']),
                                Group::make()->schema([
                                    TextInput::make('name')
                                        ->label('Enter name')
                                        ->required()
                                        ->string(),
                                    TextInput::make('email')
                                        ->label('Enter email')
                                        ->required()
                                        ->unique(User::class, 'email')
                                        ->email(),
                                    TextInput::make('phone_no')
                                        ->label('Enter Phone no.')
                                        ->required()
                                        ->unique(User::class, 'phone_no')
                                        ->maxLength(10)
                                        ->minLength(10)
                                        ->tel()
                                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                        ->prefix('+91'),
                                    TextInput::make('password')
                                        ->label('Enter password')
                                        ->required()
                                        ->password()
                                        ->confirmed()
                                        ->revealable(),
                                    TextInput::make('password_confirmation')
                                        ->label('Confirm password')
                                        ->required()
                                        ->revealable()
                                        ->password(),
                                ])->columnSpan(2),

                            ])->columns(3),
                        ])
                        ->searchable()
                        ->required()
                        ->placeholder('Select user')
                        ->validationMessages([
                            'required' => 'Please select user',
                        ]),
                    Select::make('seat_id')
                        ->relationship('seat', 'seat_no', modifyQueryUsing: fn (Builder $query) => $query->where('status', 1))
                        ->preload()
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            Section::make()->schema([
                                TextInput::make('seat_no')->label('Seat No.')->required()->string()->unique('seats', 'seat_no', ignoreRecord: true),
                                Textarea::make('note')->hint('Optional : If you want to put any extra note'),
                            ]),
                        ])
                        ->getOptionLabelFromRecordUsing(fn (Seat $seat) => config('seatprefix.pre')."{$seat->seat_no}")
                        ->validationMessages([
                            'required' => 'Please select seat',
                        ])
                        ->placeholder('Select seat'),
                    DatePicker::make('start_date')
                        ->label('Start date')
                        ->native(false)
                        ->displayFormat(app(SiteSettings::class)->dateFormat)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn (Get $get, Set $set) => $get('start_date') > $get('end_date') ? $set('end_date', '') : ''
                        )
                        ->minDate(fn ($context) => $context == 'create' ? now()->format('Y-m-d') : '')
                        ->closeOnDateSelection()
                        ->placeholder('Select start date')
                        ->validationMessages([
                            'required' => 'Please select start date',
                        ])
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('End date')
                        ->native(false)
                        ->displayFormat(app(SiteSettings::class)->dateFormat)
                        ->placeholder('Select end date')
                        ->minDate(fn (Get $get) => $get('start_date'))
                        ->live(onBlur: true)
                        ->closeOnDateSelection()
                        ->validationMessages([
                            'required' => 'Please select end date',
                        ])
                        ->required(),
                    TextInput::make('amount')
                        ->prefix(app(SiteSettings::class)->currency)
                        ->required()
                        ->minValue(1)
                        ->numeric(),
                    Select::make('status')
                        ->label('Subscription status')
                        ->options([
                            'active' => 'Active',
                            'deactive' => 'Deactive',
                            'cancelled' => 'Cancelled',
                            'expired' => 'Expired',
                        ])
                        ->searchable()
                        ->placeholder('Subscription status')
                        ->required()
                        ->validationMessages([
                            'required' => 'Please select subscription status',
                        ]),
                    Select::make('payment_method')
                        ->label('Payment mode')
                        ->options([
                            'online' => 'Online',
                            'cash' => 'Cash',
                        ])->searchable()
                        ->required()
                        ->placeholder('Payment mode')
                        ->live()
                        ->validationMessages([
                            'required' => 'Please select payment mode',
                        ]),
                    TextInput::make('txn_id')
                        ->label('Transcation Id')
                        ->placeholder('Enter Transcation Id')
                        ->required(fn (Get $get) => $get('payment_method') == 'online' ? true : false)
                        ->hint('* requires if payment mode is online')
                        ->validationMessages([
                            'required' => 'Please enter transcation id',
                        ]),
                    Group::make()->schema([
                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()
                            ->openable()
                            ->disk('public')
                            ->removeUploadedFileButtonPosition('right')
                            ->directory('payments')
                            ->uploadingMessage('Uploading Payment Proof...')
                            ->downloadable()
                            ->maxSize(5000),
                        Textarea::make('note')->placeholder('Any additional note')->rows(3),
                    ])->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(SubscriptionExporter::class)
                    ->chunkSize(100),
            ])
            ->columns([
                TextColumn::make('uuid')
                    ->label('Subscription ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('subscriber.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seat.seat_no')
                    ->badge()
                    ->color(Color::Fuchsia)
                    ->formatStateUsing(fn (string $state) => config('seatprefix.pre')."{$state}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date(app(SiteSettings::class)->dateFormat)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date(app(SiteSettings::class)->dateFormat)
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
                TextColumn::make('amount')
                    ->money(app(SiteSettings::class)->currency)
                    ->summarize(Sum::make()->label('Total')->money(app(SiteSettings::class)->currency)),
                TextColumn::make('payment_method')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucwords($state))
                    ->color(fn (string $state) => match ($state) {
                        'cash' => Color::Indigo,
                        'online' => Color::Pink,
                    })
                    ->icons([
                        'heroicon-o-banknotes' => 'cash',
                        'heroicon-o-credit-card' => 'online',
                    ])
                    ->sortable(),
                TextColumn::make('txn_id')
                    ->label('Transcation Id')
                    ->copyable()
                    ->copyMessage('Transcation Id Copied !')
                    ->searchable(),
                TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime(app(SiteSettings::class)->dateFormat),
                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Last Updated at')
                    ->dateTime(app(SiteSettings::class)->dateFormat),
            ])

            ->filters([
                SelectFilter::make('subscriber')
                    ->label('By Subscriber')
                    ->searchable()
                    ->preload()
                    ->relationship('subscriber', 'name')
                    ->preload(),
                SelectFilter::make('seat')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('By Seat No.')
                    ->relationship('seat', 'seat_no')
                    ->getOptionLabelFromRecordUsing(fn (Seat $seat) => config('seatprefix.pre')."{$seat->seat_no}")
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
                            ->displayFormat(app(SiteSettings::class)->dateFormat)
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
                            ->displayFormat(app(SiteSettings::class)->dateFormat)
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->where('end_date', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(SubscriptionExporter::class)
                        ->chunkSize(100),
                ]),
            ])
            ->poll(60)
            ->defaultSort('start_date', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewSubscription::class,
            Pages\EditSubscription::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}/'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
