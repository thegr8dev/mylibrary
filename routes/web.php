<?php


use App\Enums\SiteColors;
use App\Settings\SiteSettings;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Route;
use App\Mapper\ColorMapper;

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
    $name = 'Blue';
    return constant("Filament\Support\Colors\Color::" . $name);
});
