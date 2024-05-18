<?php

namespace Database\Factories;

use App\Models\Seat;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a start date and ensure the end date is after the start date
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d').' 00:00:00';
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month')->format('Y-m-d').' 00:00:00';

        $statuses = ['active', 'deactive', 'cancelled', 'expired'];
        $paymentMethods = ['cash', 'online'];
        // Find a user without an active subscription
        $userId = User::whereDoesntHave('subscription', function ($query) use ($startDate, $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            });
        })->inRandomOrder()->first()->id;

        // Find a seat that is available for the generated start and end dates
        $seatId = Seat::whereDoesntHave('subscription', function ($query) use ($startDate, $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            });
        })->inRandomOrder()->first()->id;

        return [
            'user_id' => $userId,
            'seat_id' => $seatId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement($statuses),
            'txn_id' => \Str::random(8),
            'amount' => $this->faker->numberBetween(100, 5000),
            'payment_method' => $this->faker->randomElement($paymentMethods),
        ];
    }
}
