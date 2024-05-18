<?php

namespace App\Filament\Pages;

use App\Enums\Currency;
use App\Enums\DateFormat;
use App\Enums\SiteColors;
use App\Enums\SiteFonts;
use App\Enums\Timezone;
use App\Settings\SiteSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\HtmlString;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationGroupIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            [
                'site_title' => app(SiteSettings::class)->site_title,
                'light_logo' => app(SiteSettings::class)->light_logo,
                'dark_logo' => app(SiteSettings::class)->dark_logo,
                'favicon' => app(SiteSettings::class)->favicon,
                'primary_color' => app(SiteSettings::class)->primary_color,
                'font' => app(SiteSettings::class)->font,
                'spa_mode' => app(SiteSettings::class)->spa_mode,
                'top_navigation' => app(SiteSettings::class)->top_navigation,
                'copyright_text' => app(SiteSettings::class)->copyright_text,
                'currency' => app(SiteSettings::class)->currency,
                'dateFormat' => app(SiteSettings::class)->dateFormat,
                'timezone' => config('app.timezone')
            ]
        );
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Site Title')->description('This will be your site title which display over emails & footer')->schema([
                TextInput::make('site_title')->required()->string()->hintIcon('heroicon-o-information-circle')->hintIconTooltip('Changing the site title will log you out. Please log in again to continue.'),
            ])->aside()->icon('heroicon-m-cursor-arrow-rays'),

            Section::make('Logo')->description('Upload a site logo where maximum size would be 400 x 300px')->schema([
                FileUpload::make('light_logo')
                    ->label('Light Mode Logo')
                    ->disk('public')
                    ->directory('site_assets')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxSize(1024),
                FileUpload::make('dark_logo')
                    ->label('Dark Mode Logo')
                    ->disk('public')
                    ->directory('site_assets')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxSize(1024),
            ])->aside()->icon('heroicon-o-puzzle-piece')->columns(2),

            Section::make('Favicon')->description('Upload a favicon where maximum size would be 100 x 100px')->schema([
                FileUpload::make('favicon')
                    ->disk('public')
                    ->directory('site_assets')
                    ->rule('dimensions:max_width=100,max_height=100')
                    ->validationMessages([
                        'dimensions' => ':attribute kk',
                    ])
                    ->hint('Favicon size max can be 100 x 100 px')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxSize(500),
            ])->aside()->icon('heroicon-o-cube'),

            Section::make('Copyright Text')
                ->description('This will display in your site footer')
                ->schema([
                    TextInput::make('copyright_text')->required()->string(),
                ])->aside()->icon('heroicon-m-code-bracket'),

            Section::make('Misc Settings')
                ->description('Configure your application settings to customize date format (e.g., d/m/Y), set your local timezone, and define the preferred currency symbol and format for accurate and personalized data display')
                ->columns(1)
                ->schema([
                    Select::make('currency')
                        ->native(false)
                        ->searchable()
                        ->placeholder('Select default currency')
                        ->options(
                            collect(Currency::cases())
                                ->mapWithKeys(
                                    fn (Currency $currency) => [$currency->value => $currency->getLabel()]
                                )->toArray()
                        ),
                    Select::make('dateFormat')
                        ->native(false)
                        ->searchable()
                        ->allowHtml()
                        ->hint(fn (Get $get) => new HtmlString(__('It will display date as: <b>:date </b>', ['date' => date($get('dateFormat'))])))
                        ->placeholder('Select default date display format')
                        ->live(onBlur: true)
                        ->options(
                            collect(DateFormat::cases())
                                ->mapWithKeys(
                                    fn (DateFormat $dateFormat) => [$dateFormat->value => $dateFormat->getLabel()]
                                )->toArray()
                        ),
                    Select::make('timezone')
                        ->native(false)
                        ->label('Timezone')
                        ->searchable()
                        ->allowHtml()
                        ->hint(new HtmlString(__('Current timezone is set to <b>:timezone</b> and time is <b>:time</b>', ['timezone' => config('app.timezone'), 'time' => now()->format('h:i A')])))
                        ->placeholder('Select default timezone format')
                        ->live(onBlur: true)
                        ->options(
                            collect(Timezone::cases())
                                ->mapWithKeys(
                                    fn (Timezone $timezone) => [$timezone->value => $timezone->getLabel()]
                                )->toArray()
                        )

                ])->aside()->icon('heroicon-m-beaker'),

            Section::make('Theming')
                ->description(new HtmlString('Customize your site primary color, fonts, spa and navigation style'))
                ->schema([
                    Select::make('primary_color')
                        ->allowHtml()
                        ->searchable()
                        ->native(false)
                        ->label('Primary Color')
                        ->placeholder('Try new site color')
                        ->options(
                            collect(SiteColors::cases())
                                ->sortBy(fn ($color) => $color->value)
                                ->mapWithKeys(static fn ($case) => [
                                    $case->value => "<span class='flex items-center gap-x-4'>
                                <span class='rounded-full w-4 h-4' style='background:rgb(" . $case->getColor()[600] . ")'></span>
                                <span>" . $case->getLabel() . '</span>
                                </span>',
                                ])
                        ),
                    Select::make('font')
                        ->allowHtml()
                        ->searchable()
                        ->label('Fonts')
                        ->placeholder('Try new font family')
                        ->native(false)
                        ->options(
                            collect(SiteFonts::cases())
                                ->mapWithKeys(static fn ($case) => [
                                    $case->value => "<span style='font-family:{$case->getLabel()}'>{$case->getLabel()}</span>",
                                ]),
                        ),
                    ToggleButtons::make('spa_mode')
                        ->label('SPA Mode')
                        ->boolean()
                        ->options([
                            1 => 'ON',
                            0 => 'OFF',
                        ])
                        ->grouped()
                        ->icons([
                            1 => 'heroicon-o-bolt',
                            0 => 'heroicon-o-power',
                        ])
                        ->inline(),
                    ToggleButtons::make('top_navigation')
                        ->label('Navigation Style')
                        ->boolean()
                        ->options([
                            0 => 'Sidebar',
                            1 => 'Topbar',
                        ])
                        ->grouped()
                        ->icons([
                            0 => 'heroicon-o-arrow-left-circle',
                            1 => 'heroicon-o-arrow-up-circle',
                        ])
                        ->colors([
                            0 => 'info',
                            1 => 'info',
                        ])
                        ->inline(),
                ])
                ->columns(2)
                ->aside()
                ->icon('heroicon-m-sparkles'),

        ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update')
                ->color('primary')
                ->label('Update Settings')
                ->icon('')
                ->submit('Update'),
        ];
    }

    public function update()
    {
        $data = $this->form->getState();

        $themeData = app(SiteSettings::class)->fill($data);

        $themeData->save();

        $envEdit = DotenvEditor::setKeys([
            'APP_NAME' => $data['site_title'],
            'TIMEZONE' => $data['timezone'],
        ]);

        $envEdit->save();

        Notification::make()
            ->title('Settings updated!')
            ->success()
            ->send();

        return Redirect::to(route(Settings::getRouteName()));
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
