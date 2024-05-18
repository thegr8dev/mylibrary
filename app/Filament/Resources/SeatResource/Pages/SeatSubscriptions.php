<?php

namespace App\Filament\Resources\SeatResource\Pages;

use App\Filament\Resources\SeatResource;
use App\Filament\Resources\SeatResource\RelationManagers\SubscriptionRelationManager;
use App\Models\Seat;
use Filament\Resources\Pages\ManageRelatedRecords;

class SeatSubscriptions extends ManageRelatedRecords
{
    protected static string $resource = SeatResource::class;

    protected static string $relationship = 'subscription';

    protected static ?string $model = Seat::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $inverseRelationship = 'seat';

    public static function getNavigationBadge(): string
    {
        $seatId = request()->route('record'); // Get the current seat ID from the route
        $subscriptionCount = Seat::findOrFail($seatId)->subscription()->count(); // Get the count of related subscriptions

        return (string) $subscriptionCount;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationLabel(): string
    {
        return 'Subscriptions';
    }

    public function getRelationManagers(): array
    {
        return [
            SubscriptionRelationManager::class
        ];
    }
}
