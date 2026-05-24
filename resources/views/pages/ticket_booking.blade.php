@extends('layouts.app')
@section('title', 'Search Trips — Mindanao Express')

@push('head')
<style>
  .trip-card { transition: box-shadow .2s, transform .2s; }
  .trip-card:hover { box-shadow: 0 12px 32px rgba(0,0,0,.09); transform: translateY(-2px); }
</style>
@include('components.partials.seat-sheet-styles')
@endpush

@section('content')

@php
  $isRoundTrip = ($prefill['trip_type'] ?? 'one_way') === 'round_trip';
  $currentLeg  = $prefill['leg'] ?? 'outbound';
  $isReturnLegSearch = $isRoundTrip && $currentLeg === 'return';
@endphp

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="bg-slate-900 py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-xs font-bold text-primary-400 uppercase tracking-widest mb-1">Bus Tickets</p>
    <h1 class="text-3xl font-extrabold text-white">
      @if($isReturnLegSearch)
        Choose Your <span class="text-primary-400">Return Trip</span>
      @else
        Search <span class="text-primary-400">Trips</span>
      @endif
    </h1>
    <p class="text-slate-400 text-sm mt-1">
      @if($isReturnLegSearch)
        Step 2 of 2 — select the trip and seats for your return journey.
      @else
        Find available buses, compare seat types, and book instantly.
      @endif
    </p>

    {{-- Round-trip progress indicator --}}
    @if($isRoundTrip)
      <div class="flex items-center gap-2 mt-4 text-xs font-semibold">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $currentLeg === 'outbound' ? 'bg-primary-500 text-white' : 'bg-emerald-500 text-white' }}">
          @if($currentLeg !== 'outbound')
            <i data-lucide="check" style="width:11px;height:11px"></i>
          @else
            <span class="font-extrabold">1</span>
          @endif
          Outbound
        </span>
        <div class="flex-1 max-w-[60px] h-px bg-slate-600"></div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $currentLeg === 'return' ? 'bg-primary-500 text-white' : 'bg-slate-700 text-slate-400' }}">
          <span class="font-extrabold">2</span> Return
        </span>
      </div>
    @endif
  </div>
</div>

@if($isReturnLegSearch && !empty($outboundBooking))
  {{-- Outbound summary banner --}}
  <div class="bg-emerald-50 border-b border-emerald-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center gap-4">
      <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
        <i data-lucide="check" style="width:16px;height:16px"></i>
      </div>
      <div class="flex-1 min-w-[220px]">
        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Outbound seats reserved</div>
        <div class="text-sm font-bold text-slate-800">
          {{ $outboundBooking->trip?->route?->originCity?->name }}
          →
          {{ $outboundBooking->trip?->route?->destinationCity?->name }}
          · {{ \Carbon\Carbon::parse($outboundBooking->trip?->trip_date)->format('M j, Y') }}
        </div>
        <div class="text-xs text-slate-600">
          Seats: {{ $outboundBooking->bookingSeats->pluck('seat_number')->join(', ') }}
          · ₱{{ number_format($outboundBooking->base_fare, 2) }}
        </div>
      </div>
    </div>
  </div>
@endif

