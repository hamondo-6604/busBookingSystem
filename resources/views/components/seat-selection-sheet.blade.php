@props([
    'trip',
    'seatMap',
    'remainingAllowed' => 5,
    'roundTripContext' => null,
    'outboundBooking' => null,
])

@php
    $rtCtx = $roundTripContext ?? ['trip_type' => 'one_way', 'leg' => 'outbound', 'return_date' => null, 'outbound_booking_id' => null];
    $isRoundTrip = ($rtCtx['trip_type'] ?? 'one_way') === 'round_trip';
    $isReturnLeg = $isRoundTrip && ($rtCtx['leg'] ?? 'outbound') === 'return';
    $dep = $trip->departure_time;
    $arr = $trip->arrival_time;
    $dur = $trip->route?->estimated_duration_minutes;
    $durStr = $dur ? floor($dur / 60) . 'h ' . str_pad($dur % 60, 2, '0', STR_PAD_LEFT) . 'm' : '—';
    $type = strtolower($trip->bus?->default_seat_type ?? 'economy');
    $typeBadge = match ($type) {
        'business' => 'bg-amber-100 text-amber-700',
        'sleeper'  => 'bg-violet-100 text-violet-700',
        default    => 'bg-emerald-100 text-emerald-700',
    };
    $avgRating = $trip->feedback_avg_rating ?? null;
    $amenities = $trip->bus?->amenities ?? collect();

    // Stops calculation
    $routeStops = $trip->route?->stops ?? collect();

    $boardingPoints = collect();
    // 1. Add origin terminal/city as the first boarding point
    $boardingPoints->push((object)[
        'id' => null, // null represents default origin/terminal
        'name' => $trip->departureTerminal?->name ?? $trip->route?->originCity?->name ?? 'Origin Terminal',
        'time' => $dep,
        'description' => $trip->departureTerminal?->address ?? 'Main Terminal',
    ]);
    // 2. Add intermediate stops that allow boarding
    foreach ($routeStops as $stop) {
        if ($stop->pivot->allows_boarding) {
            $boardingPoints->push((object)[
                'id' => $stop->id,
                'name' => $stop->name,
                'time' => $dep->copy()->addMinutes($stop->pivot->minutes_from_origin),
                'description' => $stop->address ?? $stop->location_label,
            ]);
        }
    }

    $droppingPoints = collect();
    // 1. Add intermediate stops that allow alighting (dropping)
    foreach ($routeStops as $stop) {
        if ($stop->pivot->allows_alighting) {
            $droppingPoints->push((object)[
                'id' => $stop->id,
                'name' => $stop->name,
                'time' => $dep->copy()->addMinutes($stop->pivot->minutes_from_origin),
                'description' => $stop->address ?? $stop->location_label,
            ]);
        }
    }
    // 2. Add destination terminal/city as the last dropping point
    $arrTime = $arr ?? ($trip->route?->estimated_duration_minutes ? $dep->copy()->addMinutes($trip->route->estimated_duration_minutes) : $dep);
    $droppingPoints->push((object)[
        'id' => null, // null represents default destination/terminal
        'name' => $trip->arrivalTerminal?->name ?? $trip->route?->destinationCity?->name ?? 'Destination Terminal',
        'time' => $arrTime,
        'description' => $trip->arrivalTerminal?->address ?? 'Main Terminal',
    ]);
@endphp

@include('components.partials.seat-sheet-styles')

