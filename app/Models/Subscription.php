<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query, $startDate, $endDate)
    {
        return $query->where('status', 'active')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '>=', $startDate)
                        ->where('start_date', '<=', $endDate);
                })->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('end_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                })->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<', $startDate)
                        ->where('end_date', '>', $endDate);
                })
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '>=', $startDate)
                            ->where('end_date', '<=', $endDate);
                    });
            });
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $sub) {
            $sub->uuid = \Str::orderedUuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
