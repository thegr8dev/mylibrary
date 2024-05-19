<?php

use App\Filament\Resources\SeatResource\Pages\EditSeat;
use App\Models\Seat;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect(route('filament.admin.auth.login'));
});

Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::get('/test', function () {

    $record = Seat::factory()->create();


    dd(\Livewire\Livewire::test(EditSeat::class, [
        'record' => $record,
    ]));
});
