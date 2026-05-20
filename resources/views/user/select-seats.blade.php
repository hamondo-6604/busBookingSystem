@extends('layouts.app')
@section('title', 'Select Your Seat — Mindanao Express')

@push('head')
<style>
    .bus-container {
        border: 4px solid #cbd5e1;
        border-radius: 40px;
        padding: 40px 20px;
        background: #f8fafc;
        position: relative;
        max-width: max-content;
        margin: 0 auto;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
    }
    
    .bus-front {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 40px;
        background: #cbd5e1;
        border-radius: 50px 50px 0 0;
    }
    
    .bus-steering {
        position: absolute;
        top: 20px;
        left: 30px;
        width: 40px;
        height: 40px;
        border: 4px solid #64748b;
        border-radius: 50%;
    }

    .seat {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-weight: bold;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        user-select: none;
    }

    .seat::before {
        content: '';
        position: absolute;
        top: -4px;
        width: 32px;
        height: 8px;
        border-radius: 4px;
        background: inherit;
        filter: brightness(0.9);
    }

    .seat.available {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        color: #64748b;
    }

    .seat.available:hover {
        border-color: #f97316;
        color: #f97316;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.1);
    }

    .seat.selected {
        background: #f97316;
        border: 2px solid #ea580c;
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3);
    }
    .seat.selected::before { filter: brightness(1.1); }

    .seat.occupied {
        background: #e2e8f0;
        border: 2px solid #cbd5e1;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .seat.occupied::after {
        content: '×';
        position: absolute;
        font-size: 24px;
        font-weight: 300;
        color: #94a3b8;
    }

    .seat.own-booking {
        background: #bae6fd;
        border: 2px solid #0ea5e9;
        color: #0284c7;
        cursor: not-allowed;
    }

    .seat.own-booking::after {
        content: '✓';
        position: absolute;
        font-size: 20px;
        font-weight: 400;
        color: #0284c7;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.2;
    }

    .cell-empty { width: 48px; height: 48px; }

    .cell-aisle {
        width: 28px;
        height: 48px;
        border-radius: 999px;
        background: transparent;
    }
    
    .cell-door {
        width: 48px;
        height: 48px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .cell-driver {
        width: 48px;
        height: 48px;
        background: #475569;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
    }

    .legend-box {
        width: 24px;
        height: 24px;
        border-radius: 6px;
    }

    .trip-map-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        margin-bottom: 24px;
    }

    #trip-map {
        height: 320px;
        width: 100%;
        z-index: 1;
    }

    .trip-map-meta {
        padding: 12px 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        font-size: 12px;
        color: #475569;
        background: #f8fafc;
    }
</style>
@endpush

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="bg-slate-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('landing.ticket_booking') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm mb-4">
            <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Back to search results
        </a>
        <h1 class="text-3xl font-extrabold text-white">
            Select Your <span class="text-primary-400">Seat</span>
        </h1>
    </div>
</div>