<form action="{{ route('user.book.seats', $trip->id) }}" method="POST" class="seat-sheet-root flex flex-col h-full" data-remaining="{{ $remainingAllowed }}">
    @csrf

        <div class="shrink-0 border-b border-slate-200 bg-white px-4 sm:px-6 pt-4 pb-0">
        <div class="flex items-center gap-3 mb-4">
            <button type="button" onclick="SeatSheet.close()"
                    class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors shrink-0"
                    aria-label="Close">
                <i data-lucide="x" style="width:20px;height:20px;color:#475569"></i>
            </button>
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 truncate">
                    {{ $trip->route?->originCity?->name ?? 'Origin' }}
                    <span class="text-slate-400 font-normal mx-1">→</span>
                    {{ $trip->route?->destinationCity?->name ?? 'Destination' }}
                </h2>
            </div>

            <!-- Premium Rounded Pill Yellow Coupon Badge inside Seat Modal Header (Interactive) -->
            <button type="button" onclick="SeatSheet.openCouponModal()" 
                    class="shrink-0 bg-[#fef9c3] text-[#854d0e] border border-[#fde047] px-4 py-1 text-[11px] font-black uppercase tracking-wider shadow-sm select-none mr-2 rounded-full cursor-pointer hover:scale-[1.02] active:scale-[0.98] transition-transform focus:outline-none">
                TRY NEW ₱50 OFF
            </button>

            @if($trip->available_seats <= 5)
                <span class="shrink-0 text-xs font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded-full">
                    {{ $trip->available_seats }} left
                </span>
            @endif
        </div>

        <div class="flex justify-center gap-6 text-xs sm:text-sm overflow-x-auto border-b border-slate-100 mb-2">
            <button type="button" id="step-btn-seats" onclick="SeatSheet.setStep('seats')" class="pb-3 text-primary-600 font-semibold border-b-2 border-primary-600 whitespace-nowrap focus:outline-none">1. Select seats</button>
            <button type="button" id="step-btn-board-drop" onclick="SeatSheet.setStep('board-drop')" class="pb-3 text-slate-400 font-medium whitespace-nowrap focus:outline-none" disabled>2. Board/Drop point</button>
            <button type="button" id="step-btn-passenger" class="pb-3 text-slate-300 font-medium whitespace-nowrap focus:outline-none" disabled>3. Passenger Info</button>
        </div>

        @if($isReturnLeg && !empty($outboundBooking))
            <div class="mb-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-xs text-slate-600">
                <span class="font-bold text-emerald-600">Outbound booked:</span>
                {{ $outboundBooking->trip?->route?->originCity?->name }} →
                {{ $outboundBooking->trip?->route?->destinationCity?->name }}
                · Seats {{ $outboundBooking->bookingSeats->pluck('seat_number')->join(', ') }}
            </div>
        @endif

        @if(($remainingAllowed ?? 5) < 5)
            <div class="mb-3 p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-800">
                You can select up to {{ max(0, $remainingAllowed) }} more seat(s) on this trip.
            </div>
        @endif
    </div>

    <div id="step-content-seats" class="flex-1 flex flex-col lg:flex-row overflow-hidden min-h-0">

        <div class="lg:w-[42%] xl:w-[40%] border-b lg:border-b-0 lg:border-r border-slate-200 overflow-y-auto bg-slate-50/50 p-4 sm:p-6">
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 mb-6 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="seat-sheet-legend-box border-2 border-slate-200 bg-white"></span> Available</span>
                <span class="flex items-center gap-1.5"><span class="seat-sheet-legend-box bg-green-500 border-2 border-green-600"></span> Selected</span>
                <span class="flex items-center gap-1.5"><span class="seat-sheet-legend-box bg-slate-200 border-2 border-slate-300"></span> Sold</span>
                <span class="flex items-center gap-1.5"><span class="seat-sheet-legend-box bg-sky-200 border-2 border-sky-500"></span> Yours</span>
            </div>

            @include('components.partials.seat-map-grid', ['seatMap' => $seatMap])
        </div>

        <div class="flex-1 overflow-y-auto bg-white p-4 sm:p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center shrink-0 overflow-hidden">
                    @if($trip->bus?->image_url)
                        <img src="{{ $trip->bus->image_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="bus" style="width:22px;height:22px;color:#ea580c"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">{{ $trip->bus?->bus_name ?? 'Mindanao Express' }}</h3>
                        @if($avgRating)
                            <span class="inline-flex items-center gap-0.5 text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                <i data-lucide="star" style="width:11px;height:11px;fill:currentColor"></i>
                                {{ number_format($avgRating, 1) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $typeBadge }}">{{ ucfirst($type) }}</span>
                        <span class="text-xs text-slate-500">{{ $trip->bus?->type?->type_name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-5 p-4 bg-slate-50 rounded-xl">
                <div class="text-center">
                    <div class="text-lg font-extrabold text-slate-900">{{ $dep->format('H:i') }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $trip->route?->originCity?->name }}</div>
                </div>
                <div class="flex-1 flex flex-col items-center gap-0.5">
                    <span class="text-[10px] text-slate-400">{{ $durStr }}</span>
                    <div class="w-full flex items-center">
                        <div class="w-1.5 h-1.5 rounded-full bg-primary-400"></div>
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <i data-lucide="bus" style="width:11px;height:11px;color:#ea580c"></i>
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-extrabold text-slate-900">{{ $arr?->format('H:i') ?? '—' }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $trip->route?->destinationCity?->name }}</div>
                </div>
            </div>

            <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
                @for($i = 0; $i < 3; $i++)
                    <div class="w-28 h-20 shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                        @if($i === 0 && $trip->bus?->image_url)
                            <img src="{{ $trip->bus->image_url }}" alt="Bus" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="{{ ['bus', 'armchair', 'wifi'][$i] ?? 'bus' }}" style="width:24px;height:24px;color:#94a3b8"></i>
                        @endif
                    </div>
                @endfor
            </div>

            <div class="border-b border-slate-200 mb-4">
                <div class="flex gap-4 overflow-x-auto text-xs sm:text-sm">
                    @foreach(['highlights' => 'Highlights', 'boarding' => 'Boarding', 'dropping' => 'Dropping', 'amenities' => 'Amenities'] as $tabId => $tabLabel)
                        <button type="button"
                                class="seat-info-tab pb-2.5 text-slate-500 whitespace-nowrap transition-colors {{ $tabId === 'highlights' ? 'active' : '' }}"
                                onclick="SeatSheet.switchTab('{{ $tabId }}')">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div id="seat-info-highlights" class="seat-info-panel active space-y-3 text-sm text-slate-600">
                <div class="flex items-center gap-2 p-3 bg-emerald-50 rounded-xl">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#059669"></i>
                    <span><strong class="text-slate-800">Direct route</strong> · {{ $durStr }} estimated travel time</span>
                </div>
                <div class="flex items-center gap-2 p-3 bg-primary-50 rounded-xl">
                    <i data-lucide="users" style="width:16px;height:16px;color:#ea580c"></i>
                    <span><strong class="text-slate-800">{{ $trip->available_seats }}</strong> seats available on this trip</span>
                </div>
                @auth
                    @if(auth()->user()->discountType?->percentage > 0)
                        <div class="flex items-center gap-2 p-3 bg-amber-50 rounded-xl">
                            <i data-lucide="badge-percent" style="width:16px;height:16px;color:#d97706"></i>
                            <span>Your discount: <strong>{{ number_format(auth()->user()->discountType->percentage * 100, 0) }}% off</strong> applied at checkout</span>
                        </div>
                    @endif
                @endauth
                @if($trip->bus?->description)
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $trip->bus->description }}</p>
                @endif
            </div>

            <div id="seat-info-boarding" class="seat-info-panel space-y-2 text-sm">
                <h4 class="font-bold text-slate-800">{{ $trip->departureTerminal?->name ?? $trip->route?->originCity?->name ?? 'Boarding point' }}</h4>
                @if($trip->departureTerminal?->address)
                    <p class="text-slate-600 flex items-start gap-2">
                        <i data-lucide="map-pin" style="width:14px;height:14px;color:#ea580c;margin-top:2px;flex-shrink:0"></i>
                        {{ $trip->departureTerminal->address }}
                    </p>
                @endif
                @if($trip->departureTerminal?->contact_number)
                    <p class="text-slate-600 flex items-center gap-2">
                        <i data-lucide="phone" style="width:14px;height:14px;color:#ea580c"></i>
                        {{ $trip->departureTerminal->contact_number }}
                    </p>
                @endif
                <p class="text-xs text-slate-500 mt-2">Departure: {{ $dep->format('h:i A') }} · {{ \Carbon\Carbon::parse($trip->trip_date)->format('M j, Y') }}</p>
            </div>

            <div id="seat-info-dropping" class="seat-info-panel space-y-2 text-sm">
                <h4 class="font-bold text-slate-800">{{ $trip->arrivalTerminal?->name ?? $trip->route?->destinationCity?->name ?? 'Dropping point' }}</h4>
                @if($trip->arrivalTerminal?->address)
                    <p class="text-slate-600 flex items-start gap-2">
                        <i data-lucide="map-pin" style="width:14px;height:14px;color:#ea580c;margin-top:2px;flex-shrink:0"></i>
                        {{ $trip->arrivalTerminal->address }}
                    </p>
                @endif
                @if($trip->arrivalTerminal?->contact_number)
                    <p class="text-slate-600 flex items-center gap-2">
                        <i data-lucide="phone" style="width:14px;height:14px;color:#ea580c"></i>
                        {{ $trip->arrivalTerminal->contact_number }}
                    </p>
                @endif
                <p class="text-xs text-slate-500 mt-2">Arrival: {{ $arr?->format('h:i A') ?? '—' }}</p>
            </div>

            <div id="seat-info-amenities" class="seat-info-panel">
                @if($amenities->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($amenities as $amenity)
                            <span class="flex items-center gap-1.5 text-xs text-slate-600 bg-slate-50 border border-slate-100 px-3 py-2 rounded-lg">
                                <i data-lucide="{{ $amenity->icon ?? 'check' }}" style="width:12px;height:12px;color:#ea580c"></i>
                                {{ $amenity->display_name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">Standard amenities included.</p>
                @endif
            </div>
        </div>
        </div>
    </div>

    <div id="step-content-board-drop" class="hidden flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50/60">
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Boarding points column -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col min-h-[300px]">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Boarding points</h3>
                <p class="text-xs text-slate-400 mb-4">Select Boarding Point</p>
                <div class="space-y-3 overflow-y-auto flex-1 pr-1">
                    @foreach($boardingPoints as $bp)
                        <label class="flex items-center justify-between p-4 border border-slate-200/85 hover:border-primary-400 rounded-2xl cursor-pointer transition-all bg-white hover:bg-primary-50/5 group shadow-sm">
                            <input type="radio" name="boarding_stop_id" value="{{ $bp->id }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="flex items-start gap-3.5">
                                <span class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $bp->time->format('H:i') }}</span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-primary-700 transition-colors">{{ $bp->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $bp->description }}</p>
                                </div>
                            </div>
                            <div class="radio-circle-board shrink-0 ml-3"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Dropping points column -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col min-h-[300px]">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Dropping points</h3>
                <p class="text-xs text-slate-400 mb-4">Select Dropping Point</p>
                <div class="space-y-3 overflow-y-auto flex-1 pr-1">
                    @foreach($droppingPoints as $dp)
                        <label class="flex items-center justify-between p-4 border border-slate-200/85 hover:border-emerald-400 rounded-2xl cursor-pointer transition-all bg-white hover:bg-emerald-50/5 group shadow-sm">
                            <input type="radio" name="dropping_stop_id" value="{{ $dp->id }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="flex items-start gap-3.5">
                                <span class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $dp->time->format('H:i') }}</span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">{{ $dp->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $dp->description }}</p>
                                </div>
                            </div>
                            <div class="radio-circle-drop shrink-0 ml-3"></div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="shrink-0 border-t border-slate-200 bg-white px-4 sm:px-6 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="flex items-center gap-4">
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
            <div id="seat-sheet-hidden-inputs"></div>
            <div class="flex-1 min-w-0">
                <div id="seat-sheet-empty" class="text-sm text-slate-400">Select seat(s) to continue</div>
                <div id="seat-sheet-summary" class="hidden">
                    <div class="text-xs text-slate-500"><span id="seat-sheet-count">0</span> seat(s) selected</div>
                    <div class="text-xl font-extrabold text-slate-900">₱<span id="seat-sheet-total">0</span></div>
                </div>
            </div>
            <button type="submit" id="seat-sheet-continue" disabled onclick="SeatSheet.handleContinue(event)"
                    class="shrink-0 px-5 sm:px-8 py-3 rounded-xl font-bold text-white text-sm transition-all
                           bg-slate-300 cursor-not-allowed disabled:opacity-60">
                @if($isRoundTrip && ! $isReturnLeg)
                    Select boarding & return trip →
                @elseif($isRoundTrip && $isReturnLeg)
                    Continue to passenger details →
                @else
                    Continue to passenger details →
                @endif
            </button>
        </div>
    </div>
