<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class BusRoute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'routes';

    public const SERVICE_NON_STOP = 'non_stop';
    public const SERVICE_EXPRESS  = 'express';
    public const SERVICE_REGULAR  = 'regular';

    protected $fillable = [
        'route_name',
        'origin_city_id',
        'destination_city_id',
        'origin_terminal_id',
        'destination_terminal_id',
        'service_type',
        'distance_km',
        'estimated_duration_minutes',
        'status',
        'description',
    ];

    // ------------------------------------------------------------------
    // RELATIONSHIPS
    // ------------------------------------------------------------------

    public function originCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function destinationTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destination_terminal_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'route_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'route_id');
    }

    /**
     * Intermediate stops along this route, ordered by stop_order.
     */
    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class, 'route_stops', 'route_id', 'stop_id')
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
    // ACCESSORS
    // ------------------------------------------------------------------

    public function getFullRouteNameAttribute(): string
    {
        return ($this->originCity?->name ?? '?') . ' → ' . ($this->destinationCity?->name ?? '?');
    }

    /**
     * e.g. "Cubao Terminal → Session Road Terminal"
     */
    public function getTerminalRouteNameAttribute(): string
    {
        $origin = $this->originTerminal?->name ?? $this->originCity?->name ?? '?';
        $dest   = $this->destinationTerminal?->name ?? $this->destinationCity?->name ?? '?';
        return $origin . ' → ' . $dest;
    }

    // ------------------------------------------------------------------
    // SCOPES
    // ------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeServiceType($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_NON_STOP => 'Non-Stop',
            self::SERVICE_EXPRESS  => 'Express',
            self::SERVICE_REGULAR  => 'Regular',
            default                => ucfirst((string) $this->service_type),
        };
    }
}