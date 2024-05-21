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

use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\UserSubscription;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 10;

    protected static ?string $navigationGroup = 'User and Permissions';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return [
            'Name' => $record->name,
            'Email' => $record->email,
            'Phone' => $record->phone_no,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone_no'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        /** Excluding Super Admin from search */
        return parent::getGlobalSearchEloquentQuery()->where('id', '!=', 1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(UserImporter::class)
                    ->chunkSize(100),
                ExportAction::make()
                    ->exporter(UserExporter::class)
                    ->chunkSize(100),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll(60)
            ->columns([
                ImageColumn::make('profile_pic')
                    ->disk('public')
                    ->default('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQF-pQcgOR-TCTFYKlWGs8rSkvx4PvbOFplrM_HUD3r0w&s')
                    ->circular(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('phone_no')->searchable()->sortable(),
                ToggleColumn::make('status')->searchable()->sortable(),
                TextColumn::make('created_at')->since()->searchable()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->infolist(fn (User $record) => [

                    Fieldset::make($record->name)->schema([
                        Group::make()->schema([
                            ImageEntry::make('profile_pic')
                                ->label('')
                                ->disk('public')
                                ->circular()
                                ->extraAttributes(['class' => 'justify-center'])
                                ->default('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQF-pQcgOR-TCTFYKlWGs8rSkvx4PvbOFplrM_HUD3r0w&s'),
                        ])->columnSpan(1),
                        Group::make()->schema([
                            TextEntry::make('name'),
                            TextEntry::make('email'),
                            TextEntry::make('phone_no'),
                            TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => match ($state) {
                                1 => 'Active',
                                0 => 'Deactive'
                            })->color(fn ($state) => match ($state) {
                                1 => 'success',
                                0 => 'danger'
                            }),
                            TextEntry::make('created_at')->label('User Since')->since(),
                        ])->columns(2)->columnSpan(2),
                    ])->columns(3),
                ])->modalWidth(MaxWidth::TwoExtraLarge)->modalHeading('User Card'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(UserExporter::class)
                        ->chunkSize(100),
                ]),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditUser::class,
            UserSubscription::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'subscriptions' => Pages\UserSubscription::route('/{record}/manage/subscriptions'),
        ];
    }
}
