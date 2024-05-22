<?php

use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\SubscriptionResource\Pages\CreateSubscription;
use App\Filament\Resources\SubscriptionResource\Pages\EditSubscription;
use App\Filament\Resources\SubscriptionResource\Pages\ListSubscriptions;
use App\Models\Seat;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render subscriptions page', function () {
    $this->get(SubscriptionResource::getUrl('index'))->assertSuccessful();
});

it('can list subscriptions', function () {
    $subscriptions = Subscription::factory()->count(10)->create();

    livewire(ListSubscriptions::class)
        ->assertCanSeeTableRecords($subscriptions);
});

it('can validate input', function () {
    livewire(CreateSubscription::class)
        ->fillForm([
            'seat_id' => null,
            'start_date' => null,
            'end_date' => null,
            'amount' => null,
            'status' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'seat_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ]);
});

it('can create subscription', function () {

    $subscription = Subscription::factory()->make();
   
    livewire(CreateSubscription::class)
        ->fillForm([
            'user_id' => $subscription->user_id,
            'seat_id' => $subscription->seat_id,
            'start_date' => \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d'),
            'amount' => $subscription->amount,
            'status' => $subscription->status,
            'payment_method' => $subscription->payment_method,
            'txn_id' => $subscription->txn_id
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Subscription::class, [
        'user_id' => $subscription->user_id,
        'seat_id' => $subscription->seat_id,
        'start_date' => \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d'),
        'end_date' => \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d') ,
        'amount' => $subscription->amount,
        'status' => $subscription->status,
        'payment_method' => $subscription->payment_method,
        'txn_id' => $subscription->txn_id
    ]);
});

it('can render edit subscription page', function () {
    $this->get(SubscriptionResource::getUrl('edit', [
        'record' => Subscription::factory()->create(),
    ]))->assertSuccessful();
});

it('can save subscription', function () {
    $oldSubsription = Subscription::factory()->create();
    $newData = Subscription::factory()->make();
    
    livewire(EditSubscription::class, [
        'record' => $oldSubsription->getRouteKey(),
    ])
        ->fillForm([
            'user_id' => $newData->user_id,
            'seat_id' => $newData->seat_id,
            'start_date' => $newData->start_date,
            'end_date' => $newData->end_date,
            'amount' => $newData->amount,
            'status' => $newData->status,
            'payment_method' => $newData->payment_method,
            'txn_id' => $newData->txn_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($oldSubsription->refresh())
        ->user_id->toBe($newData->user_id)
        ->seat_id->toBe($newData->seat_id)
        ->start_date->toBe(\Carbon\Carbon::parse($newData->start_date)->format('Y-m-d'))
        ->end_date->toBe(\Carbon\Carbon::parse($newData->end_date)->format('Y-m-d'))
        ->amount->toBe((double) $newData->amount)
        ->status->toBe($newData->status)
        ->payment_method->toBe($newData->payment_method)
        ->txn_id->toBe($newData->txn_id);
});

it('can retrieve data', function () {
    $subscription = Subscription::factory()->create();

    livewire(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->assertFormSet([
            'user_id' => $subscription->user_id,
            'seat_id' => $subscription->seat_id,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'amount' => $subscription->amount,
            'status' => $subscription->status,
            'payment_method' => $subscription->payment_method,
            'txn_id' => $subscription->txn_id,
        ]);
});

it('can delete', function () {
    $subscription = Subscription::factory()->create();

    livewire(EditSubscription::class, [
        'record' => $subscription->getRouteKey(),
    ])->callAction(DeleteAction::class);

    $this->assertModelMissing($subscription);
});