{{-- ── STICKY SEARCH BAR ───────────────────────────────────────── --}}
<div class="bg-white border-b border-slate-200 shadow-sm sticky top-16 z-30">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <form action="{{ route('landing.ticket_booking.search') }}" method="POST"
          class="flex flex-col gap-3">
      @csrf
      <input type="hidden" name="trip_type" id="search-trip-type" value="{{ $prefill['trip_type'] ?? 'one_way' }}">
      @if($isReturnLegSearch && !empty($prefill['outbound_booking_id']))
        <input type="hidden" name="outbound_booking_id" value="{{ $prefill['outbound_booking_id'] }}">
        <input type="hidden" name="leg" value="return">
      @endif

      {{-- Trip-type tabs (hidden when actively choosing the return leg) --}}
      @if(!$isReturnLegSearch)
        <div class="flex items-center gap-2">
          <button type="button" id="sb-tt-oneway" onclick="setSearchTripType('one_way')"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $isRoundTrip ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-primary-600 text-white shadow-sm' }}">
            <i data-lucide="arrow-right" style="width:12px;height:12px"></i> One-way
          </button>
          <button type="button" id="sb-tt-roundtrip" onclick="setSearchTripType('round_trip')"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $isRoundTrip ? 'bg-primary-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <i data-lucide="arrow-left-right" style="width:12px;height:12px"></i> Round-trip
          </button>
        </div>
      @endif

      <div class="flex flex-wrap gap-3 items-end">

      {{-- From --}}
      <div class="flex-1 min-w-[140px]">
        <label class="block text-xs font-semibold text-slate-600 mb-1">From</label>
        <div class="relative">
          <i data-lucide="map-pin"
             style="width:13px;height:13px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
          <select name="from" required
                  class="w-full pl-8 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl
                         focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white appearance-none">
            <option value="">Origin city</option>
            @foreach($originCities as $city)
              <option value="{{ $city->name }}"
                      {{ ($prefill['from'] ?? '') === $city->name ? 'selected' : '' }}>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Swap --}}
      <button type="button" onclick="swapCities()"
              class="self-end p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50
                     text-slate-500 transition-colors">
        <i data-lucide="arrow-left-right" style="width:15px;height:15px"></i>
      </button>

      {{-- To --}}
      <div class="flex-1 min-w-[140px]">
        <label class="block text-xs font-semibold text-slate-600 mb-1">To</label>
        <div class="relative">
          <i data-lucide="map-pin"
             style="width:13px;height:13px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
          <select name="to" required
                  class="w-full pl-8 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl
                         focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white appearance-none">
            <option value="">Destination</option>
            @foreach($destinationCities as $city)
              <option value="{{ $city->name }}"
                      {{ ($prefill['to'] ?? '') === $city->name ? 'selected' : '' }}>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Date --}}
      <div class="min-w-[160px]">
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          {{ $isReturnLegSearch ? 'Return Date' : 'Travel Date' }}
        </label>
        <div class="relative">
          <i data-lucide="calendar"
             style="width:13px;height:13px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
          <input type="date" name="date" required
                 min="{{ today()->toDateString() }}"
                 value="{{ $isReturnLegSearch ? ($prefill['return_date'] ?? today()->toDateString()) : ($prefill['date'] ?? today()->toDateString()) }}"
                 class="w-full pl-8 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                        focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
      </div>

      {{-- Return Date (only for round-trip outbound search) --}}
      <div class="min-w-[160px] {{ $isRoundTrip && !$isReturnLegSearch ? '' : 'hidden' }}" id="sb-return-date-wrap">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Return Date</label>
        <div class="relative">
          <i data-lucide="calendar"
             style="width:13px;height:13px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
          <input type="date" name="return_date" id="sb-return-date"
                 min="{{ today()->toDateString() }}"
                 value="{{ !$isReturnLegSearch ? ($prefill['return_date'] ?? '') : '' }}"
                 {{ $isRoundTrip && !$isReturnLegSearch ? 'required' : '' }}
                 class="w-full pl-8 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                        focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
      </div>

      {{-- Submit --}}
      <button type="submit"
              class="self-end flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700
                     text-white text-sm font-bold rounded-xl transition-colors whitespace-nowrap">
        <i data-lucide="search" style="width:14px;height:14px"></i> Search
      </button>
      </div>
    </form>
  </div>
</div>

