<?php

use App\Models\Seat;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Seat::class);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('status');
            $table->double('amount');
            $table->string('txn_id')->nullable();
            $table->string('payment_method');
            $table->string('payment_proof')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
