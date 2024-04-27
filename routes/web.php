<?php

use App\Models\Seat;
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

    $data = [
        'start_date' => '2024-05-08',
        'end_date' => '',
    ];

    return $x = Seat::whereDoesntHave('subscription', function ($q) use ($data) {
        return $q->where('status', 'active')->where('end_date', '>=', $data['start_date']);
        // ->where('start_date', '<=', $data['end_date']);
    })->get();
});
