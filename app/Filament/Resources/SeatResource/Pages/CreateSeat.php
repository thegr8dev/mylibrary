<?php

namespace App\Filament\Resources\SeatResource\Pages;

use App\Filament\Resources\SeatResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateSeat extends CreateRecord
{
    protected static string $resource = SeatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Success !')
            ->body('Seat has been created !');
    }

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
