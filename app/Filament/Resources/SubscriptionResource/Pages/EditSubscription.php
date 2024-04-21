<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Subscription updated';
    }

    protected function beforeSave(): void
    {
        $data = $this->data;

        if (! in_array($this->data['status'], ['active', 'upcoming'])) {
            return;
        }

        /** Check if user has already have active subscription */
        $ifUserHasActiveSub = Subscription::where('user_id', $data['user_id'])->where('status', 'active')->where('id', '!=', $data['id'])->first();

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
            ->when($data['id'], function ($q) use ($data) {
                return $q->where('id', '!=', $data['id']);
            })
            ->first();

        if ($ifSubsExist) {

            Notification::make()
                ->title(__('Seat already mapped with active plan to another user !'))
                ->danger()
                ->body(__('Please select different seat or date as this seat is already occupied from :startDate to :endDate !', [
                    'startDate' => date('d/m/Y', strtotime($ifSubsExist['start_date'])),
                    'endDate' => date('d/m/Y', strtotime($ifSubsExist['end_date'])),
                ]))
                ->color('info')
                ->actions([
                    Action::make('view')
                        ->button()
                        ->color('success')
                        ->url(route(EditSubscription::getRouteName(), $ifSubsExist), shouldOpenInNewTab: true),
                ])
                ->send();

            $this->halt();
        }
    }
}
