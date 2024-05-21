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
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionRelationManager;
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

    public function getRelationManagers(): array
    {
        return [
            SubscriptionRelationManager::class,
        ];
    }
}
