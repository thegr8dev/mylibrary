<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
            Actions\DeleteAction::make()->icon('heroicon-o-trash'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return '#'.$this->getRecord()->uuid;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()->schema([
                    Fieldset::make('View Subscription')->schema([

                        TextEntry::make('uuid')->label('Subscription ID')->columnSpan(3),

                        Group::make()->schema([

                            TextEntry::make('subscriber.name'),
                            TextEntry::make('seat.seat_no')
                                ->formatStateUsing(fn (string $state) => config('seatprefix.pre').$state),
                        ])->columns(2)->columnSpan(3),

                        Group::make()->schema([
                            TextEntry::make('start_date')->date('d/m/Y'),
                            TextEntry::make('end_date')->date('d/m/Y'),

                        ])->columns(2)->columnSpan(3),

                        Group::make()->schema([

                            TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => ucwords($state))
                                ->icons([
                                    'heroicon-o-rocket-launch' => 'active',
                                    'heroicon-o-exclamation-triangle' => 'deactive',
                                    'heroicon-o-x-mark' => 'cancelled',
                                    'heroicon-o-no-symbol' => 'expired',
                                ])
                                ->color(fn (string $state) => match ($state) {
                                    'active' => 'success',
                                    'deactive' => 'info',
                                    'cancelled' => 'warning',
                                    'expired' => 'danger',
                                }),

                            TextEntry::make('payment_method')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => ucwords($state))
                                ->color(fn (string $state) => match ($state) {
                                    'cash' => Color::Indigo,
                                    'online' => Color::Pink,
                                })
                                ->icons([
                                    'heroicon-o-banknotes' => 'cash',
                                    'heroicon-o-qr-code' => 'online',
                                ]),

                        ])->columns(2)->columnSpan(3),

                        Group::make()->schema([
                            TextEntry::make('txn_id')
                                ->label('Transcation ID')
                                ->default('-'),

                            TextEntry::make('note')
                                ->default('-'),
                        ])->columns(2)->columnSpan(3),

                        ImageEntry::make('payment_proof')
                            ->helperText('Click image to view')
                            ->disk('public')
                            // ->width(200)
                            ->height(150)
                            ->simpleLightbox()
                            ->columnSpanFull(),
                    ])->columns(3),
                ])->maxWidth(MaxWidth::FourExtraLarge),
            ]);
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
