@extends('layouts.app')
@section('title', 'Checkout — Mindanao Express')

@push('head')
<style>
    .payment-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .payment-method-card:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
    }
    input[type="radio"]:checked + .payment-method-card {
        border-color: #f97316;
        background-color: #fff7ed;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
    }
    input[type="radio"]:checked + .payment-method-card .radio-circle {
        border-color: #f97316;
        background-color: #f97316;
    }
    input[type="radio"]:checked + .payment-method-card .radio-circle::after {
        content: '';
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        display: block;
    }
    .radio-circle {
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .card-input {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px;
        width: 100%;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .card-input:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }
</style>
@endpush

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="bg-slate-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white">
            Secure <span class="text-primary-400">Checkout</span>
        </h1>
        <p class="text-slate-400 mt-2 text-sm">Select a payment method to complete your booking.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form action="{{ route('user.booking.pay', $booking->id) }}" method="POST" id="checkout-form">
        @csrf

        <div class="flex flex-col lg:flex-row gap-10">
            
            {{-- ── LEFT PANEL: PAYMENT METHODS ──────────────────────────────── --}}
            <div class="flex-1 space-y-6">
                
                <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i data-lucide="credit-card" style="width:20px;height:20px;color:#ea580c"></i>
                        Payment Method
                    </h2>

                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
                            <i data-lucide="alert-circle" style="width:20px;height:20px;color:#dc2626"></i>
                            <span class="font-medium">{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <div class="space-y-4">
                        {{-- E-Wallets --}}
                        <label class="block relative cursor-pointer">
                            <input type="radio" name="payment_method" value="gcash" class="sr-only" required checked onchange="toggleCardDetails()">
                            <div class="payment-method-card">
                                <div class="radio-circle"></div>
                                <div class="w-12 h-8 bg-blue-500 rounded-md flex items-center justify-center text-white font-black text-xs italic tracking-tighter">GCash</div>
                                <div class="flex-1">
                                    <div class="font-bold text-slate-800">GCash</div>
                                    <div class="text-xs text-slate-500">Pay via GCash app</div>
                                </div>
                            </div>
                        </label>

                        <label class="block relative cursor-pointer">
                            <input type="radio" name="payment_method" value="paymaya" class="sr-only" required onchange="toggleCardDetails()">
                            <div class="payment-method-card">
                                <div class="radio-circle"></div>
                                <div class="w-12 h-8 bg-emerald-500 rounded-md flex items-center justify-center text-white font-black text-xs tracking-tighter">Maya</div>
                                <div class="flex-1">
                                    <div class="font-bold text-slate-800">Maya</div>
                                    <div class="text-xs text-slate-500">Pay via Maya app</div>
                                </div>
                            </div>
                        </label>

                        {{-- Credit/Debit Card --}}
                        <label class="block relative cursor-pointer">
                            <input type="radio" name="payment_method" value="credit_card" class="sr-only" required onchange="toggleCardDetails()">
                            <div class="payment-method-card">
                                <div class="radio-circle"></div>
                                <div class="w-12 h-8 bg-slate-800 rounded-md flex items-center justify-center text-white">
                                    <i data-lucide="credit-card" style="width:16px;height:16px"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-slate-800">Credit or Debit Card</div>
                                    <div class="text-xs text-slate-500">Visa, Mastercard, JCB</div>
                                </div>
                            </div>
                        </label>

                        {{-- Simulated Card Details (Shown only when Credit Card is selected) --}}
                        <div id="card-details-form" class="hidden mt-4 pl-12 pr-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Card Number</label>
                                <input type="text" placeholder="0000 0000 0000 0000" class="card-input" maxlength="19">
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Expiry Date</label>
                                    <input type="text" placeholder="MM/YY" class="card-input" maxlength="5">
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">CVC</label>
                                    <input type="text" placeholder="123" class="card-input" maxlength="4">
                                </div>
                            </div>
                        </div>

                        {{-- Over the counter --}}
                        <label class="block relative cursor-pointer">
                            <input type="radio" name="payment_method" value="otc" class="sr-only" required onchange="toggleCardDetails()">
                            <div class="payment-method-card">
                                <div class="radio-circle"></div>
                                <div class="w-12 h-8 bg-amber-100 rounded-md flex items-center justify-center text-amber-700">
                                    <i data-lucide="store" style="width:16px;height:16px"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-slate-800">Over-the-Counter</div>
                                    <div class="text-xs text-slate-500">7-Eleven, Cebuana, M Lhuillier</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center text-sm text-slate-500">
                    <i data-lucide="lock" style="width:16px;height:16px;display:inline-block;margin-bottom:4px;color:#94a3b8"></i><br>
                    Payments are 100% secure and encrypted.
                </div>

            </div>

            {{-- ── RIGHT PANEL: SUMMARY ───────────────────── --}}
            <div class="w-full lg:w-[400px] shrink-0">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-24">
                    
                    @php
                        $rb = $booking->returnBooking;
                        $isRT = $booking->trip_type === 'round_trip' && $rb;
                        $combinedBase     = (float) $booking->base_fare + ($isRT ? (float) $rb->base_fare : 0);
                        $combinedDiscount = (float) $booking->discount_amount + ($isRT ? (float) $rb->discount_amount : 0);
                        $combinedTotal    = $combinedBase - $combinedDiscount;
                        $combinedSeats    = $booking->bookingSeats->count() + ($isRT ? $rb->bookingSeats->count() : 0);
                    @endphp

                    {{-- Trip Summary --}}
                    <div class="p-6 bg-slate-50 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">
                            Order Summary
                            @if($isRT)
                                <span class="ml-2 text-[10px] font-bold uppercase bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">Round-trip</span>
                            @endif
                        </h3>

                        @if($isRT)
                            <div class="space-y-3">
                                <div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400 mb-1">Outbound</div>
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $booking->trip->route?->originCity?->name }} → {{ $booking->trip->route?->destinationCity?->name }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($booking->trip->trip_date)->format('M j, Y') }}
                                        · {{ $booking->bookingSeats->count() }} seat(s)
                                    </div>
                                </div>
                                <div class="border-t border-slate-200/70"></div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400 mb-1">Return</div>
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $rb->trip->route?->originCity?->name }} → {{ $rb->trip->route?->destinationCity?->name }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($rb->trip->trip_date)->format('M j, Y') }}
                                        · {{ $rb->bookingSeats->count() }} seat(s)
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-600">Route</span>
                                <span class="font-semibold text-slate-900">{{ $booking->trip->route?->originCity?->name }} → {{ $booking->trip->route?->destinationCity?->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-600">Date</span>
                                <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->trip->trip_date)->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Passengers</span>
                                <span class="font-semibold text-slate-900">{{ $booking->bookingSeats->count() }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Fare Breakdown --}}
                    <div class="p-6">
                        <div class="space-y-3 mb-6 pb-6 border-b border-slate-200 border-dashed">
                            @if($isRT)
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Outbound base fare</span>
                                    <span class="font-medium">₱{{ number_format($booking->base_fare, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Return base fare</span>
                                    <span class="font-medium">₱{{ number_format($rb->base_fare, 2) }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Base Fare</span>
                                    <span class="font-medium">₱{{ number_format($booking->base_fare, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm text-emerald-600">
                                <span>Total Discounts</span>
                                <span class="font-medium">- ₱{{ number_format($combinedDiscount, 2) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end mb-6">
                            <div class="text-sm font-bold text-slate-800">Total Payable</div>
                            <div class="text-3xl font-extrabold text-primary-600">
                                ₱{{ number_format($combinedTotal, 2) }}
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-bold text-white transition-all bg-primary-600 hover:bg-primary-700 shadow-lg shadow-primary-500/30 flex items-center justify-center gap-2">
                            <i data-lucide="shield-check" style="width:18px;height:18px"></i> Pay Securely
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
    function toggleCardDetails() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const cardDetails = document.getElementById('card-details-form');
        
        if (method === 'credit_card') {
            cardDetails.classList.remove('hidden');
        } else {
            cardDetails.classList.add('hidden');
        }
    }
    
    // Run on load
    toggleCardDetails();
</script>
@endpush