@php
    $rtCtx = $roundTripContext ?? ['trip_type' => 'one_way', 'leg' => 'outbound', 'return_date' => null, 'outbound_booking_id' => null];
    $isRoundTrip = ($rtCtx['trip_type'] ?? 'one_way') === 'round_trip';
    $isReturnLeg = $isRoundTrip && ($rtCtx['leg'] ?? 'outbound') === 'return';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @if($isRoundTrip)
        {{-- Round-trip progress indicator --}}
        <div class="mb-6 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $isReturnLeg ? 'bg-emerald-500 text-white' : 'bg-primary-600 text-white' }}">
                    @if($isReturnLeg)
                        <i data-lucide="check" style="width:11px;height:11px"></i> Outbound seats
                    @else
                        <span class="font-extrabold">1</span> Outbound seats
                    @endif
                </span>
                <div class="flex-1 max-w-[80px] h-px bg-slate-200"></div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $isReturnLeg ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                    <span class="font-extrabold">2</span> Return seats
                </span>
                <div class="flex-1 max-w-[80px] h-px bg-slate-200"></div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-500">
                    <span class="font-extrabold">3</span> Passenger details
                </span>
                <div class="flex-1 max-w-[80px] h-px bg-slate-200"></div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-500">
                    <span class="font-extrabold">4</span> Payment
                </span>
            </div>
            @if($isReturnLeg && !empty($outboundBooking))
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                    <span class="font-bold text-emerald-600">✓</span>
                    Outbound:
                    {{ $outboundBooking->trip?->route?->originCity?->name }} →
                    {{ $outboundBooking->trip?->route?->destinationCity?->name }}
                    · Seats {{ $outboundBooking->bookingSeats->pluck('seat_number')->join(', ') }}
                    · Please select <strong>{{ $outboundBooking->bookingSeats->count() }} seat(s)</strong> for the return.
                </div>
            @endif
        </div>
    @endif

    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" style="width:20px;height:20px;color:#059669"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->has('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
            <i data-lucide="alert-circle" style="width:20px;height:20px;color:#dc2626"></i>
            <span class="font-medium">{{ $errors->first('error') }}</span>
        </div>
    @endif

    @if(($remainingAllowed ?? 5) < 5)
        <div class="mb-8 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-circle" style="width:20px;height:20px;color:#d97706;flex-shrink:0"></i>
                <span class="font-medium text-sm sm:text-base">
                    You have already booked {{ 5 - ($remainingAllowed ?? 5) }} seat(s) on this trip. 
                    You can select up to {{ max(0, $remainingAllowed ?? 0) }} more.
                </span>
            </div>
            <a href="{{ route('manage.bookings') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap shadow-sm">
                <i data-lucide="ticket" style="width:16px;height:16px"></i>
                View My Tickets
            </a>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-10">
        
        {{-- ── LEFT PANEL: SEAT MAP ──────────────────────────────── --}}
        <div class="flex-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                
                {{-- Legend --}}
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-3 mb-10">
                    <div class="legend-item">
                        <div class="legend-box border-2 border-slate-200 bg-white"></div> Available
                    </div>
                    <div class="legend-item">
                        <div class="legend-box border-2 border-primary-600 bg-primary-500"></div> Selected
                    </div>
                    <div class="legend-item">
                        <div class="legend-box border-2 border-slate-300 bg-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold">×</div> Occupied
                    </div>
                    <div class="legend-item">
                        <div class="legend-box border-2 border-sky-500 bg-sky-200 flex items-center justify-center text-sky-600 text-xs font-bold">✓</div> Your Seat
                    </div>
                </div>

                {{-- Trip GPS Map --}}
                <div class="trip-map-card">
                    <div id="trip-map"></div>
                    <div class="trip-map-meta">
                        <span><strong>From:</strong> {{ $trip->route?->originCity?->name ?? 'Origin' }}</span>
                        <span><strong>To:</strong> {{ $trip->route?->destinationCity?->name ?? 'Destination' }}</span>
                        <span class="text-slate-500">Live tracking updates every 5 seconds</span>
                    </div>
                </div>

                {{-- Bus Layout --}}
                <div class="overflow-x-auto pb-4">
                    <div class="bus-container">
                        <div class="bus-front"></div>
                        <div class="bus-steering"></div>
                        
                        <div class="flex flex-col gap-4 mt-8">
                            @foreach($seatMap as $rowIndex => $row)
                                <div class="flex gap-4 justify-center"
                                     style="{{ $rowIndex === (count($seatMap) - 1) ? 'column-gap: 11px;' : '' }}">
                                    @php
                                        $isLastRow = $rowIndex === (count($seatMap) - 1);
                                        $renderRow = $row;

                                        // Common bus pattern: rear bench has one extra middle seat.
                                        // If last row only has 4 seats, add a visual 5th seat block.
                                        $bookableSeatsInRow = collect($row)->filter(function ($rowCell) {
                                            return ($rowCell['cell_type'] ?? 'empty') === 'seat'
                                                && ($rowCell['is_bookable'] ?? false);
                                        })->values();

                                        if ($isLastRow && $bookableSeatsInRow->count() === 4) {
                                            $lastSeatLabel = $bookableSeatsInRow->last()['seat_label'] ?? '';
                                            $rearExtraLabel = '';

                                            if (preg_match('/^(\d+)([A-Z])$/', $lastSeatLabel, $matches)) {
                                                $rearExtraLabel = $matches[1] . chr(ord($matches[2]) + 1);
                                            }

                                            $rearExtraSeat = [
                                                'cell_type' => 'seat',
                                                'is_bookable' => true,
                                                'is_available' => false, // display-only seat block
                                                'is_own_booking' => false,
                                                'seat_label' => $rearExtraLabel,
                                                'fare' => 0,
                                            ];

                                            // Put the extra rear seat in the center for visual alignment.
                                            array_splice($renderRow, 2, 0, [$rearExtraSeat]);
                                        }

                                        $rowCount = count($renderRow);
                                        $hasExplicitAisle = collect($row)->contains(function ($rowCell) {
                                            return ($rowCell['cell_type'] ?? 'empty') === 'aisle';
                                        });

                                        // If layout uses an "empty" cell between seats, treat it as an aisle placeholder.
                                        $hasInlineGapPlaceholder = false;
                                        foreach ($renderRow as $idx => $rowCell) {
                                            if (($rowCell['cell_type'] ?? 'empty') !== 'empty') {
                                                continue;
                                            }

                                            $prevType = $idx > 0 ? ($renderRow[$idx - 1]['cell_type'] ?? 'empty') : null;
                                            $nextType = $idx < ($rowCount - 1) ? ($renderRow[$idx + 1]['cell_type'] ?? 'empty') : null;

                                            if ($prevType === 'seat' && $nextType === 'seat') {
                                                $hasInlineGapPlaceholder = true;
                                                break;
                                            }
                                        }
                                    @endphp

                                    @foreach($renderRow as $cellIndex => $cell)
                                        @if(! $isLastRow && ! $hasExplicitAisle && ! $hasInlineGapPlaceholder && $cellIndex === 2)
                                            <div class="cell-aisle" title="Aisle"></div>
                                        @endif

                                        @php
                                            $type = $cell['cell_type'] ?? 'empty';
                                            $isBookable = $cell['is_bookable'] ?? false;
                                            $isAvailable = $cell['is_available'] ?? false;
                                            $isOwnBooking = $cell['is_own_booking'] ?? false;
                                            $label = $cell['seat_label'] ?? '';
                                            $fare = $cell['fare'] ?? 0;
                                            
                                            $seatClass = 'occupied';
                                            if ($isAvailable) {
                                                $seatClass = 'available';
                                            } elseif ($isOwnBooking) {
                                                $seatClass = 'own-booking';
                                            }
                                        @endphp

                                        @if($type === 'seat' && $isBookable)
                                            <div 
                                                class="seat {{ $seatClass }}"
                                                data-seat="{{ $label }}"
                                                data-fare="{{ $fare }}"
                                                onclick="{{ $isAvailable ? 'toggleSeat(this)' : '' }}"
                                                title="{{ $isAvailable ? 'Seat ' . $label . ' - ₱' . number_format($fare, 0) : ($isOwnBooking ? 'Your Booked Seat' : 'Occupied') }}"
                                            >
                                                {{ $label }}
                                            </div>
                                        @elseif($type === 'driver')
                                            <div class="cell-driver" title="Driver">
                                                <i data-lucide="steering-wheel" style="width:24px;height:24px"></i>
                                            </div>
                                        @elseif($type === 'door')
                                            <div class="cell-door" title="Door">Door</div>
                                        @elseif($type === 'stairs')
                                            <div class="cell-door" title="Stairs">Stairs</div>
                                        @elseif($type === 'aisle' && ! $isLastRow)
                                            <div class="cell-aisle" title="Aisle"></div>
                                        @elseif(
                                            ! $isLastRow
                                            &&
                                            $type === 'empty'
                                            && $cellIndex > 0
                                            && $cellIndex < ($rowCount - 1)
                                            && (($renderRow[$cellIndex - 1]['cell_type'] ?? 'empty') === 'seat')
                                            && (($renderRow[$cellIndex + 1]['cell_type'] ?? 'empty') === 'seat')
                                        )
                                            <div class="cell-aisle" title="Aisle"></div>
                                        @else
                                            <div class="cell-empty"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL: SUMMARY & CHECKOUT ───────────────────── --}}
        <div class="w-full lg:w-[400px] shrink-0">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-24">
                
                {{-- Trip Summary Header --}}
                <div class="p-6 bg-slate-50 border-b border-slate-200">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <i data-lucide="calendar" style="width:14px;height:14px"></i>
                        {{ \Carbon\Carbon::parse($trip->trip_date)->format('D, M j, Y') }}
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xl font-extrabold text-slate-900">{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $trip->route?->originCity?->name }}</div>
                        </div>
                        <div class="flex-1 px-4 flex items-center">
                            <div class="h-px bg-slate-300 flex-1"></div>
                            <i data-lucide="bus" style="width:16px;height:16px;color:#ea580c;margin:0 8px"></i>
                            <div class="h-px bg-slate-300 flex-1"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-extrabold text-slate-900">{{ $trip->arrival_time ? \Carbon\Carbon::parse($trip->arrival_time)->format('h:i A') : '—' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $trip->route?->destinationCity?->name }}</div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-200/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="info" style="width:14px;height:14px;color:#ea580c"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">{{ $trip->bus?->bus_name ?? 'Standard Bus' }}</div>
                            <div class="text-xs text-slate-500">{{ $trip->bus?->type?->type_name ?? 'Economy' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Selection Summary --}}
                <div class="p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Your Selection</h3>
                    
                    <div id="empty-state" class="text-center py-6 text-slate-400 text-sm">
                        Please select one or more seats from the map.
                    </div>

                    <div id="selected-seats-list" class="space-y-3 hidden">
                        <!-- Filled via JS -->
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <div class="flex justify-between items-end">
                            <div class="text-sm text-slate-500">Total Fare</div>
                            <div class="text-3xl font-extrabold text-primary-600">₱<span id="total-fare">0</span></div>
                        </div>
                    </div>

                    <form action="{{ route('user.book.seats', $trip->id) }}" method="POST" id="booking-form" class="mt-6">
                        @csrf
                        @if($isRoundTrip)
                            <input type="hidden" name="trip_type" value="round_trip">
                            <input type="hidden" name="leg" value="{{ $rtCtx['leg'] ?? 'outbound' }}">
                            @if(!empty($rtCtx['return_date']))
                                <input type="hidden" name="return_date" value="{{ $rtCtx['return_date'] }}">
                            @endif
                            @if(!empty($rtCtx['outbound_booking_id']))
                                <input type="hidden" name="outbound_booking_id" value="{{ $rtCtx['outbound_booking_id'] }}">
                            @endif
                            <input type="hidden" name="from" value="{{ request('from') }}">
                            <input type="hidden" name="to" value="{{ request('to') }}">
                            <input type="hidden" name="date" value="{{ request('date') }}">
                        @endif
                        <div id="hidden-inputs"></div>
                        <button type="submit" id="checkout-btn" disabled
                                class="w-full py-4 rounded-xl font-bold text-white transition-all
                                       bg-slate-300 cursor-not-allowed
                                       hover:bg-primary-700 disabled:opacity-50">
                            @if($isRoundTrip && !$isReturnLeg)
                                Continue → Pick Return Trip
                            @elseif($isRoundTrip && $isReturnLeg)
                                Continue to Passenger Details →
                            @else
                                Continue to Details →
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const selectedSeats = new Map(); // seat_label -> fare
    const remainingAllowed = {{ $remainingAllowed ?? 5 }};
    const tripOriginCity = @json($trip->route?->originCity?->name ?? '');
    const tripDestinationCity = @json($trip->route?->destinationCity?->name ?? '');
    const tripLocationEndpoint = @json(route('user.trip.location', $trip->id));

    async function geocodeCity(cityName) {
        if (!cityName) return null;

        const query = encodeURIComponent(`${cityName}, Philippines`);
        const endpoint = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${query}`;

        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) return null;

            const data = await response.json();
            if (!Array.isArray(data) || data.length === 0) return null;

            return {
                lat: parseFloat(data[0].lat),
                lng: parseFloat(data[0].lon)
            };
        } catch (error) {
            return null;
        }
    }

    async function initTripMap() {
        const mapElement = document.getElementById('trip-map');
        if (!mapElement || typeof L === 'undefined') return;

        const defaultCenter = [12.8797, 121.7740]; // Philippines
        const map = L.map('trip-map', {
            zoomControl: true
        }).setView(defaultCenter, 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const [originCoords, destinationCoords] = await Promise.all([
            geocodeCity(tripOriginCity),
            geocodeCity(tripDestinationCity)
        ]);

        if (!originCoords || !destinationCoords) {
            return;
        }

        const originLatLng = [originCoords.lat, originCoords.lng];
        const destinationLatLng = [destinationCoords.lat, destinationCoords.lng];
        const bounds = L.latLngBounds([originLatLng, destinationLatLng]);

        L.marker(originLatLng)
            .addTo(map)
            .bindPopup(`<strong>Origin</strong><br>${tripOriginCity}`);

        L.marker(destinationLatLng)
            .addTo(map)
            .bindPopup(`<strong>Destination</strong><br>${tripDestinationCity}`);

        L.polyline([originLatLng, destinationLatLng], {
            color: '#f97316',
            weight: 4,
            opacity: 0.9
        }).addTo(map);

        const fallbackBusPosition = [
            (originCoords.lat + destinationCoords.lat) / 2,
            (originCoords.lng + destinationCoords.lng) / 2
        ];

        const busMarker = L.circleMarker(fallbackBusPosition, {
            radius: 8,
            color: '#0369a1',
            fillColor: '#0ea5e9',
            fillOpacity: 0.9,
            weight: 2
        })
            .addTo(map)
            .bindPopup('<strong>Bus</strong><br>Waiting for live GPS location...');

        async function refreshBusLocation() {
            try {
                const response = await fetch(tripLocationEndpoint, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const payload = await response.json();
                if (!payload || payload.has_location !== true) return;

                const liveLatLng = [Number(payload.lat), Number(payload.lng)];
                busMarker.setLatLng(liveLatLng);

                const updatedAt = payload.last_updated_at
                    ? new Date(payload.last_updated_at).toLocaleString()
                    : 'just now';

                busMarker.bindPopup(`<strong>Bus (Live)</strong><br>Updated: ${updatedAt}`);
            } catch (error) {
                // Keep last known marker location when polling fails.
            }
        }

        map.fitBounds(bounds.pad(0.35));

        await refreshBusLocation();
        const pollerId = window.setInterval(refreshBusLocation, 5000);

        window.addEventListener('beforeunload', () => {
            window.clearInterval(pollerId);
        });
    }

    function toggleSeat(element) {
        const seatLabel = element.dataset.seat;
        const fare = parseFloat(element.dataset.fare);

        if (element.classList.contains('selected')) {
            // Deselect
            element.classList.remove('selected');
            selectedSeats.delete(seatLabel);
        } else {
            // Select
            if (selectedSeats.size >= remainingAllowed) {
                alert(`You can only select up to ${remainingAllowed} seat(s) for this trip.`);
                return;
            }
            element.classList.add('selected');
            selectedSeats.set(seatLabel, fare);
        }

        updateSummary();
    }

    function updateSummary() {
        const listContainer = document.getElementById('selected-seats-list');
        const emptyState = document.getElementById('empty-state');
        const totalSpan = document.getElementById('total-fare');
        const hiddenInputs = document.getElementById('hidden-inputs');
        const checkoutBtn = document.getElementById('checkout-btn');

        // Clear current list and inputs
        listContainer.innerHTML = '';
        hiddenInputs.innerHTML = '';
        let total = 0;

        if (selectedSeats.size === 0) {
            emptyState.classList.remove('hidden');
            listContainer.classList.add('hidden');
            checkoutBtn.disabled = true;
            checkoutBtn.classList.remove('bg-primary-600', 'shadow-lg', 'shadow-primary-500/30');
            checkoutBtn.classList.add('bg-slate-300', 'cursor-not-allowed');
        } else {
            emptyState.classList.add('hidden');
            listContainer.classList.remove('hidden');
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('bg-slate-300', 'cursor-not-allowed');
            checkoutBtn.classList.add('bg-primary-600', 'shadow-lg', 'shadow-primary-500/30');

            selectedSeats.forEach((fare, seat) => {
                total += fare;
                
                // Add to visible list
                const item = document.createElement('div');
                item.className = 'flex justify-between items-center p-3 bg-slate-50 rounded-lg border border-slate-100';
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-white border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                            ${seat}
                        </div>
                        <div class="text-sm font-semibold text-slate-700">Seat</div>
                    </div>
                    <div class="text-sm font-bold text-slate-900">₱${fare.toLocaleString()}</div>
                `;
                listContainer.appendChild(item);

                // Add to form inputs
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_seats[]';
                input.value = seat;
                hiddenInputs.appendChild(input);
            });
        }

        totalSpan.textContent = total.toLocaleString();
    }

    document.addEventListener('DOMContentLoaded', initTripMap);
</script>
@endpush
