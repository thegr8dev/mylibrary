<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionRelationManager;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 10;

    protected static ?string $navigationGroup = 'User and Permissions';

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
            ->columns([
                ImageColumn::make('avatar')->default('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQF-pQcgOR-TCTFYKlWGs8rSkvx4PvbOFplrM_HUD3r0w&s')->circular(),
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
                            ImageEntry::make('profile_pic')->label('')->disk('public')->circular()->extraAttributes(['class' => 'justify-center'])->default('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQF-pQcgOR-TCTFYKlWGs8rSkvx4PvbOFplrM_HUD3r0w&s'),
                        ])->columnSpan(1),
                        Group::make()->schema([
                            TextEntry::make('name'),
                            TextEntry::make('email'),
                            TextEntry::make('phone_no'),
                            TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => match ($state) {
                                1 => 'Active',
                                0 => 'Deactive'
                            })
                                ->color(fn ($state) => match ($state) {
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
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SubscriptionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            // 'view' => Pages\ViewUser::route('/{record}/'),
        ];
    }
}
