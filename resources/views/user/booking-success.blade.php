@extends('layouts.app')
@section('title', 'Booking Success — Mindanao Express')

@push('head')
<style>
    .ticket-container {
        position: relative;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 0 auto;
        overflow: hidden;
    }
    
    .ticket-header {
        background: #f8fafc;
        padding: 32px;
        text-align: center;
        border-bottom: 2px dashed #cbd5e1;
        position: relative;
    }
    
    .ticket-header::before, .ticket-header::after {
        content: '';
        position: absolute;
        bottom: -12px;
        width: 24px;
        height: 24px;
        background: #f1f5f9; /* Matches body background */
        border-radius: 50%;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .ticket-header::before { left: -12px; }
    .ticket-header::after { right: -12px; }
    
    .ticket-body { padding: 40px 32px; }
    
    .qr-placeholder {
        width: 140px;
        height: 140px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background-image: repeating-linear-gradient(45deg, #f8fafc 0, #f8fafc 10px, #ffffff 10px, #ffffff 20px);
    }

    @media print {
        body { background: white !important; }
        .min-h-\[calc\(100vh-80px\)\] { min-height: auto !important; padding: 0 !important; }
        nav, header, footer, .print\:hidden { display: none !important; }
        .ticket-container {
            box-shadow: none !important;
            border: 2px solid #e2e8f0;
            max-width: 100% !important;
            margin: 0 !important;
            page-break-inside: avoid;
        }
        .text-center.mb-8 { display: none !important; }
    }
</style>
@endpush

@section('content')

<div class="bg-slate-100 min-h-[calc(100vh-80px)] py-12 px-4 sm:px-6">
    
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
            <i data-lucide="check-circle" style="width:40px;height:40px"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900">Payment Successful!</h1>
        <p class="text-slate-500 mt-2">Your booking has been confirmed and your digital ticket is ready.</p>
    </div>

    @php
        $rb = $booking->returnBooking;
        $isRT = $booking->trip_type === 'round_trip' && $rb;
        $legs = collect([
            ['label' => $isRT ? 'Outbound' : null, 'booking' => $booking],
        ]);
        if ($isRT) {
            $legs->push(['label' => 'Return', 'booking' => $rb]);
        }
    @endphp

    @foreach($legs as $leg)
        @php
            $lb = $leg['booking'];
            $legLabel = $leg['label'];
        @endphp
        <div class="ticket-container {{ !$loop->first ? 'mt-8' : '' }}">
            {{-- Header --}}
            <div class="ticket-header">
                <div class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">
                    Boarding Pass{{ $legLabel ? ' · ' . $legLabel : '' }}
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $lb->booking_reference }}</h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full mt-3">
                    <i data-lucide="badge-check" style="width:14px;height:14px"></i>
                    Confirmed & Paid
                </div>
            </div>

            {{-- Body --}}
            <div class="ticket-body">

                {{-- Route Info --}}
                <div class="flex items-center justify-between mb-8">
                    <div class="text-center w-1/3">
                        <div class="text-3xl font-extrabold text-slate-900">{{ strtoupper(substr($lb->trip->route?->originCity?->name, 0, 3)) }}</div>
                        <div class="text-xs text-slate-500 mt-1 truncate">{{ $lb->trip->route?->originCity?->name }}</div>
                    </div>

                    <div class="flex-1 px-4 relative">
                        <div class="h-px bg-slate-200 w-full absolute top-1/2 left-0 -translate-y-1/2"></div>
                        <div class="w-8 h-8 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto relative z-10 text-primary-500 shadow-sm">
                            <i data-lucide="bus" style="width:14px;height:14px"></i>
                        </div>
                    </div>

                    <div class="text-center w-1/3">
                        <div class="text-3xl font-extrabold text-slate-900">{{ strtoupper(substr($lb->trip->route?->destinationCity?->name, 0, 3)) }}</div>
                        <div class="text-xs text-slate-500 mt-1 truncate">{{ $lb->trip->route?->destinationCity?->name }}</div>
                    </div>
                </div>

                {{-- Details Grid --}}
                <div class="grid grid-cols-2 gap-y-6 gap-x-4 mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Departure Date</div>
                        <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($lb->trip->trip_date)->format('M j, Y') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Time</div>
                        <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($lb->departure_time_for_stop)->format('h:i A') }}</div>
                    </div>
                    @if($lb->boardingStop)
                        <div class="col-span-2">
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Boarding Point</div>
                            <div class="font-bold text-slate-900">{{ $lb->boardingStop->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $lb->boardingStop->address }}</div>
                        </div>
                    @endif
                    @if($lb->droppingStop)
                        <div class="col-span-2">
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Dropping Point</div>
                            <div class="font-bold text-slate-900">{{ $lb->droppingStop->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $lb->droppingStop->address }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Bus Class</div>
                        <div class="font-bold text-slate-900">{{ $lb->trip->bus?->type?->type_name ?? 'Economy' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Seat(s)</div>
                        <div class="font-bold text-primary-600 text-lg leading-none">
                            {{ implode(', ', $lb->bookingSeats->pluck('seat_number')->toArray()) }}
                        </div>
                    </div>
                </div>

                {{-- QR Code --}}
                <div class="text-center">
                    <div class="qr-placeholder mb-3">
                        <i data-lucide="qr-code" style="width:64px;height:64px;color:#94a3b8;opacity:0.5"></i>
                    </div>
                    <p class="text-xs text-slate-500">Scan at boarding</p>
                </div>

            </div>
        </div>
    @endforeach

    {{-- Actions --}}
    <div class="max-w-[600px] mx-auto mt-8 flex flex-wrap gap-4 justify-center print:hidden">
        <button onclick="window.print()" class="px-6 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2">
            <i data-lucide="download" style="width:16px;height:16px"></i> Download PDF
        </button>
        <a href="{{ route('landing.home') }}" class="px-6 py-3 bg-primary-600 rounded-xl font-bold text-white hover:bg-primary-700 shadow-lg shadow-primary-500/30 transition-all flex items-center gap-2">
            Back to Home
        </a>
    </div>

</div>

@endsection
