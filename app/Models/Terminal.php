<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Terminal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'city_id',
        'address',
        'latitude',
        'longitude',
        'contact_number',
        'email',
        'opening_time',
        'closing_time',
        'status',
        'description',
        'photo',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ------------------------------------------------------------------
    // RELATIONSHIPS
    // ------------------------------------------------------------------

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** Routes that originate from this terminal. */
    public function originRoutes(): HasMany
    {
        return $this->hasMany(BusRoute::class, 'origin_terminal_id');
    }

    /** Routes that terminate at this terminal. */
    public function destinationRoutes(): HasMany
    {
        return $this->hasMany(BusRoute::class, 'destination_terminal_id');
    }

    /** Trips departing from this terminal. */
    public function departingTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'departure_terminal_id');
    }

    /** Trips arriving at this terminal. */
    public function arrivingTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'arrival_terminal_id');
    }

    /** Stops that are physically at this terminal. */
    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class);
    }

    // ------------------------------------------------------------------
    // ACCESSORS
    // ------------------------------------------------------------------

    public function getFullAddressAttribute(): string
    {
        return trim(implode(', ', array_filter([
            $this->name,
            $this->address,
            $this->city?->name,
        ])));
    }

    public function isOpenNow(): bool
    {
        if (! $this->opening_time || ! $this->closing_time) {
            return true; // assume 24/7 if not set
        }

        $now = now()->format('H:i:s');
        return $now >= $this->opening_time && $now <= $this->closing_time;
    }

    // ------------------------------------------------------------------
    // SCOPES
    // ------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }
}