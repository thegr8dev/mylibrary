<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->actingAs(User::factory()->create());
});

it('can render user index page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can list users', function () {
    $seats = User::factory()->count(10)->create();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($seats);
});

it('can validate input', function () {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'email' => 'required']);
});

it('can create user', function () {

    $user = User::factory()->make();

    livewire(CreateUser::class)
        ->fillForm([
            'name' => (string) $user->name,
            'email' => $user->email,
            'phone_no' => 1234567890,
            'password' => 12345678,
            'status' => (bool) $user->status,
            'password_confirmation' => 12345678,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name' => (string) $user->name,
        'email' => $user->email,
        'status' => $user->status,
        'phone_no' => 1234567890,
    ]);
});

it('can render edit user page', function () {
    $this->get(UserResource::getUrl('edit', [
        'record' => User::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertFormSet([
            'name' => (string) $user->name,
            'email' => $user->email,
            'phone_no' => $user->phone_no,
            'status' => $user->status,
        ]);
});

it('can save user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'name' => (string) $newData->name,
            'email' => $newData->email,
            'status' => $newData->status,
            'phone_no' => 1234567890,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->name->toBe((string) $newData->name)
        ->email->toBe($newData->email)
        ->phone_no->toBe('1234567890')
        ->status->toBe((int) $newData->status);
});

it('can delete', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($user);
});

// it('can not delete seat', function () {
//     $post = Seat::factory()->create();

//     livewire(EditSeat::class, [
//         'record' => $post->getRouteKey(),
//     ])->assertActionHidden(DeleteAction::class);
// });
