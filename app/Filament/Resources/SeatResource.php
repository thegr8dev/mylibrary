<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SeatExporter;
use App\Filament\Imports\SeatImporter;
use App\Filament\Resources\SeatResource\Pages;
use App\Filament\Resources\SeatResource\Pages\EditSeat;
use App\Filament\Resources\SeatResource\Pages\SeatSubscriptions;
use App\Filament\Resources\SeatResource\RelationManagers\SubscriptionRelationManager;
use App\Models\Seat;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SeatResource extends Resource
{
    protected static ?string $model = Seat::class;

    protected static ?string $navigationGroup = 'Seating Management';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('seat_no')->label('Seat No.')->required()->string()->unique('seats', 'seat_no', ignoreRecord: true),
                    Textarea::make('note')->hint('Optional : If you want to put any extra note'),
                    Toggle::make('status'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(SeatImporter::class)
                    ->chunkSize(250)
                    ->options([
                        'updateExisting' => false,
                    ]),
                ExportAction::make()
                    ->exporter(SeatExporter::class)
                    ->chunkSize(250),
            ])
            ->columns([
                TextColumn::make('seat_no')->sortable()->searchable(),
                ToggleColumn::make('status')->sortable(),
                TextColumn::make('note')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->date('d-m-Y')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(SeatExporter::class)
                        ->chunkSize(250),
                ]),
            ]);
    }


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditSeat::class,
            SeatSubscriptions::class
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeats::route('/'),
            // 'create' => Pages\CreateSeat::route('/create'),
            'edit' => Pages\EditSeat::route('/{record}/edit'),
            'subscriptions' => Pages\SeatSubscriptions::route('/{record}/manage/subscriptions')
        ];
    }
}
