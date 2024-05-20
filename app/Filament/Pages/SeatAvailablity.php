<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SubscriptionResource\Pages\EditSubscription;
use App\Filament\Resources\SubscriptionResource\Pages\ViewSubscription;
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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
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
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

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
            ->poll(60)
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
                        Select::make('seat_no')
                            ->label('Seat No.')
                            ->searchable()
                            ->placeholder('Filter by seat')
                            ->preload()
                            ->options(Seat::where('status', 1)->limit(10)->pluck('seat_no', 'id'))
                            ->getSearchResultsUsing(
                                fn (string $search): array => Seat::where('seat_no', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('seat_no', 'id')
                                    ->toArray()
                            ),
                        DatePicker::make('start_date')
                            ->native(false)
                            ->displayFormat(app(SiteSettings::class)->dateFormat)
                            ->default(now()->format('Y-m-d'))
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) => $get('start_date') > $get('end_date') ? $set('end_date', '') : ''
                            ),
                        DatePicker::make('end_date')
                            ->native(false)
                            ->displayFormat(app(SiteSettings::class)->dateFormat)
                            ->minDate(fn (Get $get) => $get('start_date'))
                            ->default(now()->format('Y-m-d'))
                            ->live(),
                    ])
                    ->columns(3)
                    ->columnSpan(3)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['seat_no'], function ($q, $seat) {
                                return $q->where('id', $seat);
                            })
                            ->whereDoesntHave('subscription', function ($q) use ($data) {
                                return $q->active($data['start_date'], $data['end_date']);
                            });
                    }),

                TernaryFilter::make('status')->trueLabel('Active')->falseLabel('Deactive')->default('1')->columnSpan([
                    'default' => 3,
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 1,
                ]),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Action::make('add_sub')
                    ->label(fn (Seat $seat) => __('Add Subscription For Seat :seat', ['seat' => config('seatprefix.pre').$seat->seat_no]))
                    ->tooltip(__('Add subscription'))
                    ->icon('heroicon-m-plus-circle')
                    ->slideOver()
                    ->fillForm(fn ($livewire): array => [
                        'start_date' => $livewire->tableFilters['dateRange']['start_date'],
                        'end_date' => $livewire->tableFilters['dateRange']['end_date'],
                    ])
                    ->form([
                        $this->loadSubscriptionForm(),
                    ])
                    ->action(function (array $data, Seat $record): void {

                        $data['seat_id'] = $record->id;

                        if (! in_array($data['status'], ['active', 'upcoming'])) {
                            return;
                        }

                        /** Check if user has already have active subscription */
                        $ifUserHasActiveSub = Subscription::where('user_id', $data['user_id'])->where('status', 'active')->first();

                        if ($ifUserHasActiveSub) {
                            Notification::make()
                                ->title(__('User already have active subscription !'))
                                ->danger()
                                ->body(__('User already have active subscription ON Seat No. :seat', ['seat' => $ifUserHasActiveSub->seat->seat_no]))
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->button()
                                        ->color('success')
                                        ->url(route(EditSubscription::getRouteName(), $ifUserHasActiveSub), shouldOpenInNewTab: true),
                                ])
                                ->color('info')
                                ->send();
                            $this->halt();
                        }

                        $ifSubsExist = Subscription::where('seat_id', $data['seat_id'])
                            ->active($data['start_date'], $data['end_date'])
                            ->first();

                        if ($ifSubsExist) {

                            Notification::make()
                                ->title(__('Seat already mapped with active plan to another user !'))
                                ->danger()
                                ->body(__('Please select different seat or date as this seat is already occupied from :startDate to :endDate !', [
                                    'startDate' => date(app(SiteSettings::class)->dateFormat, strtotime($ifSubsExist['start_date'])),
                                    'endDate' => date(app(SiteSettings::class)->dateFormat, strtotime($ifSubsExist['end_date'])),
                                ]))
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->button()
                                        ->color('success')
                                        ->url(route(EditSubscription::getRouteName(), $ifSubsExist), shouldOpenInNewTab: true),
                                ])
                                ->color('info')
                                ->send();

                            $this->halt();
                        }

                        $data = $record->subscription()->create($data);

                        Notification::make()
                            ->title('Subscription added !')
                            ->success()
                            ->send();

                        User::first()->notify(
                            Notification::make()
                                ->success()
                                ->icon('heroicon-o-bolt')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->size(ActionSize::ExtraSmall)
                                        ->markAsRead()
                                        ->url(route(ViewSubscription::getRouteName(), $data)),
                                ])
                                ->title('A New Subscription added for '.$data->subscriber->name)
                                ->body('This subscription added for Seat No.'.$data->seat->seat_no)
                                ->toDatabase(),
                        );
                    })
                    ->databaseTransaction()
                    ->iconButton(),
            ]);
    }

    public function loadSubscriptionForm()
    {
        $form = Section::make()->schema([
            Select::make('user_id')
                ->options(User::where('status', 1)->limit(10)->pluck('name', 'id'))
                ->getSearchResultsUsing(fn (string $search): array => User::where('name', 'like', "%{$search}%")->limit(10)->pluck('name', 'id')->toArray())
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

            DatePicker::make('start_date')
                ->label('Start date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->live(onBlur: true)
                ->afterStateUpdated(
                    fn (Get $get, Set $set) => $get('start_date') > $get('end_date') ? $set('end_date', '') : ''
                )
                ->minDate(now()->format(app(SiteSettings::class)->dateFormat))
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
            TextInput::make('amount')
                ->prefix(app(SiteSettings::class)->currency)
                ->required()
                ->minValue(1)
                ->numeric(),
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
        ])->columns(2);

        return $form;
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
