<?php

/*
 - Copyright (c) 2024 @thegr8dev
 -
 - This source code is licensed under the MIT license found in the
 - LICENSE file in the root directory of this source tree.
 -
 - Made in India.
 */

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon('heroicon-o-clipboard-document-list')
                ->badge(Subscription::query()->count()),
            'active' => Tab::make()
                ->icon('heroicon-o-rocket-launch')
                ->badgeColor('success')
                ->badge(Subscription::query()->where('status', 'active')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'inactive' => Tab::make()
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(Subscription::query()->where('status', 'deactive')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'deactive')),
            'expired' => Tab::make()
                ->icon('heroicon-o-no-symbol')
                ->badgeColor('danger')
                ->badge(Subscription::query()->where('status', 'expired')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired')),
            'cancelled' => Tab::make()
                ->icon('heroicon-o-x-circle')
                ->badgeColor('warning')
                ->badge(Subscription::query()->where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
