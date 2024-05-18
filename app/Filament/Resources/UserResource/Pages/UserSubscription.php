<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionRelationManager;
use App\Models\User;
use Filament\Resources\Pages\ManageRelatedRecords;

class UserSubscription extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'subscription';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $title = 'User Subscriptions';

    public static function getNavigationLabel(): string
    {
        return 'Subscriptions';
    }

    public static function getNavigationBadge(): string
    {
        $seatId = request()->route('record'); // Get the current seat ID from the route
        $subscriptionCount = User::findOrFail($seatId)->subscription()->count(); // Get the count of related subscriptions

        return (string) $subscriptionCount;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public function getRelationManagers(): array
    {
        return [
            SubscriptionRelationManager::class
        ];
    }
}
