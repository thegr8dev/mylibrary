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

    public function scopeActive($query, $startDate, $endDate = null)
    {

        return $query->where('status', 'active')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->when($endDate, function ($query) use ($startDate, $endDate) {

                    return $query->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '>=', $startDate)
                            ->when($endDate, function ($q, $date) {
                                $q->where('start_date', '<=', $date);
                            });
                    })->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('end_date', '>=', $startDate)
                            ->when($endDate, function ($q, $date) {
                                $q->where('end_date', '<=', $date);
                            });
                    })->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<', $startDate)
                            ->when($endDate, function ($q, $date) {
                                $q->where('end_date', '>', $date);
                            });
                    })->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '>=', $startDate)
                            ->when($endDate, function ($q, $date) {
                                return $q->where('end_date', '<=', $date);
                            });
                    });
                })->when(is_null($endDate), function ($query) use ($startDate) {
                    $query->where(function ($query) use ($startDate) {
                        $query->where('end_date', '>=', $startDate)
                            ->where('start_date', '<=', $startDate);
                    });
                });
            });
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $sub) {
            $sub->uuid = \Str::orderedUuid();
            $sub->txn_id = is_null($sub->txn_id) ? strtoupper(\Str::random(8)) : $sub->txn_id;
        });

        static::updating(function (Subscription $sub) {
            $sub->txn_id = is_null($sub->txn_id) ? strtoupper(\Str::random(8)) : $sub->txn_id;
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
