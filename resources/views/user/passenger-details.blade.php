@extends('layouts.app')
@section('title', 'Passenger Details — Mindanao Express')

@php
    $returnBooking = $booking->returnBooking;
    $isRoundTrip   = $booking->trip_type === 'round_trip' && $returnBooking;

    // Build a flat list of "legs" we need to render passenger forms for.
    $legs = collect([
        ['label' => $isRoundTrip ? 'Outbound' : null, 'booking' => $booking],
    ]);
    if ($isRoundTrip) {
        $legs->push(['label' => 'Return', 'booking' => $returnBooking]);
    }

    $combinedBaseFare = (float) $booking->base_fare + ($isRoundTrip ? (float) $returnBooking->base_fare : 0);
@endphp

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="bg-slate-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('landing.ticket_booking') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm mb-4">
            <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Start over
        </a>
        <h1 class="text-3xl font-extrabold text-white">
            Passenger <span class="text-primary-400">Details</span>
        </h1>
        @if($isRoundTrip)
            <p class="text-slate-400 text-sm mt-1">
                Round-trip booking — {{ $legs->count() }} legs, {{ $booking->bookingSeats->count() + $returnBooking->bookingSeats->count() }} passenger entries.
            </p>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form action="{{ route('user.booking.store_details', $booking->id) }}" method="POST" id="passenger-form">
        @csrf

        <div class="flex flex-col lg:flex-row gap-10">

            {{-- ── LEFT PANEL: PASSENGER FORMS (per leg) ──────────────────── --}}
            <div class="flex-1 space-y-6">

                @foreach($legs as $legIdx => $leg)
                    @php
                        $legBooking = $leg['booking'];
                        $legLabel   = $leg['label'];
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                        @if($legLabel)
                            <div class="mb-4 pb-4 border-b border-slate-100 flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $legLabel === 'Outbound' ? 'bg-primary-100 text-primary-700' : 'bg-emerald-100 text-emerald-700' }} font-extrabold text-sm">
                                    {{ $legIdx + 1 }}
                                </span>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">{{ $legLabel }} Trip</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $legBooking->trip?->route?->originCity?->name }} →
                                        {{ $legBooking->trip?->route?->destinationCity?->name }}
                                        · {{ \Carbon\Carbon::parse($legBooking->trip?->trip_date)->format('D, M j, Y') }}
                                        · {{ \Carbon\Carbon::parse($legBooking->trip?->departure_time)->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                                <i data-lucide="users" style="width:20px;height:20px;color:#ea580c"></i>
                                Who's traveling?
                            </h2>
                        @endif

                        <div class="space-y-8">
                            @foreach($legBooking->bookingSeats as $index => $seat)
                                <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-200">
                                        <div class="w-10 h-10 bg-primary-100 text-primary-700 font-bold flex items-center justify-center rounded-lg">
                                            {{ $seat->seat_number }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800">Passenger {{ $index + 1 }}{{ $legLabel ? ' ('.$legLabel.')' : '' }}</h3>
                                            <div class="text-xs text-slate-500">Seat {{ $seat->seat_number }}</div>
                                        </div>
                                        <div class="ml-auto text-right">
                                            <div class="text-sm font-bold text-slate-900">₱{{ number_format($seat->fare, 2) }}</div>
                                            <div class="text-[10px] text-slate-400 uppercase tracking-widest">Base Fare</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {{-- Full Name --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name</label>
                                            <div class="relative">
                                                <i data-lucide="user" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                                                <input type="text" name="passengers[{{ $seat->id }}][name]" required
                                                       value="{{ old('passengers.'.$seat->id.'.name', $seat->passenger_name) }}"
                                                       placeholder="e.g. Juan Dela Cruz"
                                                       class="w-full pl-9 pr-4 py-3 text-sm border border-slate-300 rounded-xl
                                                              focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white transition-shadow">
                                            </div>
                                        </div>

                                        {{-- Discount Type --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Passenger Type</label>
                                            <div class="relative">
                                                <i data-lucide="tag" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                                                <select name="passengers[{{ $seat->id }}][discount_type_id]"
                                                        class="passenger-type-select w-full pl-9 pr-4 py-3 text-sm border border-slate-300 rounded-xl
                                                               focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white appearance-none transition-shadow"
                                                        data-fare="{{ $seat->fare }}">
                                                    <option value="" data-pct="0">Regular (No Discount)</option>
                                                    @foreach($discountTypes as $discount)
                                                        <option value="{{ $discount->id }}" data-pct="{{ $discount->percentage }}"
                                                                {{ old('passengers.'.$seat->id.'.discount_type_id') == $discount->id ? 'selected' : '' }}>
                                                            {{ $discount->display_name }} ({{ floatval($discount->percentage) * 100 }}% Off)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down" style="width:14px;height:14px;position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="bg-primary-50 border border-primary-200 rounded-2xl p-6 flex items-start gap-4">
                    <i data-lucide="info" style="width:24px;height:24px;color:#ea580c;flex-shrink:0;margin-top:2px;"></i>
                    <p class="text-sm text-primary-800 leading-relaxed">
                        <strong>Note:</strong> If you apply a Senior Citizen, PWD, or Student discount, you will be required to present a valid ID upon boarding. Failure to present a valid ID will result in the forfeiture of the discount and the difference must be paid in full.
                    </p>
                </div>

            </div>

            {{-- ── RIGHT PANEL: SUMMARY & CHECKOUT ───────────────────── --}}
            <div class="w-full lg:w-[400px] shrink-0">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-24">

                    {{-- Trip Summary Header --}}
                    <div class="p-6 bg-slate-50 border-b border-slate-200 space-y-4">
                        @foreach($legs as $leg)
                            @php $lb = $leg['booking']; @endphp
                            <div>
                                @if($leg['label'])
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">{{ $leg['label'] }}</div>
                                @endif
                                <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                                    <i data-lucide="calendar" style="width:14px;height:14px"></i>
                                    {{ \Carbon\Carbon::parse($lb->trip->trip_date)->format('D, M j, Y') }}
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-lg font-extrabold text-slate-900">{{ \Carbon\Carbon::parse($lb->trip->departure_time)->format('h:i A') }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $lb->trip->route?->originCity?->name }}</div>
                                    </div>
                                    <div class="flex-1 px-3 flex items-center">
                                        <div class="h-px bg-slate-300 flex-1"></div>
                                        <i data-lucide="bus" style="width:14px;height:14px;color:#ea580c;margin:0 6px"></i>
                                        <div class="h-px bg-slate-300 flex-1"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-extrabold text-slate-900">{{ $lb->trip->arrival_time ? \Carbon\Carbon::parse($lb->trip->arrival_time)->format('h:i A') : '—' }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $lb->trip->route?->destinationCity?->name }}</div>
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="border-t border-slate-200/70"></div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Fare Summary --}}
                    <div class="p-6">
                        <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Fare Summary</h3>

                        <div class="space-y-3 mb-6 pb-6 border-b border-slate-200 border-dashed">
                            @if($isRoundTrip)
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Outbound ({{ $booking->bookingSeats->count() }} seat{{ $booking->bookingSeats->count() > 1 ? 's' : '' }})</span>
                                    <span class="font-semibold text-slate-900">₱{{ number_format($booking->base_fare, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Return ({{ $returnBooking->bookingSeats->count() }} seat{{ $returnBooking->bookingSeats->count() > 1 ? 's' : '' }})</span>
                                    <span class="font-semibold text-slate-900">₱{{ number_format($returnBooking->base_fare, 2) }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Base Fare ({{ $booking->bookingSeats->count() }} Seat{{ $booking->bookingSeats->count() > 1 ? 's' : '' }})</span>
                                    <span class="font-semibold text-slate-900">₱{{ number_format($booking->base_fare, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm text-emerald-600 font-medium">
                                <span>Total Discounts</span>
                                <span id="summary-discount">- ₱0.00</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end mb-6">
                            <div class="text-sm font-bold text-slate-800">Total Payable</div>
                            <div class="text-3xl font-extrabold text-primary-600">
                                ₱<span id="summary-total">{{ number_format($combinedBaseFare, 2) }}</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-bold text-white transition-all bg-primary-600 hover:bg-primary-700 shadow-lg shadow-primary-500/30">
                            Proceed to Payment →
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.passenger-type-select');
        const discountSpan = document.getElementById('summary-discount');
        const totalSpan = document.getElementById('summary-total');
        const baseFare = {{ $combinedBaseFare }};

        function calculateTotals() {
            let totalDiscount = 0;

            selects.forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                const pct = parseFloat(selectedOption.dataset.pct || 0);
                const fare = parseFloat(select.dataset.fare || 0);

                totalDiscount += (fare * pct);
            });

            const finalTotal = baseFare - totalDiscount;

            discountSpan.textContent = `- ₱${totalDiscount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            totalSpan.textContent = finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        selects.forEach(select => {
            select.addEventListener('change', calculateTotals);
        });

        calculateTotals();
    });
</script>
@endpush
