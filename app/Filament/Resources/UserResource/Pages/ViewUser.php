<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getFooter(): ?View
    {
        return view('filament.footer');
    }
}
