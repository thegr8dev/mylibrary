<?php

use App\Filament\Resources\SeatResource;
use App\Filament\Resources\SeatResource\Pages\CreateSeat;
use App\Filament\Resources\SeatResource\Pages\EditSeat;
use App\Filament\Resources\SeatResource\Pages\ListSeats;
use App\Models\Seat;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->actingsAs(User::factory()->create());
});

it('can render index page', function () {
    $this->get(SeatResource::getUrl('index'))->assertSuccessful();
});

it('can list seats', function () {
    $seats = Seat::factory()->count(10)->create();

    livewire(ListSeats::class)
        ->assertCanSeeTableRecords($seats);
});

it('can validate input', function () {
    livewire(CreateSeat::class)
        ->fillForm([
            'seat_no' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['seat_no' => 'required']);
});

it('can create seat', function () {

    $seat = Seat::factory()->make();

    livewire(CreateSeat::class)
        ->fillForm([
            'seat_no' => (string) $seat->seat_no,
            'note' => $seat->note,
            'status' => $seat->status,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Seat::class, [
        'seat_no' => $seat->seat_no,
        'note' => $seat->note,
        'status' => $seat->status,
    ]);
});

it('can render edit seat page', function () {
    $this->get(SeatResource::getUrl('edit', [
        'record' => Seat::factory()->create(),
    ]))->assertSuccessful();
});

it('can save seat', function () {
    $seat = Seat::factory()->create();
    $newData = Seat::factory()->make();

    livewire(EditSeat::class, [
        'record' => (int) $seat->getRouteKey(),
    ])
        ->fillForm([
            'seat_no' => (string) $newData->seat_no,
            'note' => $newData->note,
            'status' => $newData->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($seat->refresh())
        ->seat_no->toBe((string) $newData->seat_no)
        ->note->toBe($newData->note)
        ->status->toBe($newData->status);
});

it('can retrieve data', function () {
    $seat = Seat::factory()->create();

    livewire(EditSeat::class, ['record' => (int) $seat->getRouteKey()])
        ->assertFormSet([
            'seat_no' => $seat->seat_no,
            'note' => $seat->note,
            'status' => $seat->status,
        ]);
});

it('can delete', function () {
    $seat = Seat::factory()->create();

    livewire(EditSeat::class, [
        'record' => $seat->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($seat);
});
