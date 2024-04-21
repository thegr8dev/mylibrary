<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-profile';

    protected static ?string $slug = 'profile';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            auth()->user()->attributesToArray()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Edit Profile')->schema([
                    Group::make()->schema([
                        FileUpload::make('profile_pic')
                            ->label('Profile Picture')
                            ->avatar()
                            ->disk('public')
                            ->directory('profile-pictures')
                            ->imageEditor()
                            ->circleCropper()
                            ->alignment(Alignment::Center)
                            ->extraAttributes(['class' => 'pt-4']),
                    ])->columnSpan(1)->extraAttributes(['style' => 'text-align:center']),
                    Group::make()->schema([
                        TextInput::make('name')
                            ->label('Enter name')
                            ->required()
                            ->string(),
                        TextInput::make('email')
                            ->label('Enter email')
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->required()
                            ->email(),
                        TextInput::make('phone_no')
                            ->label('Enter Phone no.')
                            ->required()
                            ->maxLength(10)
                            ->minLength(10)
                            ->unique(User::class, 'phone_no', ignoreRecord: true)
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                            ->prefix('+91'),
                        Toggle::make('status'),

                        Fieldset::make('Security')->schema([
                            Toggle::make('change_password')->live(),
                            TextInput::make('password')
                                ->label('Enter password')
                                ->required(fn (Get $get) => $get('change_password') ? true : false)
                                ->password()
                                ->confirmed()
                                ->dehydrated(fn (Get $get) => $get('change_password') ? true : false)
                                ->revealable()
                                ->hidden(function (Get $get) {
                                    return ! $get('change_password') ? true : false;
                                }),
                            TextInput::make('password_confirmation')
                                ->label('Confirm password')
                                ->required(fn (Get $get) => $get('change_password') ? true : false)
                                ->revealable()
                                ->password()
                                ->hidden(function (Get $get) {
                                    return ! $get('change_password') ? true : false;
                                }),
                        ])->columns(1),

                    ])->columnSpan(2),

                ])->columns(3)->icon('heroicon-m-pencil-square'),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update')
                ->color('primary')
                ->submit('Update'),
        ];
    }

    public function update()
    {
        auth()->user()->update(
            $this->form->getState()
        );

        Notification::make()
            ->title('Profile updated!')
            ->success()
            ->send();
    }
}
