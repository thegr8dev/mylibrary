<?php

/*
 - Copyright (c) 2024 @thegr8dev
 -
 - This source code is licensed under the MIT license found in the
 - LICENSE file in the root directory of this source tree.
 -
 - Made in India.
 */

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

    public static function getNavigationItems(array $urlParameters = []): array
    {
        $item = parent::getNavigationItems($urlParameters)[0];

        $ownerRecord = $urlParameters['record'];

        $formSubmissionsCount = $ownerRecord->subscription()->count();

        $item->badge($formSubmissionsCount > 0 ? $formSubmissionsCount : null);

        return [$item];
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
            SubscriptionRelationManager::class,
        ];
    }
}
