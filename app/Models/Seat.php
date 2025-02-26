<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = ['seat_no', 'status', 'note'];

    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'seat_id');
    }
}
