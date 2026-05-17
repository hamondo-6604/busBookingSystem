<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'city_id',
        'terminal_id',
        'address',
        'latitude',
        'longitude',
        'type',
        'status',
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

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    /**
     * Routes that pass through this stop.
     */
    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(BusRoute::class, 'route_stops', 'stop_id', 'route_id')
                    ->withPivot([
                        'stop_order',
                        'minutes_from_origin',
                        'fare_from_origin',
                        'allows_boarding',
                        'allows_alighting',
                    ])
                    ->orderByPivot('stop_order');
    }

    // ------------------------------------------------------------------
    // SCOPES
    // ------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBoardable($query)
    {
        return $query->whereHas('routes', fn ($q) =>
            $q->where('route_stops.allows_boarding', true)
        );
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'terminal'  => 'Terminal',
            'barangay'  => 'Barangay',
            'pickup'    => 'Pickup',
            'dropoff'   => 'Drop-off',
            'waypoint'  => 'Waypoint',
            default     => ucfirst((string) $this->type),
        };
    }

    public function getLocationLabelAttribute(): string
    {
        $parts = array_filter([
            $this->city?->name,
            $this->address,
        ]);

        return $parts ? implode(' · ', $parts) : 'No location set';
    }
}