{{-- ── RESULTS AREA ────────────────────────────────────────────── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  @if($trips->isNotEmpty())
    {{-- ── Result meta + sort ────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
      <div>
        <h2 class="text-lg font-extrabold text-slate-900">
          {{ $trips->count() }} trip{{ $trips->count() !== 1 ? 's' : '' }} found
        </h2>
        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
          <i data-lucide="map-pin" style="width:11px;height:11px"></i>
          <span>{{ $prefill['search_from'] ?? $prefill['from'] ?? '' }} → {{ $prefill['search_to'] ?? $prefill['to'] ?? '' }}</span>
          <span class="text-slate-300">·</span>
          <i data-lucide="calendar" style="width:11px;height:11px"></i>
          <span>
            {{ isset($prefill['search_date'])
               ? \Carbon\Carbon::parse($prefill['search_date'])->format('D, M j Y')
               : '' }}
          </span>
          @if($isReturnLegSearch)
            <span class="text-slate-300">·</span>
            <span class="text-emerald-600 font-bold">Return leg</span>
          @endif
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-slate-500">Sort:</span>
        @foreach([['departure','Earliest'],['price','Lowest fare'],['seats','Most seats']] as [$val,$lbl])
          <button onclick="sortTrips('{{ $val }}')" data-sort="{{ $val }}"
                  class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                         {{ $val === 'departure'
                            ? 'bg-primary-600 text-white border-primary-600'
                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            {{ $lbl }}
          </button>
        @endforeach
      </div>
    </div>

    {{-- Filter chips --}}
    <div class="flex flex-wrap gap-2 mb-6">
      @foreach(['all' => 'All Classes', 'economy' => 'Economy', 'business' => 'Business', 'sleeper' => 'Sleeper'] as $val => $lbl)
        <button onclick="filterClass('{{ $val }}')" data-filter="{{ $val }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors
                       {{ $val === 'all'
                          ? 'bg-slate-800 text-white border-slate-800'
                          : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
          {{ $lbl }}
        </button>
      @endforeach
    </div>

    {{-- ── Trip cards ────────────────────────────────────────── --}}
    <div id="trip-list" class="space-y-4">
      @foreach($trips as $trip)
        @php
          $dep     = $trip->departure_time;
          $arr     = $trip->arrival_time;
          $dur     = $trip->route?->estimated_duration_minutes;
          $durStr  = $dur ? floor($dur/60).'h '.str_pad($dur%60,2,'0',STR_PAD_LEFT).'m' : '—';
          $type    = strtolower($trip->bus?->default_seat_type ?? 'economy');
          $typeBadge = match($type) {
            'business' => 'bg-amber-100 text-amber-700',
            'sleeper'  => 'bg-violet-100 text-violet-700',
            default    => 'bg-emerald-100 text-emerald-700',
          };
          $seatsLow = $trip->available_seats <= 5;
        @endphp
        <div class="trip-card bg-white border border-slate-200 rounded-2xl relative"
             data-class="{{ $type }}"
             data-departure="{{ $dep->format('H:i') }}"
             data-price="{{ $trip->fare }}"
             data-seats="{{ $trip->available_seats }}">

          <!-- Premium Rounded Pill Yellow Coupon Badge -->
          <div class="absolute top-0 right-8 -translate-y-1/2 bg-[#fef9c3] text-[#854d0e] border border-[#fde047] px-4 py-1 text-[11px] font-black uppercase tracking-wider shadow-sm select-none rounded-full" 
               style="z-index: 10;">
              TRY NEW ₱50 OFF
          </div>

          <div class="p-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 items-center gap-6">

              {{-- Operator --}}
              <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 bg-orange-50 rounded-xl flex items-center justify-center shrink-0 border border-orange-100/50">
                  <i data-lucide="bus" style="width:20px;height:20px;color:#ea580c"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-base font-extrabold text-slate-900 truncate">
                    {{ $trip->bus?->bus_name ?? 'Mindanao Express Bus' }}
                  </div>
                  <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold {{ $typeBadge }}">
                      {{ ucfirst($type) }}
                    </span>
                    <span class="text-xs text-slate-400 font-semibold">
                      {{ $trip->bus?->type?->type_name }}
                    </span>
                    @if($trip->departureTerminal)
                      <span class="text-xs text-slate-400 font-medium flex items-center gap-0.5">
                        <i data-lucide="map-pin" style="width:11px;height:11px;color:#ea580c"></i>
                        {{ $trip->departureTerminal->name }}
                      </span>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Time + duration --}}
              <div class="flex items-center justify-center gap-6 w-full lg:justify-self-center py-2 lg:py-0 border-y lg:border-y-0 border-slate-100/60 lg:my-0 my-2">
                <div class="text-center min-w-[75px]">
                  <div class="text-2xl font-black text-slate-900 leading-none tracking-tight">{{ $dep->format('H:i') }}</div>
                  <div class="text-xs text-slate-400 font-bold mt-2 truncate max-w-[100px]">{{ $trip->route?->originCity?->name }}</div>
                </div>
                
                <div class="flex flex-col items-center gap-1.5 w-24 shrink-0">
                  <span class="text-[10px] font-bold text-slate-400 tracking-wide uppercase">{{ $durStr }}</span>
                  <div class="w-full flex items-center relative py-1">
                    <!-- Left Orange Dot -->
                    <div class="w-2.5 h-2.5 rounded-full bg-orange-500 shrink-0 shadow-sm shadow-orange-500/20"></div>
                    
                    <!-- Thin Connector Line -->
                    <div class="flex-1 h-[2px] bg-slate-200"></div>
                    
                    <!-- Filled Orange Bus Icon in center -->
                    <div class="mx-1 shrink-0 bg-white px-1 z-10 relative">
                      <i data-lucide="bus" style="width:13px;height:13px;color:#ea580c;fill:#ea580c;transform:scaleX(-1)"></i>
                    </div>
                    
                    <!-- Thin Connector Line -->
                    <div class="flex-1 h-[2px] bg-slate-200"></div>
                    
                    <!-- Right Emerald Dot -->
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 shadow-sm shadow-emerald-500/20"></div>
                  </div>
                  <span class="text-[10px] font-bold text-slate-400 tracking-wide uppercase">Direct</span>
                </div>
                
                <div class="text-center min-w-[75px]">
                  <div class="text-2xl font-black text-slate-900 leading-none tracking-tight">{{ $arr?->format('H:i') ?? '—' }}</div>
                  <div class="text-xs text-slate-400 font-bold mt-2 truncate max-w-[100px]">{{ $trip->route?->destinationCity?->name }}</div>
                </div>
              </div>

              {{-- Fare + CTA --}}
              <div class="text-right shrink-0 lg:ml-auto w-full lg:w-auto flex flex-col items-end justify-center">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 leading-none">per person</div>
                <div class="text-3xl font-black text-orange-600 mt-1 tracking-tight leading-none">
                  ₱{{ number_format($trip->fare, 0) }}
                </div>

                @auth
                  @if(auth()->user()->discountType?->percentage > 0)
                    @php $discounted = auth()->user()->calculateFare((float)$trip->fare); @endphp
                    <div class="text-xs text-emerald-600 font-semibold mt-1">
                      You pay ₱{{ number_format($discounted, 0) }}
                      <span class="text-slate-400 font-normal">
                        ({{ number_format(auth()->user()->discountType->percentage * 100, 0) }}% off)
                      </span>
                    </div>
                  @endif
                @endauth

                <button onclick="bookTrip({{ $trip->id }})"
                        class="mt-3 px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white
                               text-xs font-bold rounded-full transition-all shadow-sm shadow-orange-600/10 hover:shadow-md hover:translate-y-[-1px] active:translate-y-0 flex items-center gap-1.5">
                  <span>{{ $isReturnLegSearch ? 'Select Return Seat' : 'Select Seat' }}</span>
                  <i data-lucide="arrow-right" style="width:13px;height:13px"></i>
                </button>
              </div>

            </div>

            {{-- Footer: amenities + seat count --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-slate-100">
              <div class="flex flex-wrap gap-2">
                @foreach($trip->bus?->amenities ?? [] as $amenity)
                  <span class="text-[11px] text-slate-600 font-bold bg-slate-100/70 px-3 py-1 rounded-full border border-slate-200/20 shadow-sm">
                    {{ $amenity->display_name }}
                  </span>
                @endforeach
              </div>
              <div class="flex items-center gap-1.5 text-xs font-bold
                          {{ $seatsLow ? 'text-red-600' : 'text-emerald-600' }}">
                <i data-lucide="{{ $seatsLow ? 'alert-circle' : 'check-circle' }}"
                   style="width:14px;height:14px"></i>
                {{ $trip->available_seats }} seat{{ $trip->available_seats !== 1 ? 's' : '' }}
                {{ $seatsLow ? 'left — book fast!' : 'available' }}
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

  @elseif(!empty($prefill['search_from']) && !empty($prefill['search_to']))

    {{-- ══ NO RESULTS — show upcoming trips for this route ══ --}}
    <div class="max-w-4xl mx-auto">
      <div class="text-center py-8 mb-8">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i data-lucide="calendar-x" style="width:28px;height:28px;color:#94a3b8"></i>
        </div>

        <h3 class="text-lg font-extrabold text-slate-800 mb-1">No trips on this date</h3>
        <p class="text-sm text-slate-500 mb-2">
          <span class="font-semibold text-slate-700">
            {{ $prefill['search_from'] }} → {{ $prefill['search_to'] }}
          </span>
          has no available trips on
          <span class="font-semibold text-slate-700">
            {{ \Carbon\Carbon::parse($prefill['search_date'])->format('D, M j Y') }}
          </span>.
        </p>

        @if($alternativeDates->isNotEmpty())
          <p class="text-sm text-slate-500 mb-4">Here are the nearest available dates for this route:</p>
          <div class="flex flex-wrap gap-2 justify-center mb-6">
            @foreach($alternativeDates as $altDate)
              <a href="{{ route('landing.ticket_booking') }}?from={{ urlencode($prefill['from']) }}&to={{ urlencode($prefill['to']) }}&date={{ $altDate->toDateString() }}{{ $isRoundTrip ? '&trip_type=round_trip&return_date='.urlencode($prefill['return_date'] ?? '').'&leg='.$currentLeg.($prefill['outbound_booking_id'] ? '&outbound_booking_id='.$prefill['outbound_booking_id'] : '') : '' }}"
                 class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200
                        rounded-xl text-sm font-semibold text-slate-700 hover:border-primary-400
                        hover:text-primary-700 hover:bg-primary-50 transition-all shadow-sm">
                <i data-lucide="calendar" style="width:13px;height:13px;color:#ea580c"></i>
                {{ $altDate->format('D, M j') }}
              </a>
            @endforeach
          </div>
        @endif
      </div>

      @if($upcomingTrips->isNotEmpty())
        <div class="bg-gradient-to-r from-primary-50 to-emerald-50 border border-primary-200 rounded-2xl p-6 mb-8">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
              <i data-lucide="calendar-check" style="width:20px;height:20px;color:white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-800">Upcoming Scheduled Trips</h3>
              <p class="text-sm text-slate-600">
                Book your seat in advance for {{ $prefill['search_from'] }} → {{ $prefill['search_to'] }}
              </p>
            </div>
          </div>

          @foreach($upcomingTrips as $date => $tripsForDate)
            <div class="mb-6 last:mb-0">
              <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm">
                  <i data-lucide="calendar" style="width:14px;height:14px;color:#ea580c"></i>
                </div>
                <h4 class="font-bold text-slate-800">
                  {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </h4>
                <span class="text-xs text-slate-500 bg-white px-2 py-1 rounded-full">
                  {{ $tripsForDate->count() }} trip{{ $tripsForDate->count() !== 1 ? 's' : '' }}
                </span>
              </div>

              <div class="space-y-3">
                @foreach($tripsForDate as $trip)
                  @php
                    $dep     = $trip->departure_time;
                    $arr     = $trip->arrival_time;
                    $dur     = $trip->route?->estimated_duration_minutes;
                    $durStr  = $dur ? floor($dur/60).'h '.str_pad($dur%60,2,'0',STR_PAD_LEFT).'m' : '—';
                    $type    = strtolower($trip->bus?->default_seat_type ?? 'economy');
                    $typeBadge = match($type) {
                      'business' => 'bg-amber-100 text-amber-700',
                      'sleeper'  => 'bg-violet-100 text-violet-700',
                      default    => 'bg-emerald-100 text-emerald-700',
                    };
                  @endphp

                  <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                      <div class="flex items-center gap-3 shrink-0">
                        <div class="text-center">
                          <div class="text-lg font-bold text-slate-900">{{ $dep->format('H:i') }}</div>
                          <div class="text-[10px] text-slate-400">Departure</div>
                        </div>
                        <div class="flex items-center gap-1">
                          <i data-lucide="arrow-right" style="width:12px;height:12px;color:#94a3b8"></i>
                          <span class="text-xs text-slate-500">{{ $durStr }}</span>
                        </div>
                        <div class="text-center">
                          <div class="text-lg font-bold text-slate-900">
                            {{ $arr?->format('H:i') ?? '—' }}
                          </div>
                          <div class="text-[10px] text-slate-400">Arrival</div>
                        </div>
                      </div>

                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                          <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $typeBadge }}">
                            {{ ucfirst($type) }}
                          </span>
                          <span class="text-xs text-slate-400">
                            {{ $trip->bus?->type?->type_name }}
                          </span>
                        </div>
                        <div class="text-sm font-semibold text-slate-900 truncate">
                          {{ $trip->bus?->bus_name ?? 'Mindanao Express Bus' }}
                        </div>
                        @if($trip->departureTerminal)
                          <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                            <i data-lucide="building-2" style="width:10px;height:10px"></i>
                            {{ $trip->departureTerminal->name }}
                          </div>
                        @endif
                      </div>

                      <div class="text-right shrink-0">
                        <div class="text-xs text-slate-400">per person</div>
                        <div class="text-lg font-bold text-primary-600">
                          ₱{{ number_format($trip->fare, 0) }}
                        </div>
                        <div class="text-xs text-emerald-600 font-semibold mb-2">
                          {{ $trip->available_seats }} seats available
                        </div>
                        <a href="{{ route('landing.ticket_booking') }}?from={{ urlencode($prefill['from']) }}&to={{ urlencode($prefill['to']) }}&date={{ $date }}{{ $isRoundTrip ? '&trip_type=round_trip&return_date='.urlencode($prefill['return_date'] ?? '').'&leg='.$currentLeg.($prefill['outbound_booking_id'] ? '&outbound_booking_id='.$prefill['outbound_booking_id'] : '') : '' }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-600 hover:bg-primary-700
                                  text-white text-xs font-bold rounded-lg transition-colors">
                          <i data-lucide="calendar-plus" style="width:12px;height:12px"></i>
                          Book This Date
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl
                    px-4 py-3 text-left mb-6 text-sm text-amber-700">
          <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;margin-top:1px"></i>
          <span>
            No upcoming trips are currently scheduled for this route.
            Try searching a different route or check back later.
          </span>
        </div>
      @endif

      <div class="flex flex-wrap gap-3 justify-center">
        <a href="{{ route('landing.booking_routes') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700
                  text-white text-sm font-bold rounded-xl transition-colors">
          <i data-lucide="map" style="width:14px;height:14px"></i> Browse All Routes
        </a>
        <a href="{{ route('landing.ticket_booking') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 border border-slate-200
                  text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
          <i data-lucide="refresh-cw" style="width:14px;height:14px"></i> New Search
        </a>
      </div>
    </div>

  @else

    {{-- ══ INITIAL EMPTY STATE (no search yet) ══ --}}
    <div class="text-center py-20">
      <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i data-lucide="bus" style="width:28px;height:28px;color:#ea580c"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">Find your next trip</h3>
      <p class="text-sm text-slate-500 max-w-xs mx-auto">
        Select your origin, destination, and travel date above to see all available buses.
      </p>
    </div>

  @endif

</div>

{{-- Seat selection bottom sheet (redBus-style) --}}
<div id="seat-sheet-overlay" class="fixed inset-0 z-[200]" data-mode="modal">
  <div class="absolute inset-0 bg-black/50" onclick="SeatSheet.close()"></div>
  <div id="seat-sheet-panel"
       class="fixed left-0 right-0 bottom-0 top-[10vh] bg-white rounded-t-2xl shadow-2xl flex flex-col overflow-hidden">
    <div id="seat-sheet-content" class="flex-1 flex flex-col min-h-0 overflow-hidden"></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // ── Swap city dropdowns ──────────────────────────────────────────
  function swapCities() {
    const from = document.querySelector('select[name="from"]');
    const to   = document.querySelector('select[name="to"]');
    [from.value, to.value] = [to.value, from.value];
  }

  // ── Trip-type toggle on results page ─────────────────────────────
  function setSearchTripType(type) {
    const input = document.getElementById('search-trip-type');
    if (input) input.value = type;

    const ow = document.getElementById('sb-tt-oneway');
    const rt = document.getElementById('sb-tt-roundtrip');
    const wrap = document.getElementById('sb-return-date-wrap');
    const ret  = document.getElementById('sb-return-date');
    if (!ow || !rt) return;

    const active = ['bg-primary-600', 'text-white', 'shadow-sm'];
    const idle   = ['bg-slate-100', 'text-slate-600', 'hover:bg-slate-200'];

    if (type === 'round_trip') {
      rt.classList.add(...active); rt.classList.remove(...idle);
      ow.classList.add(...idle);   ow.classList.remove(...active);
      wrap?.classList.remove('hidden');
      if (ret) ret.required = true;
    } else {
      ow.classList.add(...active); ow.classList.remove(...idle);
      rt.classList.add(...idle);   rt.classList.remove(...active);
      wrap?.classList.add('hidden');
      if (ret) { ret.required = false; ret.value = ''; }
    }
  }

  // ── Sort trip cards ──────────────────────────────────────────────
  function sortTrips(key) {
    document.querySelectorAll('[data-sort]').forEach(btn => {
      const active = btn.dataset.sort === key;
      btn.className = `px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
        ${active
          ? 'bg-primary-600 text-white border-primary-600'
          : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'}`;
    });

    const list  = document.getElementById('trip-list');
    const cards = [...list.querySelectorAll('.trip-card')];
    const getVal = {
      departure: c => c.dataset.departure,
      price:     c => parseFloat(c.dataset.price),
      seats:     c => -parseInt(c.dataset.seats),
    };
    cards.sort((a, b) => {
      const va = getVal[key](a), vb = getVal[key](b);
      return va < vb ? -1 : va > vb ? 1 : 0;
    });
    cards.forEach(c => list.appendChild(c));
  }

  // ── Filter by seat class ─────────────────────────────────────────
  function filterClass(cls) {
    document.querySelectorAll('[data-filter]').forEach(btn => {
      const active = btn.dataset.filter === cls;
      btn.className = `px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors
        ${active
          ? 'bg-slate-800 text-white border-slate-800'
          : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'}`;
    });
    document.querySelectorAll('#trip-list .trip-card').forEach(card => {
      card.style.display = (cls === 'all' || card.dataset.class === cls) ? '' : 'none';
    });
  }

  // ── Book trip (auth guard) — carry round-trip context ───────────
  function bookTrip(tripId) {
    const params  = new URLSearchParams();
    const tripType = @json($prefill['trip_type'] ?? 'one_way');
    const leg      = @json($prefill['leg'] ?? 'outbound');
    const retDate  = @json($prefill['return_date'] ?? null);
    const outId    = @json($prefill['outbound_booking_id'] ?? null);
    const fromVal  = @json($prefill['from'] ?? null);
    const toVal    = @json($prefill['to'] ?? null);
    const dateVal  = @json($prefill['date'] ?? null);

    if (tripType === 'round_trip') {
      params.set('trip_type', 'round_trip');
      params.set('leg', leg);
      if (retDate) params.set('return_date', retDate);
      if (outId)   params.set('outbound_booking_id', outId);
      if (fromVal) params.set('from', fromVal);
      if (toVal)   params.set('to', toVal);
      if (dateVal) params.set('date', dateVal);
    }

    const qs = params.toString();
    const url = '/select-seats/' + tripId + (qs ? '?' + qs : '');
    @auth
      if (typeof SeatSheet !== 'undefined') {
        SeatSheet.open(url);
      } else {
        window.location.href = url;
      }
    @else
      requireAuth(url);
    @endauth
  }
</script>
@include('components.partials.seat-sheet-scripts')
@endpush
