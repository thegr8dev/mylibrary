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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\View\View;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create new user')->schema([
                    Group::make()->schema([
                        FileUpload::make('profile_pic')
                            ->label('User Profile Picture')
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
                        Toggle::make('status'),
                    ])->columnSpan(2),

                ])->columns(3),
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
            ->body('User has been created !');
    }
}
