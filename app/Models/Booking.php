<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'trip_id',
        'trip_type',         // 'one_way' or 'round_trip'
        'return_booking_id', // outbound booking points at its return leg
        'is_return_leg',     // true on the return-leg row of a round-trip pair
        'seat_id',          // primary seat (kept for BC) — full list is in booking_seats
        'promotion_id',
        'seat_number',
        'status',
        'base_fare',
        'discount_amount',
        'amount_paid',
        'payment_status',
        'booking_reference',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'cancelled_at'    => 'datetime',
        'base_fare'       => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'is_return_leg'   => 'boolean',
    ];

    // ------------------------------------------------------------------
    // BOOT
    // ------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BKG-' . strtoupper(Str::random(8));
            }
        });
    }

    // ------------------------------------------------------------------
    // RELATIONSHIPS
    // ------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /** Primary seat (single-seat bookings / backwards compatibility). */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * All seats in this booking (supports group/multi-seat bookings).
     */
    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /**
     * The return-leg booking (set only on outbound bookings of round-trips).
     */
    public function returnBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'return_booking_id');
    }

    /**
     * The outbound booking that points at this booking as its return leg.
     * Useful when you only have the return-leg booking in hand.
     */
    public function outboundBooking(): HasOne
    {
        return $this->hasOne(Booking::class, 'return_booking_id');
    }

    // ------------------------------------------------------------------
    // CONVENIENCE ACCESSORS
    // ------------------------------------------------------------------

    public function getBusAttribute(): ?Bus
    {
        return $this->trip?->bus;
    }

    public function getRouteAttribute(): ?BusRoute
    {
        return $this->trip?->route;
    }

    public function getEffectiveSeatTypeAttribute(): string
    {
        return $this->seat?->effectiveSeatType?->name
            ?? $this->seat?->seat_type
            ?? $this->bus?->default_seat_type
            ?? 'economy';
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return '₱' . number_format($this->amount_paid, 2);
    }

    public function getFinalFareAttribute(): float
    {
        return (float) $this->base_fare - (float) $this->discount_amount;
    }

    /**
     * Total number of seats in this booking (1 for single, N for group).
     */
    public function getSeatCountAttribute(): int
    {
        $count = $this->bookingSeats()->count();
        return $count > 0 ? $count : 1;
    }

    /**
     * Comma-separated seat numbers for display: "3A, 3B, 3C"
     */
    public function getSeatListAttribute(): string
    {
        $seats = $this->bookingSeats()->pluck('seat_number');
        return $seats->isNotEmpty()
            ? $seats->join(', ')
            : ($this->seat_number ?? '—');
    }

    // ------------------------------------------------------------------
    // QUERY SCOPES
    // ------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // ------------------------------------------------------------------
    // ROUND-TRIP HELPERS
    // ------------------------------------------------------------------

    /**
     * Whether this booking is part of a round-trip pair (either leg).
     */
    public function getIsRoundTripAttribute(): bool
    {
        return $this->trip_type === 'round_trip';
    }

    /**
     * Whether this booking is the outbound leg of a round-trip pair.
     */
    public function isOutboundLeg(): bool
    {
        return $this->trip_type === 'round_trip' && ! $this->is_return_leg;
    }

    /**
     * Resolve the outbound ("primary") booking row for a round-trip pair.
     * Returns $this for one-way bookings or the outbound leg of a round-trip.
     */
    public function primaryLeg(): self
    {
        if ($this->is_return_leg) {
            return $this->outboundBooking()->first() ?? $this;
        }

        return $this;
    }

    /**
     * Convenience: get the partner leg (return for outbound, outbound for return).
     * Returns null for one-way bookings.
     */
    public function partnerLeg(): ?self
    {
        if (! $this->is_round_trip) {
            return null;
        }

        return $this->is_return_leg
            ? $this->outboundBooking()->first()
            : $this->returnBooking;
    }
}