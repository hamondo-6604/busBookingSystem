<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\BusRoute;
use App\Models\DiscountType;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketBookingController extends Controller
{
    // ------------------------------------------------------------------
    // GET /ticket-booking  (initial load or from home search widget)
    // ------------------------------------------------------------------
    public function index()
    {
        return $this->renderSearch(request());
    }

    // ------------------------------------------------------------------
    // POST /ticket-booking  (search form submit)
    // ------------------------------------------------------------------
    public function search(Request $request)
    {
        $request->validate([
            'from'        => 'required|string',
            'to'          => 'required|string|different:from',
            'date'        => 'required|date',
            'trip_type'   => 'nullable|in:one_way,round_trip',
            'return_date' => 'nullable|date|after_or_equal:date',
        ]);

        return $this->renderSearch($request);
    }

    /**
     * Shared rendering for both GET (with query params) and POST (search submit).
     *
     * For round-trip flows, the page stays a single screen but knows which leg
     * it's currently selecting via the `leg` query parameter:
     *   - leg=outbound (default): show outbound trips for `from`/`to`/`date`
     *   - leg=return: show return trips for `to`/`from`/`return_date`
     */
    private function renderSearch(Request $request)
    {
        [$originCities, $destinationCities] = $this->dropdowns();

        $tripType   = $request->input('trip_type', 'one_way');
        $isRoundTrip = $tripType === 'round_trip';

        // Round-trip leg currently being selected.
        $leg = $request->input('leg', 'outbound');
        if (! in_array($leg, ['outbound', 'return'], true)) {
            $leg = 'outbound';
        }

        $from       = $request->input('from');
        $to         = $request->input('to');
        $date       = $request->input('date');
        $returnDate = $request->input('return_date');
        $outboundBookingId = $request->input('outbound_booking_id');

        // If we're looking at the return leg, swap from/to for the search.
        $searchFrom = $leg === 'return' ? $to : $from;
        $searchTo   = $leg === 'return' ? $from : $to;
        $searchDate = $leg === 'return' ? $returnDate : $date;

        // Auto-fill missing date with the nearest available trip on this route.
        if ($searchFrom && $searchTo && ! $searchDate) {
            $searchDate = $this->nearestTripDate($searchFrom, $searchTo);
        }

        $searchDate = $searchDate ?? today()->toDateString();

        $prefill = [
            'from'                => $from,
            'to'                  => $to,
            'date'                => $date ?? today()->toDateString(),
            'return_date'         => $returnDate,
            'trip_type'           => $tripType,
            'leg'                 => $leg,
            'outbound_booking_id' => $outboundBookingId,
            // Effective search params for the current leg (used by the result list).
            'search_from'         => $searchFrom,
            'search_to'           => $searchTo,
            'search_date'         => $searchDate,
        ];

        $trips            = collect();
        $alternativeDates = collect();
        $upcomingTrips    = collect();
        $outboundBooking  = null;

        // If we're in the middle of a round-trip flow, load the outbound booking
        // so we can show a "Step 1 selected" badge on the return-leg search.
        if ($isRoundTrip && $leg === 'return' && $outboundBookingId && auth()->check()) {
            $outboundBooking = Booking::with(['trip.route.originCity', 'trip.route.destinationCity', 'bookingSeats'])
                ->where('user_id', auth()->id())
                ->where('id', $outboundBookingId)
                ->first();
        }

        if ($searchFrom && $searchTo) {
            $trips = $this->searchTrips($searchFrom, $searchTo, $searchDate);

            if ($trips->isEmpty()) {
                $alternativeDates = $this->findAlternativeDates($searchFrom, $searchTo, $searchDate);
                $upcomingTrips    = $this->getUpcomingTripsForRoute($searchFrom, $searchTo, $searchDate);
            }
        }

        return view('pages.ticket_booking', compact(
            'originCities', 'destinationCities',
            'prefill', 'trips', 'alternativeDates', 'upcomingTrips',
            'outboundBooking'
        ));
    }

    // ------------------------------------------------------------------
    // PRIVATE HELPERS
    // ------------------------------------------------------------------

    /**
     * Search for trips matching the given from/to/date.
     */
    private function searchTrips(string $from, string $to, string $date)
    {
        return Trip::with([
            'route.originCity',
            'route.destinationCity',
            'bus.type',
            'bus.amenities',
            'departureTerminal',
            'bookings.bookingSeats',
        ])
        ->whereHas('route', fn ($q) =>
            $q->whereHas('originCity',      fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($from)]))
              ->whereHas('destinationCity', fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($to)]))
        )
        ->whereDate('trip_date', $date)
        ->where('status', 'scheduled')
        ->where('is_active', true)
        ->where('available_seats', '>', 0)
        ->orderBy('departure_time')
        ->get()
        ->filter(fn($trip) => $trip->available_seats > 0)
        ->values();
    }

    private function nearestTripDate(string $from, string $to): ?string
    {
        $trip = Trip::whereHas('route', fn ($q) =>
            $q->whereHas('originCity',      fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($from)]))
              ->whereHas('destinationCity', fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($to)]))
        )
        ->where('status', 'scheduled')
        ->where('is_active', true)
        ->where('available_seats', '>', 0)
        ->where('trip_date', '>=', today()->toDateString())
        ->orderBy('trip_date')
        ->orderBy('departure_time')
        ->first();

        return $trip?->trip_date?->toDateString();
    }

    private function findAlternativeDates(string $from, string $to, string $date): \Illuminate\Support\Collection
    {
        return Trip::whereHas('route', fn ($q) =>
            $q->whereHas('originCity',      fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($from)]))
              ->whereHas('destinationCity', fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($to)]))
        )
        ->where('status', 'scheduled')
        ->where('is_active', true)
        ->where('available_seats', '>', 0)
        ->where('trip_date', '>=', today()->toDateString())
        ->where('trip_date', '!=', $date)
        ->orderBy('trip_date')
        ->limit(5)
        ->pluck('trip_date')
        ->unique()
        ->values();
    }

    private function getUpcomingTripsForRoute(string $from, string $to, string $excludeDate): \Illuminate\Support\Collection
    {
        return Trip::with([
            'route.originCity',
            'route.destinationCity',
            'bus.type',
            'bus.amenities',
            'departureTerminal',
            'bookings.bookingSeats',
        ])
        ->whereHas('route', fn ($q) =>
            $q->whereHas('originCity',      fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($from)]))
              ->whereHas('destinationCity', fn ($c) => $c->whereRaw('LOWER(name) = ?', [strtolower($to)]))
        )
        ->where('status', 'scheduled')
        ->where('is_active', true)
        ->where('available_seats', '>', 0)
        ->where('trip_date', '>=', today()->toDateString())
        ->where('trip_date', '!=', $excludeDate)
        ->orderBy('trip_date')
        ->orderBy('departure_time')
        ->limit(20)
        ->get()
        ->filter(fn($trip) => $trip->available_seats > 0)
        ->groupBy('trip_date');
    }

    private function dropdowns(): array
    {
        $origin = BusRoute::with('originCity')->where('status', 'active')
            ->get()->pluck('originCity')->filter()->unique('id')->sortBy('name')->values();

        $dest = BusRoute::with('destinationCity')->where('status', 'active')
            ->get()->pluck('destinationCity')->filter()->unique('id')->sortBy('name')->values();

        return [$origin, $dest];
    }

    // ------------------------------------------------------------------
    // GET /select-seats/{trip_id} - Show seat selection page
    // ------------------------------------------------------------------
    public function selectSeats(Request $request, $trip_id)
    {
        $trip = Trip::with([
            'route.originCity',
            'route.destinationCity',
            'bus.type',
            'bus.seatLayout',
            'departureTerminal',
        ])->findOrFail($trip_id);

        $existingSeatsCount = BookingSeat::whereHas('booking', function ($q) use ($trip) {
            $q->where('trip_id', $trip->id)
              ->where('user_id', auth()->id())
              ->whereIn('status', ['pending', 'confirmed']);
        })->whereIn('status', ['reserved', 'confirmed'])->count();

        $maxAllowed = 5;
        $remainingAllowed = max(0, $maxAllowed - $existingSeatsCount);

        $seatMap = $this->generateSeatMap($trip);

        // Carry round-trip context across the selection step.
        $tripType   = $request->input('trip_type', 'one_way');
        $leg        = $request->input('leg', 'outbound');
        $returnDate = $request->input('return_date');
        $outboundBookingId = $request->input('outbound_booking_id');

        $outboundBooking = null;
        if ($tripType === 'round_trip' && $leg === 'return' && $outboundBookingId) {
            $outboundBooking = Booking::with(['trip.route.originCity', 'trip.route.destinationCity', 'bookingSeats'])
                ->where('user_id', auth()->id())
                ->where('id', $outboundBookingId)
                ->first();
        }

        $roundTripContext = [
            'trip_type'           => $tripType,
            'leg'                 => $leg,
            'return_date'         => $returnDate,
            'outbound_booking_id' => $outboundBookingId,
        ];

        return view('user.select-seats', compact(
            'trip', 'seatMap', 'remainingAllowed',
            'roundTripContext', 'outboundBooking'
        ));
    }

    // ------------------------------------------------------------------
    // POST /select-seats/{trip_id} - Handle seat selection and proceed
    // ------------------------------------------------------------------
    public function bookSeats(Request $request, $trip_id)
    {
        $request->validate([
            'selected_seats'      => 'required|array|min:1|max:5',
            'selected_seats.*'    => 'string',
            'trip_type'           => 'nullable|in:one_way,round_trip',
            'leg'                 => 'nullable|in:outbound,return',
            'return_date'         => 'nullable|date',
            'outbound_booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        $trip      = Trip::findOrFail($trip_id);
        $tripType  = $request->input('trip_type', 'one_way');
        $leg       = $request->input('leg', 'outbound');
        $isRoundTrip = $tripType === 'round_trip';
        $isReturnLeg = $isRoundTrip && $leg === 'return';

        // Per-trip 5-seat limit
        $existingSeatsCount = BookingSeat::whereHas('booking', function ($q) use ($trip) {
            $q->where('trip_id', $trip->id)
              ->where('user_id', auth()->id())
              ->whereIn('status', ['pending', 'confirmed']);
        })->whereIn('status', ['reserved', 'confirmed'])->count();

        $maxAllowed = 5;
        $remainingAllowed = max(0, $maxAllowed - $existingSeatsCount);

        if (count($request->selected_seats) > $remainingAllowed) {
            $msg = $existingSeatsCount > 0
                ? "You can only book a maximum of {$maxAllowed} seats per trip. You already have {$existingSeatsCount} seats booked."
                : "You can only book a maximum of {$maxAllowed} seats per transaction.";
            return redirect()->back()->withErrors(['error' => $msg]);
        }

        // Already-booked seat check
        $alreadyBooked = BookingSeat::whereHas('booking', function ($q) use ($trip) {
            $q->where('trip_id', $trip->id)
              ->whereIn('status', ['confirmed', 'pending']);
        })->whereIn('seat_number', $request->selected_seats)->exists();

        if ($alreadyBooked) {
            return redirect()->back()->withErrors(['error' => 'One or more of your selected seats are no longer available. Please try again.']);
        }

        // Round-trip: if this is the return leg, ensure seat count matches outbound.
        $outboundBooking = null;
        if ($isReturnLeg) {
            $outboundBooking = Booking::with('bookingSeats')
                ->where('user_id', auth()->id())
                ->where('id', $request->input('outbound_booking_id'))
                ->where('trip_type', 'round_trip')
                ->where('is_return_leg', false)
                ->whereNull('return_booking_id')
                ->where('status', 'pending')
                ->first();

            if (! $outboundBooking) {
                return redirect()->route('landing.ticket_booking')
                                 ->withErrors(['error' => 'We could not find your outbound booking. Please start over.']);
            }

            if (count($request->selected_seats) !== $outboundBooking->bookingSeats->count()) {
                return redirect()->back()->withErrors([
                    'error' => 'Please select the same number of seats as your outbound trip ('
                        . $outboundBooking->bookingSeats->count() . ').',
                ]);
            }
        }

        // Compute fares from the seat map
        $grid = $this->generateSeatMap($trip);
        $seatFares = [];
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                if (($cell['cell_type'] ?? '') === 'seat') {
                    $seatFares[$cell['seat_label']] = $cell['fare'] ?? $trip->fare;
                }
            }
        }

        $totalFare = 0;
        $bookingSeatsData = [];

        $seats = \App\Models\Seat::where('bus_id', $trip->bus_id ?? $trip->bus->id)
            ->whereIn('seat_number', $request->selected_seats)
            ->get()
            ->keyBy('seat_number');

        foreach ($request->selected_seats as $seatLabel) {
            $fare = $seatFares[$seatLabel] ?? $trip->fare;
            $totalFare += $fare;

            $seatModel = $seats->get($seatLabel);

            $bookingSeatsData[] = [
                'seat_id'      => $seatModel ? $seatModel->id : 0,
                'seat_type_id' => $seatModel ? $seatModel->seat_type_id : null,
                'seat_number'  => $seatLabel,
                'fare'         => $fare,
                'status'       => 'reserved',
            ];
        }

        $booking = DB::transaction(function () use (
            $trip, $totalFare, $bookingSeatsData,
            $isRoundTrip, $isReturnLeg, $outboundBooking
        ) {
            $newBooking = Booking::create([
                'user_id'        => auth()->id(),
                'trip_id'        => $trip->id,
                'trip_type'      => $isRoundTrip ? 'round_trip' : 'one_way',
                'is_return_leg'  => $isReturnLeg,
                'seat_id'        => $bookingSeatsData[0]['seat_id'] ?? null,
                'status'         => 'pending',
                'base_fare'      => $totalFare,
                'amount_paid'    => 0,
                'payment_status' => 'unpaid',
            ]);

            foreach ($bookingSeatsData as $data) {
                $data['booking_id'] = $newBooking->id;
                BookingSeat::create($data);
            }

            // Link the return leg back to the outbound booking.
            if ($isReturnLeg && $outboundBooking) {
                $outboundBooking->update(['return_booking_id' => $newBooking->id]);
            }

            return $newBooking;
        });

        // ----- Decide where to go next -----

        // Round-trip + just finished outbound seats -> go pick the return trip.
        if ($isRoundTrip && ! $isReturnLeg) {
            return redirect()->route('landing.ticket_booking', [
                'from'                => request('from'),
                'to'                  => request('to'),
                'date'                => request('date'),
                'return_date'         => request('return_date'),
                'trip_type'           => 'round_trip',
                'leg'                 => 'return',
                'outbound_booking_id' => $booking->id,
            ])->with('success', 'Outbound seats reserved. Now select your return trip.');
        }

        // Round-trip + just finished return seats -> go to passenger details
        // for the OUTBOUND booking (which now has the return linked).
        if ($isReturnLeg && $outboundBooking) {
            return redirect()->route('user.booking.details', $outboundBooking->id)
                             ->with('success', 'Both legs reserved. Please enter passenger details.');
        }

        // One-way (existing behavior)
        return redirect()->route('user.booking.details', $booking->id)
                         ->with('success', 'Seats successfully reserved. Please enter passenger details.');
    }

    // ------------------------------------------------------------------
    // GET /select-seats/{trip_id}/location
    // ------------------------------------------------------------------
    public function tripLocation($trip_id)
    {
        $trip = Trip::select(['id', 'current_lat', 'current_lng', 'last_location_updated_at'])
            ->findOrFail($trip_id);

        $hasLocation = !is_null($trip->current_lat) && !is_null($trip->current_lng);

        return response()->json([
            'trip_id'         => $trip->id,
            'has_location'    => $hasLocation,
            'lat'             => $hasLocation ? (float) $trip->current_lat : null,
            'lng'             => $hasLocation ? (float) $trip->current_lng : null,
            'last_updated_at' => $trip->last_location_updated_at?->toIso8601String(),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /booking/{booking_id}/details
    // ------------------------------------------------------------------
    public function passengerDetails($booking_id)
    {
        $booking = Booking::with([
            'bookingSeats',
            'trip.route.originCity',
            'trip.route.destinationCity',
            'trip.bus',
            'returnBooking.bookingSeats',
            'returnBooking.trip.route.originCity',
            'returnBooking.trip.route.destinationCity',
            'returnBooking.trip.bus',
        ])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($booking_id);

        // If a user lands on the return-leg URL directly, redirect them to the
        // outbound leg so the combined details view is shown.
        if ($booking->is_return_leg) {
            $outbound = $booking->outboundBooking()->first();
            if ($outbound) {
                return redirect()->route('user.booking.details', $outbound->id);
            }
        }

        $discountTypes = DiscountType::active()->get();

        return view('user.passenger-details', compact('booking', 'discountTypes'));
    }

    // ------------------------------------------------------------------
    // POST /booking/{booking_id}/details
    // ------------------------------------------------------------------
    public function storePassengerDetails(Request $request, $booking_id)
    {
        $booking = Booking::with(['bookingSeats', 'returnBooking.bookingSeats'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($booking_id);

        $request->validate([
            'passengers'                       => 'required|array',
            'passengers.*.name'                => 'required|string|max:255',
            'passengers.*.discount_type_id'    => 'nullable|exists:discount_types,id',
        ]);

        DB::transaction(function () use ($request, $booking) {
            $this->savePassengersForBooking($request, $booking);

            if ($booking->returnBooking) {
                $this->savePassengersForBooking($request, $booking->returnBooking);
            }
        });

        return redirect()->route('user.booking.checkout', $booking->id)
                         ->with('success', 'Passenger details saved. Please proceed to checkout.');
    }

    /**
     * Apply the submitted passenger payload to a single Booking's seats.
     * Mutates passenger_name / passenger_type and recomputes discount_amount.
     */
    private function savePassengersForBooking(Request $request, Booking $booking): void
    {
        $totalDiscount = 0;

        foreach ($booking->bookingSeats as $seat) {
            $data = $request->passengers[$seat->id] ?? null;
            if (! $data) {
                continue;
            }

            $seat->passenger_name = $data['name'];

            if (! empty($data['discount_type_id'])) {
                $discount = DiscountType::find($data['discount_type_id']);
                if ($discount) {
                    $seat->passenger_type = $discount->name;
                    $totalDiscount       += $discount->discountAmount((float) $seat->fare);
                }
            } else {
                $seat->passenger_type = 'regular';
            }

            $seat->save();
        }

        $booking->discount_amount = $totalDiscount;
        $booking->save();
    }

    // ------------------------------------------------------------------
    // GET /booking/{booking_id}/checkout
    // ------------------------------------------------------------------
    public function checkout($booking_id)
    {
        $booking = Booking::with([
            'bookingSeats',
            'trip.route.originCity',
            'trip.route.destinationCity',
            'trip.bus',
            'returnBooking.bookingSeats',
            'returnBooking.trip.route.originCity',
            'returnBooking.trip.route.destinationCity',
            'returnBooking.trip.bus',
        ])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($booking_id);

        if ($booking->is_return_leg) {
            $outbound = $booking->outboundBooking()->first();
            if ($outbound) {
                return redirect()->route('user.booking.checkout', $outbound->id);
            }
        }

        return view('user.checkout', compact('booking'));
    }

    // ------------------------------------------------------------------
    // POST /booking/{booking_id}/pay
    // ------------------------------------------------------------------
    public function processPayment(Request $request, $booking_id)
    {
        $booking = Booking::with('returnBooking')
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($booking_id);

        $request->validate([
            'payment_method' => 'required|in:credit_card,gcash,paymaya,otc',
        ]);

        $legs = collect([$booking])
            ->when($booking->returnBooking, fn ($c) => $c->push($booking->returnBooking));

        $totalAmount = $legs->sum(
            fn (Booking $leg) => (float) $leg->base_fare - (float) $leg->discount_amount
        );

        DB::transaction(function () use ($legs, $request, $totalAmount, $booking) {
            // Single Payment row attached to the outbound (primary) booking
            // covering the combined amount.
            Payment::create([
                'booking_id'       => $booking->id,
                'amount'           => $totalAmount,
                'payment_method'   => $request->payment_method,
                'status'           => 'paid',
                'transaction_id'   => 'SIM-' . strtoupper(uniqid()),
                'currency'         => 'PHP',
                'paid_at'          => now(),
                'gateway_response' => ['simulated' => true],
            ]);

            foreach ($legs as $leg) {
                $legAmount = (float) $leg->base_fare - (float) $leg->discount_amount;

                $leg->update([
                    'status'         => 'confirmed',
                    'payment_status' => 'paid',
                    'amount_paid'    => $legAmount,
                ]);

                BookingSeat::where('booking_id', $leg->id)->update(['status' => 'confirmed']);
            }
        });

        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        $seatCount = $booking->bookingSeats()->count()
                   + ($booking->returnBooking?->bookingSeats()->count() ?? 0);

        $tripDescription = $booking->trip->route->originCity->name . ' to ' . $booking->trip->route->destinationCity->name;
        if ($booking->returnBooking) {
            $tripDescription .= ' (round-trip)';
        }

        foreach ($admins as $admin) {
            Notification::create([
                'user_id'         => $admin->id,
                'title'           => 'New Booking #' . $booking->booking_reference,
                'message'         => auth()->user()->name . " booked $seatCount seat(s) on $tripDescription.",
                'type'            => 'booking_confirmed',
                'notifiable_type' => Booking::class,
                'notifiable_id'   => $booking->id,
                'is_read'         => false,
            ]);
        }

        return redirect()->route('user.booking.success', $booking->id);
    }

    // ------------------------------------------------------------------
    // GET /booking/{booking_id}/success
    // ------------------------------------------------------------------
    public function bookingSuccess($booking_id)
    {
        $booking = Booking::with([
            'bookingSeats',
            'trip.route.originCity',
            'trip.route.destinationCity',
            'trip.bus',
            'returnBooking.bookingSeats',
            'returnBooking.trip.route.originCity',
            'returnBooking.trip.route.destinationCity',
            'returnBooking.trip.bus',
        ])
            ->where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->findOrFail($booking_id);

        if ($booking->is_return_leg) {
            $outbound = $booking->outboundBooking()->first();
            if ($outbound) {
                return redirect()->route('user.booking.success', $outbound->id);
            }
        }

        return view('user.booking-success', compact('booking'));
    }

    /**
     * Generate the seat map array with availability status for the given trip.
     */
    private function generateSeatMap(Trip $trip): array
    {
        if (!$trip->bus || !$trip->bus->seatLayout) {
            return [];
        }

        $userId = auth()->id();

        $bookedSeatsData = BookingSeat::whereHas('booking', function ($q) use ($trip) {
            $q->where('trip_id', $trip->id)
              ->whereIn('status', ['confirmed', 'pending']);
        })->with('booking')->get();

        $bookedSeats = $bookedSeatsData->pluck('seat_number')->toArray();

        $ownBookedSeats = [];
        if ($userId) {
            $ownBookedSeats = $bookedSeatsData->filter(function ($seat) use ($userId) {
                return $seat->booking->user_id === $userId;
            })->pluck('seat_number')->toArray();
        }

        $layoutGrid = $trip->bus->seatLayout->buildGrid();
        $baseFare = (float) $trip->fare;

        $enhancedGrid = [];
        foreach ($layoutGrid as $rowIdx => $row) {
            $enhancedRow = [];
            foreach ($row as $cell) {
                $cellData = is_array($cell) ? $cell : $cell->toArray();

                if (($cellData['cell_type'] ?? '') === 'seat' && ($cellData['is_bookable'] ?? false)) {
                    $seatLabel = $cellData['seat_label'] ?? '';
                    $cellData['is_available']    = !in_array($seatLabel, $bookedSeats);
                    $cellData['is_own_booking']  = in_array($seatLabel, $ownBookedSeats);

                    $fare = $baseFare;
                    if (!empty($cellData['seat_type_id'])) {
                        $seatType = \App\Models\SeatType::find($cellData['seat_type_id']);
                        if ($seatType) {
                            $fare = $seatType->calculateFare($baseFare);
                        }
                    }
                    $cellData['fare'] = $fare;
                }

                $enhancedRow[] = $cellData;
            }
            $enhancedGrid[] = $enhancedRow;
        }

        return $enhancedGrid;
    }
}
