<?php

namespace App\Filament\Pages;

use App\Models\Settings as ModelsSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public $settings;

    public function __construct()
    {
        $this->settings = ModelsSettings::first();
    }

    public function mount(): void
    {
        $this->form->fill(
            $this->settings->attributesToArray()
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

        ])
            ->statePath('data')
            ->model($this->settings);
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
        $this->settings->update(
            $this->form->getState()
        );

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
