@extends('layouts.app')
@section('title', 'My Bookings — Mindanao Express')

@section('content')

{{-- ── PAGE HEADER ────────────────────────────────────────────────── --}}
<div class="bg-slate-900 py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-xs font-bold text-primary-400 uppercase tracking-widest mb-1">Dashboard</p>
      <h1 class="text-3xl font-extrabold text-white">My <span class="text-primary-400">Bookings</span></h1>
      <p class="text-slate-400 text-sm mt-1">Welcome back, {{ auth()->user()->name }}!</p>
    </div>
    <a href="{{ route('landing.ticket_booking') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition-colors">
      <i data-lucide="plus" style="width:14px;height:14px"></i> Book New Trip
    </a>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="flex flex-col lg:flex-row gap-8">

    {{-- ── SIDEBAR ─────────────────────────────────────────────────── --}}
    <aside class="w-full lg:w-64 shrink-0 space-y-4">

      {{-- User card --}}
      <div class="bg-white border border-slate-200 rounded-2xl p-5 text-center">
        <div class="w-14 h-14 rounded-2xl bg-primary-100 text-primary-700 text-xl font-extrabold flex items-center justify-center mx-auto mb-3">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</div>
        <div class="text-xs text-slate-500 mt-0.5 truncate">{{ auth()->user()->email }}</div>

        @if(auth()->user()->discountType && auth()->user()->discountType->percentage > 0)
          <div class="mt-2 inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">
            <i data-lucide="badge-percent" style="width:11px;height:11px"></i>
            {{ auth()->user()->discountType->display_name }}
            · {{ number_format(auth()->user()->discountType->percentage * 100, 0) }}% off
          </div>
        @endif

        <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
          <div>
            <div class="text-base font-extrabold text-slate-900">{{ $counts->total }}</div>
            <div class="text-[10px] text-slate-400">Total</div>
          </div>
          <div>
            <div class="text-base font-extrabold text-emerald-600">{{ $counts->completed }}</div>
            <div class="text-[10px] text-slate-400">Trips</div>
          </div>
          <div>
            <div class="text-base font-extrabold text-primary-600">₱{{ number_format($profileStats['totalSpent']/1000,1) }}k</div>
            <div class="text-[10px] text-slate-400">Spent</div>
          </div>
        </div>
      </div>

      {{-- Next trip --}}
      @if($nextTrip)
        <div class="bg-primary-600 rounded-2xl p-4 text-white">
          <div class="flex items-center gap-2 mb-3">
            <i data-lucide="clock" style="width:14px;height:14px"></i>
            <span class="text-xs font-bold uppercase tracking-wide">Next Trip</span>
          </div>
          <div class="text-xs text-primary-200 mb-1">{{ $nextTrip->booking_reference }}</div>
          <div class="text-sm font-bold">{{ $nextTrip->trip?->route?->originCity?->name }} → {{ $nextTrip->trip?->route?->destinationCity?->name }}</div>
          <div class="text-xs text-primary-200 mt-1 flex items-center gap-1">
            <i data-lucide="calendar" style="width:11px;height:11px"></i>
            {{ $nextTrip->trip?->departure_time?->format('M j, Y · g:i A') }}
          </div>
          <div class="mt-3 flex items-center justify-between">
            <span class="text-xs font-bold bg-white/20 px-2 py-0.5 rounded-full">Seat {{ $nextTrip->seat_number }}</span>
            <span class="text-xs font-extrabold">₱{{ number_format($nextTrip->amount_paid, 0) }}</span>
          </div>
        </div>
      @endif

      {{-- Quick nav --}}
      <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        @foreach([
          ['manage.bookings',  'all',       'all',       'All Bookings',   'ticket',    $counts->total],
          ['manage.bookings',  'confirmed', 'confirmed', 'Upcoming',       'clock',     $counts->confirmed],
          ['manage.bookings',  'completed', 'completed', 'Completed',      'check-circle', $counts->completed],
          ['manage.bookings',  'cancelled', 'cancelled', 'Cancelled',      'x-circle',  $counts->cancelled],
          ['manage.bookings',  'pending',   'pending',   'Pending',        'hourglass', $counts->pending],
        ] as [$r,$q,$cur,$lbl,$icon,$cnt])
          <a href="{{ route($r) }}?status={{ $q }}"
             class="flex items-center justify-between px-4 py-3 text-sm hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0
                    {{ ($status ?? 'all') === $cur ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700' }}">
            <div class="flex items-center gap-2.5">
              <i data-lucide="{{ $icon }}" style="width:14px;height:14px"></i> {{ $lbl }}
            </div>
            <span class="text-xs font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $cnt }}</span>
          </a>
        @endforeach
      </div>

      {{-- Notifications --}}
      @if($unreadCount > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
          <div class="flex items-center gap-2 mb-2">
            <i data-lucide="bell" style="width:14px;height:14px;color:#d97706"></i>
            <span class="text-xs font-bold text-amber-700">{{ $unreadCount }} Unread</span>
          </div>
          <form method="POST" action="{{ route('manage.bookings.notifications.read') }}">
            @csrf
            <button type="submit" class="text-xs text-amber-600 hover:text-amber-700 font-semibold">
              Mark all as read →
            </button>
          </form>
        </div>
      @endif

    </aside>

    {{-- ── MAIN ────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0">

      {{-- Search + sort bar --}}
      <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="relative flex-1 min-w-[200px]">
          <i data-lucide="search" style="width:13px;height:13px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
          <input type="text" placeholder="Search by reference or route…"
                 oninput="filterBookings(this.value)"
                 class="w-full pl-8 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <select onchange="location.href='?status={{ $status ?? 'all' }}&sort='+this.value"
                class="py-2.5 px-3 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
          <option value="newest"    {{ ($sort??'newest')==='newest'    ? 'selected' : '' }}>Newest First</option>
          <option value="oldest"    {{ ($sort??'')==='oldest'    ? 'selected' : '' }}>Oldest First</option>
          <option value="departure" {{ ($sort??'')==='departure' ? 'selected' : '' }}>By Departure</option>
        </select>
      </div>

      {{-- Booking cards --}}
      <div id="bookings-list" class="space-y-4">
        @forelse($bookings as $booking)
          @php
            $origin  = $booking->trip?->route?->originCity?->name ?? '—';
            $dest    = $booking->trip?->route?->destinationCity?->name ?? '—';
            $dep     = $booking->trip?->departure_time;
            $isPast  = $dep?->isPast();
            $rb      = $booking->returnBooking;
            $isRT    = $booking->trip_type === 'round_trip' && $rb;
            $combinedAmount = (float) $booking->amount_paid + ($isRT ? (float) $rb->amount_paid : 0);
            $combinedDiscount = (float) $booking->discount_amount + ($isRT ? (float) $rb->discount_amount : 0);
            $totalSeatCount = $booking->seat_count + ($isRT ? $rb->seat_count : 0);
            $statusMap = [
              'confirmed' => ['bg-blue-100 text-blue-700',   'clock',        'Confirmed'],
              'pending'   => ['bg-amber-100 text-amber-700', 'hourglass',    'Pending'],
              'completed' => ['bg-emerald-100 text-emerald-700','check-circle','Completed'],
              'cancelled' => ['bg-red-100 text-red-700',     'x-circle',     'Cancelled'],
            ];
            [$sbg,$sicon,$slabel] = $statusMap[$booking->status] ?? ['bg-slate-100 text-slate-600','circle','Unknown'];
          @endphp
          <div class="booking-card bg-white border border-slate-200 rounded-2xl overflow-hidden
                      hover:shadow-md transition-shadow"
               data-ref="{{ strtolower($booking->booking_reference) }}"
               data-route="{{ strtolower($origin.' '.$dest) }}">

            {{-- Card header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono font-bold text-slate-600">{{ $booking->booking_reference }}</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $sbg }}">
                  <i data-lucide="{{ $sicon }}" style="width:10px;height:10px"></i>
                  {{ $slabel }}
                </span>
                @if($isRT)
                  <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full bg-primary-100 text-primary-700">
                    <i data-lucide="arrow-left-right" style="width:10px;height:10px"></i>
                    Round-trip
                  </span>
                @endif
              </div>
              <span class="text-xs text-slate-400">{{ $booking->created_at->format('M j, Y') }}</span>
            </div>

            {{-- Card body --}}
            <div class="p-5 flex flex-wrap gap-5 items-start">

              {{-- Route(s) --}}
              <div class="flex-1 min-w-[200px] space-y-3">
                {{-- Outbound --}}
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-primary-50 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="bus" style="width:16px;height:16px;color:#ea580c"></i>
                  </div>
                  <div class="min-w-0">
                    @if($isRT)
                      <div class="text-[10px] font-bold uppercase tracking-wider text-primary-600 mb-0.5">Outbound</div>
                    @endif
                    <div class="text-sm font-bold text-slate-900 truncate">{{ $origin }} → {{ $dest }}</div>
                    <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                      <i data-lucide="calendar" style="width:10px;height:10px"></i>
                      {{ $dep?->format('D, M j Y · g:i A') ?? '—' }}
                    </div>
                    <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                      <i data-lucide="armchair" style="width:10px;height:10px"></i>
                      Seat {{ $booking->seat_list }}
                      @if($booking->seat_count > 1)
                        <span class="text-primary-600 font-semibold">({{ $booking->seat_count }} seats)</span>
                      @endif
                    </div>
                  </div>
                </div>
                {{-- Return (if round-trip) --}}
                @if($isRT)
                  @php
                    $rOrigin = $rb->trip?->route?->originCity?->name ?? '—';
                    $rDest   = $rb->trip?->route?->destinationCity?->name ?? '—';
                    $rDep    = $rb->trip?->departure_time;
                  @endphp
                  <div class="flex items-center gap-3 pt-3 border-t border-dashed border-slate-200">
                    <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                      <i data-lucide="corner-down-left" style="width:16px;height:16px;color:#059669"></i>
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-0.5">Return</div>
                      <div class="text-sm font-bold text-slate-900 truncate">{{ $rOrigin }} → {{ $rDest }}</div>
                      <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                        <i data-lucide="calendar" style="width:10px;height:10px"></i>
                        {{ $rDep?->format('D, M j Y · g:i A') ?? '—' }}
                      </div>
                      <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                        <i data-lucide="armchair" style="width:10px;height:10px"></i>
                        Seat {{ $rb->seat_list }}
                        @if($rb->seat_count > 1)
                          <span class="text-emerald-600 font-semibold">({{ $rb->seat_count }} seats)</span>
                        @endif
                      </div>
                    </div>
                  </div>
                @endif
              </div>

              {{-- Payment --}}
              <div class="text-right shrink-0">
                <div class="text-xs text-slate-400">{{ $isRT ? 'Total Paid (both legs)' : 'Amount Paid' }}</div>
                <div class="text-xl font-extrabold text-slate-900">₱{{ number_format($combinedAmount, 2) }}</div>
                @if($combinedDiscount > 0)
                  <div class="text-xs text-emerald-600 font-semibold flex items-center gap-1 justify-end">
                    <i data-lucide="badge-percent" style="width:10px;height:10px"></i>
                    Saved ₱{{ number_format($combinedDiscount, 2) }}
                  </div>
                @endif
                <span class="text-xs font-semibold {{ $booking->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                  {{ ucfirst($booking->payment_status) }}
                </span>
              </div>

            </div>

            {{-- Card footer --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 border-t border-slate-100 bg-slate-50">
              <div class="flex flex-wrap gap-2">
                {{-- Download ticket --}}
                @if(in_array($booking->status, ['confirmed','completed']))
                  <button onclick="openTicketModal({{ $booking->id }})"
                     class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700">
                    <i data-lucide="ticket" style="width:12px;height:12px"></i> E-Ticket
                  </button>
                @endif
                {{-- View details --}}
                <button onclick="openBookingDetail({{ $booking->id }})"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">
                  <i data-lucide="eye" style="width:12px;height:12px"></i> Details
                </button>
              </div>

              {{-- Cancel --}}
              @if(in_array($booking->status, ['pending','confirmed']) && !$isPast)
                <button onclick="confirmCancel({{ $booking->id }}, '{{ $booking->booking_reference }}')"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-600">
                  <i data-lucide="x-circle" style="width:12px;height:12px"></i> Cancel Booking
                </button>
              @elseif(in_array($booking->status, ['cancelled', 'completed']) || $isPast)
                <form method="POST" action="{{ route('manage.bookings.destroy', $booking->id) }}" onsubmit="return confirm('Are you sure you want to delete this booking record?');" class="m-0 flex">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition-colors">
                    <i data-lucide="trash-2" style="width:12px;height:12px"></i> Delete
                  </button>
                </form>
              @endif
            </div>

          </div>
        @empty
          <div class="text-center py-20">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i data-lucide="ticket" style="width:26px;height:26px;color:#94a3b8"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-2">No bookings yet</h3>
            <p class="text-sm text-slate-500 mb-5">You haven't booked any trips yet. Let's change that!</p>
            <a href="{{ route('landing.ticket_booking') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-colors">
              <i data-lucide="search" style="width:14px;height:14px"></i> Find a Trip
            </a>
          </div>
        @endforelse
      </div>

      {{-- Pagination --}}
      @if($bookings->hasPages())
        <div class="mt-8">{{ $bookings->withQueryString()->links() }}</div>
      @endif

    </div>
  </div>
</div>

{{-- Cancel confirmation modal --}}
<div id="cancel-modal"
     class="hidden fixed inset-0 z-[9999] items-center justify-center modal-bg"
     onclick="if(event.target===this)closeModal('cancel-modal')">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 animate-slide-up">
    <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
      <i data-lucide="alert-triangle" style="width:22px;height:22px;color:#ef4444"></i>
    </div>
    <h3 class="text-base font-extrabold text-slate-900 text-center mb-1">Cancel Booking?</h3>
    <p id="cancel-ref" class="text-sm text-slate-500 text-center mb-5"></p>
    <div class="mb-4">
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Reason (optional)</label>
      <select id="cancel-reason" class="w-full py-2.5 px-3 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
        <option value="Customer request">Customer request</option>
        <option value="Change of plans">Change of plans</option>
        <option value="Duplicate booking">Duplicate booking</option>
        <option value="Other">Other</option>
      </select>
    </div>
    <div class="flex gap-3">
      <button onclick="closeModal('cancel-modal')"
              class="flex-1 py-2.5 border border-slate-200 text-sm font-semibold rounded-xl text-slate-700 hover:bg-slate-50 transition-colors">
        Keep Booking
      </button>
      <form id="cancel-form" method="POST" class="flex-1">
        @csrf
        <input type="hidden" name="reason" id="cancel-reason-input">
        <button type="submit"
                class="w-full py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-colors">
          Yes, Cancel
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Detail Modal --}}
<div id="detail-modal"
     class="hidden fixed inset-0 z-[9999] items-center justify-center modal-bg"
     onclick="if(event.target===this)closeModal('detail-modal')">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden animate-slide-up flex flex-col max-h-[90vh]">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
      <h3 class="font-bold text-slate-800 flex items-center gap-2">
        <i data-lucide="ticket" style="width:18px;height:18px;color:#ea580c"></i>
        Booking Details
      </h3>
      <button onclick="closeModal('detail-modal')" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
        <i data-lucide="x" style="width:20px;height:20px"></i>
      </button>
    </div>
    
    <div class="p-6 overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-1">Reference No.</p>
          <p id="dt-ref" class="text-lg font-mono font-extrabold text-slate-900"></p>
        </div>
        <div id="dt-status-badge" class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
          <!-- populated via js -->
        </div>
      </div>

      <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-slate-500 mb-1">Route</p>
            <p id="dt-route" class="text-sm font-bold text-slate-800"></p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1">Departure</p>
            <p id="dt-dep" class="text-sm font-bold text-slate-800"></p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1">Seat(s)</p>
            <p id="dt-seats" class="text-sm font-bold text-slate-800"></p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1">Bus Type</p>
            <p id="dt-bus" class="text-sm font-bold text-slate-800"></p>
          </div>
          <div>
            <p class="text-xs text-slate-500 mb-1">Driver</p>
            <p id="dt-driver" class="text-sm font-bold text-slate-800"></p>
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Payment Summary</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-500">Base Fare</span>
            <span id="dt-base" class="font-medium text-slate-800"></span>
          </div>
          <div id="dt-discount-row" class="flex justify-between text-emerald-600 hidden">
            <span>Discount</span>
            <span id="dt-discount" class="font-medium"></span>
          </div>
          <div class="flex justify-between border-t border-slate-100 pt-2 mt-2">
            <span class="font-bold text-slate-800">Total Paid</span>
            <span id="dt-total" class="font-extrabold text-slate-900"></span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-slate-100 bg-slate-50 shrink-0">
      <button onclick="closeModal('detail-modal')" class="w-full py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-bold rounded-xl transition-colors">
        Close
      </button>
    </div>
  </div>
</div>

{{-- Ticket Modal --}}
<div id="ticket-modal"
     class="hidden fixed inset-0 z-[9999] items-center justify-center modal-bg"
     onclick="if(event.target===this)closeModal('ticket-modal')">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden animate-slide-up flex flex-col md:flex-row relative max-h-[90vh]">
    <button onclick="closeModal('ticket-modal')" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 transition-colors z-10 md:hidden">
      <i data-lucide="x" style="width:20px;height:20px"></i>
    </button>
    
    {{-- Left Side: Main Info --}}
    <div class="p-6 md:p-8 flex-1 border-b md:border-b-0 md:border-r border-dashed border-slate-300 relative overflow-y-auto">
      <div class="flex justify-between items-start mb-8">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center shrink-0">
              <i data-lucide="bus" style="width:16px;height:16px;color:#fff"></i>
            </div>
            <span class="text-lg font-extrabold tracking-tight text-slate-900 hidden sm:inline">Mindanao<span class="text-primary-600">Express</span></span>
          </div>
          <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">Official E-Ticket</p>
        </div>
        <div class="text-right">
          <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Booking Ref</p>
          <p id="tk-ref" class="text-lg sm:text-xl font-mono font-extrabold text-slate-900"></p>
        </div>
      </div>

      <div class="flex items-center justify-between mb-8">
        <div class="flex-1">
          <p id="tk-origin" class="text-xl sm:text-3xl font-extrabold text-slate-900"></p>
        </div>
        <div class="px-2 sm:px-4 text-center shrink-0">
          <i data-lucide="arrow-right" style="width:24px;height:24px;color:#cbd5e1" class="mx-auto"></i>
        </div>
        <div class="flex-1 text-right">
          <p id="tk-dest" class="text-xl sm:text-3xl font-extrabold text-slate-900"></p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-2xl p-4 sm:p-6 border border-slate-100">
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Passenger</p>
          <p class="text-xs sm:text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
        </div>
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Date & Time</p>
          <p id="tk-date" class="text-xs sm:text-sm font-bold text-slate-900"></p>
          <p id="tk-time" class="text-[10px] sm:text-xs font-semibold text-slate-600"></p>
        </div>
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Seat(s)</p>
          <p id="tk-seats" class="text-base sm:text-lg font-extrabold text-primary-600"></p>
        </div>
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Bus Type</p>
          <p id="tk-bus" class="text-xs sm:text-sm font-bold text-slate-900"></p>
        </div>
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Driver</p>
          <p id="tk-driver" class="text-xs sm:text-sm font-bold text-slate-900"></p>
        </div>
      </div>
    </div>

    {{-- Right Side: Stub --}}
    <div class="p-6 md:p-8 w-full md:w-64 bg-slate-50 flex flex-col justify-between items-center text-center relative shrink-0">
      <div class="absolute -left-3 top-[-12px] w-6 h-6 bg-slate-900/40 rounded-full hidden md:block mix-blend-multiply"></div>
      <div class="absolute -left-3 bottom-[-12px] w-6 h-6 bg-slate-900/40 rounded-full hidden md:block mix-blend-multiply"></div>
      <button onclick="closeModal('ticket-modal')" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 transition-colors z-10 hidden md:block">
        <i data-lucide="x" style="width:20px;height:20px"></i>
      </button>
      
      <div class="w-full mt-4 md:mt-8">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Boarding Pass</p>
        <p id="tk-ref-small" class="text-base font-mono font-extrabold text-slate-900 mb-4 sm:mb-6"></p>
        
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border border-slate-100 inline-block mb-4 sm:mb-6">
          <i data-lucide="qr-code" style="width:64px;height:64px;color:#0f172a"></i>
        </div>
        
        <div>
          <p class="text-[10px] sm:text-xs text-slate-500 uppercase tracking-wider mb-1">Total Paid</p>
          <p id="tk-total" class="text-xl sm:text-2xl font-extrabold text-slate-900"></p>
          <span class="inline-block mt-2 px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wider">Paid</span>
        </div>
      </div>
      
      <a id="tk-print-btn" href="#" target="_blank" class="mt-6 w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition-colors inline-flex items-center justify-center gap-2">
        <i data-lucide="printer" style="width:16px;height:16px"></i> Print PDF
      </a>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  const bookingsData = @json($bookings->items());

  function filterBookings(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.booking-card').forEach(card => {
      const match = card.dataset.ref.includes(q) || card.dataset.route.includes(q);
      card.style.display = match ? '' : 'none';
    });
  }

  function confirmCancel(id, ref) {
    document.getElementById('cancel-ref').textContent = 'Booking ' + ref + ' will be cancelled. This cannot be undone.';
    document.getElementById('cancel-form').action = '/my-bookings/' + id + '/cancel';
    openModal('cancel-modal');
  }

  document.getElementById('cancel-form')?.addEventListener('submit', function() {
    document.getElementById('cancel-reason-input').value = document.getElementById('cancel-reason').value;
  });

  function openBookingDetail(id) {
    const booking = bookingsData.find(b => b.id === id);
    if(!booking) return;

    document.getElementById('dt-ref').textContent = booking.booking_reference;
    
    // Status
    const sMap = {
      'confirmed': { bg: 'bg-blue-100', text: 'text-blue-700', icon: 'clock', label: 'Confirmed' },
      'pending':   { bg: 'bg-amber-100', text: 'text-amber-700', icon: 'hourglass', label: 'Pending' },
      'completed': { bg: 'bg-emerald-100', text: 'text-emerald-700', icon: 'check-circle', label: 'Completed' },
      'cancelled': { bg: 'bg-red-100', text: 'text-red-700', icon: 'x-circle', label: 'Cancelled' }
    };
    const s = sMap[booking.status] || { bg: 'bg-slate-100', text: 'text-slate-600', icon: 'circle', label: booking.status };
    
    document.getElementById('dt-status-badge').className = `px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5 ${s.bg} ${s.text}`;
    document.getElementById('dt-status-badge').innerHTML = `<i data-lucide="${s.icon}" style="width:12px;height:12px"></i> ${s.label}`;
    
    // Route & Bus
    const origin = booking.trip?.route?.origin_city?.name || '—';
    const dest = booking.trip?.route?.destination_city?.name || '—';
    document.getElementById('dt-route').textContent = `${origin} → ${dest}`;
    
    document.getElementById('dt-bus').textContent = booking.trip?.bus?.type?.name || 'Standard Bus';
    document.getElementById('dt-driver').textContent = booking.trip?.driver?.user?.name || 'Assigned before departure';
    
    // Dates
    if(booking.trip?.departure_time) {
      const d = new Date(booking.trip.departure_time);
      document.getElementById('dt-dep').textContent = d.toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    } else {
      document.getElementById('dt-dep').textContent = '—';
    }

    // Seats
    let seats = '—';
    if(booking.booking_seats && booking.booking_seats.length > 0) {
      seats = booking.booking_seats.map(s => s.seat_number).join(', ');
    } else if (booking.seat_number) {
      seats = booking.seat_number;
    }
    document.getElementById('dt-seats').textContent = seats;

    // Payment
    document.getElementById('dt-base').textContent = '₱' + parseFloat(booking.base_fare || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('dt-total').textContent = '₱' + parseFloat(booking.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    const disc = parseFloat(booking.discount_amount || 0);
    const discRow = document.getElementById('dt-discount-row');
    if(disc > 0) {
      discRow.classList.remove('hidden');
      document.getElementById('dt-discount').textContent = '-₱' + disc.toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
      discRow.classList.add('hidden');
    }

    lucide.createIcons();
    openModal('detail-modal');
  }

  function openTicketModal(id) {
    const booking = bookingsData.find(b => b.id === id);
    if(!booking) return;

    document.getElementById('tk-ref').textContent = booking.booking_reference;
    document.getElementById('tk-ref-small').textContent = booking.booking_reference;
    
    const origin = booking.trip?.route?.origin_city?.name || 'Origin';
    const dest = booking.trip?.route?.destination_city?.name || 'Destination';
    document.getElementById('tk-origin').textContent = origin;
    document.getElementById('tk-dest').textContent = dest;
    
    document.getElementById('tk-bus').textContent = booking.trip?.bus?.type?.name || 'Standard Bus';
    document.getElementById('tk-driver').textContent = booking.trip?.driver?.user?.name || 'Assigned before departure';
    
    if(booking.trip?.departure_time) {
      const d = new Date(booking.trip.departure_time);
      document.getElementById('tk-date').textContent = d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      document.getElementById('tk-time').textContent = d.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    } else {
      document.getElementById('tk-date').textContent = '—';
      document.getElementById('tk-time').textContent = '—';
    }

    let seats = '—';
    if(booking.booking_seats && booking.booking_seats.length > 0) {
      seats = booking.booking_seats.map(s => s.seat_number).join(', ');
    } else if (booking.seat_number) {
      seats = booking.seat_number;
    }
    document.getElementById('tk-seats').textContent = seats;

    document.getElementById('tk-total').textContent = '₱' + parseFloat(booking.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    document.getElementById('tk-print-btn').href = '/my-bookings/' + id + '/ticket';

    lucide.createIcons();
    openModal('ticket-modal');
  }
</script>
@endpush