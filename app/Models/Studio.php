<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(["name", "description", "price_per_hour", "has_piano", "is_active"])]
class Studio extends Model {
    use HasFactory;

    protected function casts(): array
    {
        return [
            'has_piano'=>'boolean',
            'is_active'=>'boolean',
            'price_per_hour' => 'decimal:2',
        ];
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

