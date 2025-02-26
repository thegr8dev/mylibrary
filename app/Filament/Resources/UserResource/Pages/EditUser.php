<?php

/*
 - Copyright (c) 2024 @thegr8dev
 -
 - This source code is licensed under the MIT license found in the
 - LICENSE file in the root directory of this source tree.
 -
 - Made in India.
 */

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\View\View;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $navigationLabel = 'Basic Details';

    public function getContentTabLabel(): ?string
    {
        return 'Basic Details';
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(fn (User $user) => [
                Section::make(__('Edit User : :user', ['user' => $user->name]))->schema([
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
                            ->label('Enter full name')
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
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Success !')
            ->body('User has been updated !');
    }
}
