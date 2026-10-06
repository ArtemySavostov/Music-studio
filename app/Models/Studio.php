<?php

namespace App\Models;

use Database\Factories\StudioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property string $price_per_hour */
#[Fillable(['name', 'description', 'price_per_hour', 'has_piano', 'is_active'])]
class Studio extends Model
{
    /** @use HasFactory<StudioFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'has_piano' => 'boolean',
            'is_active' => 'boolean',
            'price_per_hour' => 'decimal:2',
        ];
    }

    /** @return HasMany<Booking, $this> */
    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function priceForHours(int $hours): string
    {
        [$rubles, $kopecks] = explode('.', $this->price_per_hour);
        $total = ((int) $rubles * 100 + (int) $kopecks) * $hours;

        return intdiv($total, 100).'.'.str_pad((string) ($total % 100), 2, '0', STR_PAD_LEFT);
    }
}
