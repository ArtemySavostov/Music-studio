<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(["user_id", "studio_id", "starts_at", "ends_at", "total_price", "status"])]
class Booking extends Model
{
    use HasFactory;


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    protected function casts(): array
    {
        return [
            'starts_at'=>'datetime',
            'ends_at'=>'datetime',
            'total_price'=>'decimal:2',
        ];
    }
}

