@extends('layouts.app')
@section('title', 'E-Ticket: ' . $booking->booking_reference)

@section('content')
<div class="bg-slate-900 py-10 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-3xl w-full">
    
    {{-- Header Actions --}}
    <div class="flex justify-between items-center mb-6 text-white">
      <a href="{{ route('manage.bookings') }}" class="inline-flex items-center gap-2 hover:text-primary-400 transition-colors">
        <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Back to Bookings
      </a>
      <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition-colors">
        <i data-lucide="printer" style="width:16px;height:16px"></i> Print Ticket
      </button>
    </div>

    {{-- Ticket Container --}}
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative">
      
      {{-- Left Side: Main Info --}}
      <div class="p-8 flex-1 border-b md:border-b-0 md:border-r border-dashed border-slate-300 relative">
        <div class="flex justify-between items-start mb-8">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                <i data-lucide="bus" style="width:16px;height:16px;color:#fff"></i>
              </div>
              <span class="text-lg font-extrabold tracking-tight text-slate-900">Mindanao<span class="text-primary-600">Express</span></span>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">Official E-Ticket</p>
          </div>
          <div class="text-right">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Booking Ref</p>
            <p class="text-xl font-mono font-extrabold text-slate-900">{{ $booking->booking_reference }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between mb-8">
          <div class="flex-1">
            <p class="text-3xl font-extrabold text-slate-900">{{ $booking->trip?->route?->originCity?->name ?? 'Origin' }}</p>
          </div>
          <div class="px-4 text-center">
            <i data-lucide="arrow-right" style="width:24px;height:24px;color:#cbd5e1" class="mx-auto"></i>
          </div>
          <div class="flex-1 text-right">
            <p class="text-3xl font-extrabold text-slate-900">{{ $booking->trip?->route?->destinationCity?->name ?? 'Destination' }}</p>
          </div>
        </div>

        @if($booking->boardingStop || $booking->droppingStop)
          <div class="mb-8 p-5 bg-slate-50 border border-slate-100 rounded-2xl text-xs space-y-2.5">
            @if($booking->boardingStop)
              <div class="flex items-start gap-2">
                <i data-lucide="map-pin" style="width:14px;height:14px;color:#ea580c;flex-shrink:0" class="mt-0.5"></i>
                <p class="text-slate-600 leading-normal">
                  <strong class="text-slate-800">Boarding Point:</strong>
                  {{ $booking->boardingStop->name }}
                  <span class="font-extrabold text-slate-900">({{ \Carbon\Carbon::parse($booking->departure_time_for_stop)->format('g:i A') }})</span>
                  @if($booking->boardingStop->address)
                    <br><span class="text-slate-400 font-medium">{{ $booking->boardingStop->address }}</span>
                  @endif
                </p>
              </div>
            @endif
            @if($booking->droppingStop)
              <div class="flex items-start gap-2 pt-2 border-t border-slate-200/50">
                <i data-lucide="map-pin" style="width:14px;height:14px;color:#10b981;flex-shrink:0" class="mt-0.5"></i>
                <p class="text-slate-600 leading-normal">
                  <strong class="text-slate-800">Dropping Point:</strong>
                  {{ $booking->droppingStop->name }}
                  <span class="font-extrabold text-slate-900">({{ \Carbon\Carbon::parse($booking->arrival_time_for_stop)->format('g:i A') }})</span>
                  @if($booking->droppingStop->address)
                    <br><span class="text-slate-400 font-medium">{{ $booking->droppingStop->address }}</span>
                  @endif
                </p>
              </div>
            @endif
          </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 bg-slate-50 rounded-2xl p-6 border border-slate-100">
          <div>
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Passenger</p>
            <p class="text-sm font-bold text-slate-900">{{ $booking->user->name }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Date & Time</p>
            <p class="text-sm font-bold text-slate-900">{{ $booking->trip?->departure_time?->format('M j, Y') ?? '—' }}</p>
            <p class="text-xs font-semibold text-slate-600">{{ \Carbon\Carbon::parse($booking->departure_time_for_stop)->format('g:i A') }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Seat(s)</p>
            <p class="text-lg font-extrabold text-primary-600">{{ $booking->seat_list }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Bus Type</p>
            <p class="text-sm font-bold text-slate-900">{{ $booking->trip?->bus?->type?->name ?? 'Standard Bus' }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Driver</p>
            <p class="text-sm font-bold text-slate-900">{{ $booking->trip?->driver?->user?->name ?? 'Assigned before departure' }}</p>
          </div>
        </div>

      </div>

      {{-- Right Side: Stub --}}
      <div class="p-8 w-full md:w-64 bg-slate-50 flex flex-col justify-between items-center text-center relative">
        {{-- Cutouts for realism --}}
        <div class="absolute -left-3 top-[-12px] w-6 h-6 bg-slate-900 rounded-full hidden md:block"></div>
        <div class="absolute -left-3 bottom-[-12px] w-6 h-6 bg-slate-900 rounded-full hidden md:block"></div>
        
        <div class="w-full">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Boarding Pass</p>
          <p class="text-lg font-mono font-extrabold text-slate-900 mb-6">{{ $booking->booking_reference }}</p>
          
          <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 inline-block mb-6">
            {{-- A placeholder barcode/QR. In a real app we'd generate a real SVG/PNG --}}
            <i data-lucide="qr-code" style="width:80px;height:80px;color:#0f172a"></i>
          </div>
          
          <div>
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Paid</p>
            <p class="text-2xl font-extrabold text-slate-900">{{ $booking->formatted_amount_paid }}</p>
            <span class="inline-block mt-2 px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wider">Paid</span>
          </div>
        </div>
      </div>

    </div>
    
    <p class="text-center text-slate-500 text-sm mt-6">
      Please present this e-ticket along with a valid ID upon boarding.
    </p>

  </div>
</div>

<style>
  @media print {
    body * { visibility: hidden; }
    .max-w-3xl, .max-w-3xl * { visibility: visible; }
    .max-w-3xl { position: absolute; left: 0; top: 0; width: 100%; transform: scale(0.9); transform-origin: top left; }
    .bg-slate-900 { background: white !important; }
    .text-white { color: black !important; }
    button, a { display: none !important; }
    /* Remove shadow for printing */
    .shadow-2xl { box-shadow: none !important; border: 1px solid #e2e8f0; }
  }
</style>
@endsection
