@extends('layouts.app')
@section('title', 'Select Your Seat — Mindanao Express')

@push('head')
@include('components.partials.seat-sheet-styles')
@endpush

@section('content')

<div id="seat-sheet-overlay" class="fixed inset-0 z-[200] open" data-mode="page">
    <div class="absolute inset-0 bg-black/50"></div>
    <div id="seat-sheet-panel"
         class="fixed left-0 right-0 bottom-0 top-[10vh] bg-white rounded-t-2xl shadow-2xl flex flex-col overflow-hidden">
        <div id="seat-sheet-content" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <x-seat-selection-sheet
                :trip="$trip"
                :seatMap="$seatMap"
                :remainingAllowed="$remainingAllowed"
                :roundTripContext="$roundTripContext"
                :outboundBooking="$outboundBooking"
            />
        </div>
    </div>
</div>

@endsection

@push('scripts')
@include('components.partials.seat-sheet-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        SeatSheet.init(document.querySelector('.seat-sheet-root'));
        document.body.style.overflow = 'hidden';
    });
</script>
@endpush
