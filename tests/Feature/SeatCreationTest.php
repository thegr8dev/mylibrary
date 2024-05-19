<?php

use App\Filament\Resources\SeatResource;
use App\Filament\Resources\SeatResource\Pages\CreateSeat;
use App\Filament\Resources\SeatResource\Pages\ListSeats;
use App\Models\Seat;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});


it('can render index page', function () {
    $this->get(SeatResource::getUrl('index'))->assertSuccessful();
});

it('can list seats', function () {
    $seats = Seat::factory()->count(10)->create();

    livewire(ListSeats::class)
        ->assertCanSeeTableRecords($seats);
});

// it('can create', function () {

//     $user = User::factory()->create();
//     $this->actingAs($user);

//     $newData = Seat::factory()->make();

//     $component = Livewire::test(CreateSeat::class)
//         ->fillForm([
//             'seat_no' => $newData->seat_no,
//         ])
//         ->call('create');


//     $component->assert();

//     $this->assertDatabaseHas(Seat::class, [
//         'seat_no' => $newData->seat_no,
//     ]);
// });


it('can validate input', function () {
    livewire(CreateSeat::class)
        ->fillForm([
            'seat_no' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['seat_no' => 'required']);
});
