<?php

namespace App\Filament\Pages;

use App\Enums\SiteColors;
use App\Enums\SiteFonts;
use App\Models\Settings as ModelsSettings;
use App\Settings\SiteSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\View\View;
use PHPUnit\TestRunner\TestResult\Collector;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationGroupIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            [
                'site_title' => app(SiteSettings::class)->site_title,
                'logo' => app(SiteSettings::class)->logo,
                'favicon' => app(SiteSettings::class)->favicon,
                'primary_color' => app(SiteSettings::class)->primary_color,
                'font' => app(SiteSettings::class)->font,
                'copyright_text' => app(SiteSettings::class)->copyright_text,
            ]
        );
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Site Title')->description('This will be your site title which display over emails & footer')->schema([
                TextInput::make('site_title')->required()->string(),
            ])->aside()->icon('heroicon-m-cursor-arrow-rays'),

            Section::make('Logo')->description('Upload a site logo where maximum size would be 400 x 300px')->schema([
                FileUpload::make('logo')
                    ->disk('public')
                    ->directory('site_assets')
                    ->rule('dimensions:max_width=400,max_height=300')
                    ->validationMessages([
                        'dimensions' => ':attribute kk',
                    ])
                    ->hint('Logo size max is 400 x 300 px')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxSize(1024),
            ])->aside()->icon('heroicon-o-puzzle-piece'),

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

            Section::make('Theming')
                ->description('Customize your site primary color, fonts.')
                ->schema([
                    Select::make('primary_color')
                        ->allowHtml()
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
                        ->label('Fonts')
                        ->placeholder('Try new font family')
                        ->native(false)
                        ->options(
                            collect(SiteFonts::cases())
                                ->mapWithKeys(static fn ($case) => [
                                    $case->value => "<span style='font-family:{$case->getLabel()}'>{$case->getLabel()}</span>",
                                ]),
                        ),
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

        Notification::make()
            ->title('Settings updated!')
            ->success()
            ->send();
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