</form>

<!-- Coupon Details Modal -->
<div id="coupon-detail-modal" class="hidden fixed inset-0 z-[300] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60" onclick="SeatSheet.closeCouponModal()"></div>
    
    <!-- Modal Container -->
    <div class="bg-white rounded-[28px] shadow-2xl max-w-[35rem] w-full p-8 relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <!-- Close Button -->
        <button type="button" onclick="SeatSheet.closeCouponModal()" 
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 transition-colors">
            <i data-lucide="x" style="width:16px;height:16px;color:#475569"></i>
        </button>
        
        <!-- Coupon Card Content -->
        <div class="flex flex-col items-center mt-4">
            <!-- Ticket-shaped yellow coupon badge -->
            <div class="flex flex-col items-center justify-center text-slate-800 px-5 py-2.5 select-none" 
                 style="background: radial-gradient(circle at 0px 50%, transparent 5px, #fef08a 6px) left / 51% 100% no-repeat, radial-gradient(circle at 100% 50%, transparent 5px, #fef08a 6px) right / 51% 100% no-repeat; border-radius: 8px; min-width: 130px; height: 52px; box-shadow: 0 4px 12px -2px rgba(234,88,12,0.08);">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider leading-none">Try new</span>
                <span class="text-[15px] font-black text-slate-900 uppercase tracking-tight leading-none mt-1.5">₱50 OFF</span>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-extrabold text-slate-900 text-center mt-6">
                Try a new bus operator
            </h3>
            
            <!-- Description -->
            <p class="text-sm text-slate-500 text-center mt-3 leading-relaxed">
                Get a discount by travelling with a bus operator you haven't tried, only on Mindanao Express.
            </p>
            
            <!-- Okay Button -->
            <button type="button" onclick="SeatSheet.closeCouponModal()"
                    class="w-full mt-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-full transition-all shadow-lg shadow-orange-600/10 hover:shadow-orange-600/20 text-sm">
                Okay
            </button>
        </div>
    </div>
</div>

