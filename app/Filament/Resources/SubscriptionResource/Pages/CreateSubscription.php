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
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\ActionSize;
use Illuminate\Contracts\View\View;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Subscription created !';
    }

    protected function beforeCreate(): void
    {
        $data = $this->data;

        if (! in_array($this->data['status'], ['active', 'upcoming'])) {
            return;
        }

        /** Check if user has already have active subscription */
        $ifUserHasActiveSub = Subscription::where('user_id', $data['user_id'])->where('status', 'active')->first();

        if ($ifUserHasActiveSub) {
            Notification::make()
                ->title(__('User already have active subscription !'))
                ->danger()
                ->body(__('User already have active subscription ON Seat No. :seat', ['seat' => $ifUserHasActiveSub->seat->seat_no]))
                ->actions([
                    Action::make('view')
                        ->button()
                        ->color('success')
                        ->url(route(EditSubscription::getRouteName(), $ifUserHasActiveSub), shouldOpenInNewTab: true),
                ])
                ->color('info')
                ->send();
            $this->halt();
        }

        $ifSubsExist = Subscription::where('seat_id', $data['seat_id'])
            ->active($data['start_date'], $data['end_date'])
            ->first();

        if ($ifSubsExist) {

            Notification::make()
                ->title(__('Seat already mapped with active plan to another user !'))
                ->danger()
                ->body(__('Please select different seat or date as this seat is already occupied from :startDate to :endDate !', [
                    'startDate' => date('d/m/Y', strtotime($ifSubsExist['start_date'])),
                    'endDate' => date('d/m/Y', strtotime($ifSubsExist['end_date'])),
                ]))
                ->actions([
                    Action::make('view')
                        ->button()
                        ->color('success')
                        ->url(route(EditSubscription::getRouteName(), $ifSubsExist), shouldOpenInNewTab: true),
                ])
                ->color('info')
                ->send();

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        $data = $this->getRecord();
        User::first()->notify(
            Notification::make()
                ->success()
                ->icon('heroicon-o-bolt')
                ->actions([
                    Action::make('view')
                        ->size(ActionSize::ExtraSmall)
                        ->markAsRead()
                        ->url(route(ViewSubscription::getRouteName(), $this->getRecord())),
                ])
                ->title('A New Subscription added for '.$data->subscriber->name)
                ->body('This subscription added for Seat No.'.$data->seat->seat_no)
                ->toDatabase(),
        );
    }
}
