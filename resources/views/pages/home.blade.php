@extends('layouts.app')
@section('title', 'Mindanao Express — Book Your Bus Trip')

@section('content')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
  .hero-swiper { width: 100%; height: calc(100vh - calc(64px * 2)); min-height: 500px; position: relative; }
  .swiper-slide { position: relative; background-color: #0f172a; overflow: hidden; }
  .swiper-slide img { width: 100%; height: 100%; object-fit: cover; object-position: center; opacity: 0.65; image-rendering: auto; }
  .hero-bg-img { min-width: 100%; min-height: 100%; }
  .hero-overlay { position: absolute; inset: 0; background: linear-gradient(to right, rgba(15,23,42,0.9) 0%, rgba(15,23,42,0.4) 100%); }
  
  .search-widget-container {
      margin-top: -64px; /* Symmetrical overlap matching the 64px navbar height */
      position: relative;
      z-index: 20;
  }
</style>
@endpush

{{-- ══════════════════════════════════ HERO SLIDER ══════════════════════════════════ --}}
<section class="relative bg-slate-900">
  <div class="swiper hero-swiper">
    <div class="swiper-wrapper">
      @php
          $heroSlides = $cmsSettings['hero_slides'] ?? [];
          $heroFromCms = !empty($heroSlides);
          if (empty($heroSlides)) {
              $heroSlides = [
                  [
                      'image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000',
                      'badge' => 'Instant e-ticket confirmation',
                      'title' => 'Travel Mindanao<br><span class="text-primary-400">Your Way</span>',
                      'subtitle' => 'Book intercity bus trips in seconds. Pick your seat, pay securely, and receive your QR e-ticket instantly.',
                  ],
                  [
                      'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=2000',
                      'badge' => 'Premium Comfort',
                      'title' => 'First Class<br><span class="text-primary-400">Experience</span>',
                      'subtitle' => 'Enjoy spacious seating, full air-conditioning, and smooth rides across the beautiful landscapes of Mindanao.',
                  ],
                  [
                      'image' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&q=80&w=2000',
                      'badge' => 'Discover More',
                      'title' => 'Your Journey<br><span class="text-primary-400">Starts Here</span>',
                      'subtitle' => 'Join thousands of daily passengers who trust Mindanao Express for their daily commute and long-distance travel.',
                  ]
              ];
          }
      @endphp
      @foreach($heroSlides as $index => $slide)
      @php
          $rawImage = $slide['image'] ?? '';
          $fallback = 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=85&w=2400';
          $heroImg = \App\Services\Cloudinary::heroImageSrcset($rawImage ?: null);
          $heroSrc = $heroImg['src'] ?? \App\Services\Cloudinary::heroImageUrl($fallback);
          $heroSrcset = $heroImg['srcset'] ?? null;
      @endphp
      <div class="swiper-slide">
        <img src="{{ $heroSrc }}"
             @if($heroSrcset) srcset="{{ $heroSrcset }}" @endif
             sizes="100vw"
             class="hero-bg-img"
             alt="{{ strip_tags($slide['title'] ?? 'Hero') }}"
             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
             fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
             decoding="async">
        <div class="hero-overlay"></div>
        <div class="absolute inset-0 flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-10">
                @if(!empty($slide['badge']))
                <span class="inline-flex items-center gap-2 bg-primary-500/20 text-primary-300 text-xs font-bold px-3 py-1.5 rounded-full mb-6 backdrop-blur-md">
                  <i data-lucide="zap" style="width:11px;height:11px"></i> {{ $slide['badge'] }}
                </span>
                @endif
                <h1 class="text-5xl sm:text-7xl font-extrabold text-white leading-[1.1] mb-5 tracking-tight">
                  {!! $slide['title'] !!}
                </h1>
                <p class="text-slate-300 text-lg leading-relaxed mb-8 max-w-lg">
                  {{ $slide['subtitle'] }}
                </p>
                @if(!empty($slide['show_trust_badges']) || (!$heroFromCms && $index === 0))
                <div class="flex flex-wrap gap-5">
                  @foreach([
                    ['shield-check', 'LTO Accredited'],
                    ['clock',        'On-Time Guarantee'],
                    ['credit-card',  'Secure Payment'],
                  ] as [$icon,$label])
                    <div class="flex items-center gap-1.5 text-white/80 text-sm font-semibold backdrop-blur-sm bg-white/10 px-3 py-1.5 rounded-lg border border-white/10">
                      <i data-lucide="{{ $icon }}" style="width:14px;height:14px;color:#f97316"></i> {{ $label }}
                    </div>
                  @endforeach
                </div>
                @endif
                @if(!empty($slide['cta_text']))
                <div class="flex flex-wrap gap-3">
                  <a href="{{ $slide['cta_url'] ?: route('landing.booking_routes') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 font-bold text-sm rounded-xl hover:bg-slate-100 transition-colors">
                    {{ $slide['cta_text'] }}
                  </a>
                </div>
                @elseif(!$heroFromCms && $index === 1)
                <div class="flex flex-wrap gap-3">
                  <a href="{{ route('landing.booking_routes') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 font-bold text-sm rounded-xl hover:bg-slate-100 transition-colors">
                    Browse Routes
                  </a>
                </div>
                @endif
                @if(!empty($slide['show_live_stats']) || (!$heroFromCms && $index === 2))
                <div class="inline-flex items-center gap-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-white font-semibold">
                      <span class="text-2xl font-bold text-emerald-300">{{ $liveStats['trips_today'] ?? 0 }}</span> <span class="text-sm text-slate-300"> trips today</span>
                    </span>
                  </div>
                </div>
                @endif
            </div>
        </div>
      </div>
      @endforeach
    </div>
    
    <!-- Navigation -->
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev !text-white/50 hover:!text-white after:!text-2xl transition-colors"></div>
    <div class="swiper-button-next !text-white/50 hover:!text-white after:!text-2xl transition-colors"></div>
  </div>
</section>

{{-- ══════════════════════════════════ SEARCH WIDGET (OVERLAPPING) ══════════════════════════════════ --}}
<div class="search-widget-container max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
  
  {{-- Next Departure Alert (Moved from right column) --}}
  @if(isset($heroTrip) && $heroTrip)
    <div class="bg-slate-900 border border-slate-700 rounded-t-2xl p-3 flex flex-wrap items-center justify-between gap-4 text-white shadow-2xl relative z-10 w-11/12 mx-auto -mb-2">
      <div class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
        <span class="text-xs font-bold tracking-widest text-emerald-400 uppercase hidden sm:inline">Next Departure:</span>
        <span class="text-sm font-semibold">{{ $heroTrip->departure_time->format('h:i A') }} • {{ $heroTrip->route?->originCity?->name }} <i data-lucide="arrow-right" class="inline w-3 h-3 text-slate-400 mx-1"></i> {{ $heroTrip->route?->destinationCity?->name }}</span>
      </div>
      <div class="flex items-center gap-3 text-sm">
        <span class="text-slate-400 hidden sm:inline"><i data-lucide="users" class="inline w-3 h-3 mr-1"></i>{{ $heroTrip->available_seats }} seats</span>
        <span class="font-bold text-primary-400">₱{{ number_format($heroTrip->fare, 0) }}</span>
        <a href="{{ route('landing.ticket_booking') }}?from={{ urlencode($heroTrip->route?->originCity?->name ?? '') }}&to={{ urlencode($heroTrip->route?->destinationCity?->name ?? '') }}&date={{ today()->toDateString() }}" class="px-3 py-1 text-xs font-bold bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">Book Now</a>
      </div>
    </div>
  @endif

  <div class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 p-2 relative z-20">
    
    {{-- Widget Tabs --}}
    <div class="flex gap-2 p-2 border-b border-slate-100 mb-2 bg-slate-50 rounded-t-xl">
      <button onclick="switchHeroTab('search')" id="tab-btn-search" class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold bg-white text-primary-600 rounded-lg shadow-sm border border-slate-200 transition-all flex items-center justify-center gap-2">
        <i data-lucide="search" style="width:16px;height:16px"></i> Quick Search
      </button>
      <button onclick="switchHeroTab('track')" id="tab-btn-track" class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-200/50 rounded-lg transition-all flex items-center justify-center gap-2 border border-transparent">
        <i data-lucide="crosshair" style="width:16px;height:16px"></i> Track Trip
      </button>
    </div>

    {{-- Horizontal Search Form --}}
    <form action="{{ route('landing.ticket_booking.search') }}" method="POST" id="hero-search-form" class="p-4">
      @csrf
      <div class="flex flex-col md:flex-row items-start gap-3">
        <div class="flex-1 w-full">
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Origin</label>
          <div class="relative" data-hero-select="from">
            <input type="hidden" name="from" id="hero-from" value="">
            <button type="button" class="w-full pl-10 pr-10 py-3.5 text-sm font-semibold border-2 border-slate-200 rounded-xl bg-white text-slate-800 outline-none focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 hover:border-slate-300 transition-colors shadow-sm text-left flex items-center justify-between gap-3"
                    onclick="toggleHeroSelect('from')">
              <span id="hero-from-text" class="truncate text-slate-500">Leaving from...</span>
              <i data-lucide="chevron-down" style="width:16px;height:16px;color:#94a3b8;flex-shrink:0"></i>
            </button>
            <i data-lucide="map-pin" style="width:16px;height:16px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none"></i>

            <div id="hero-from-menu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden max-h-60 overflow-y-auto">
              @foreach($originCities ?? [] as $city)
                <div class="px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors"
                     data-value="{{ $city->name }}"
                     onclick="selectHeroOption('from', this.dataset.value, this.textContent)">
                  {{ $city->name }}
                </div>
              @endforeach
            </div>
          </div>
          <p id="hero-from-error" class="hidden mt-1.5 ml-1 text-xs font-semibold text-red-600 min-h-[16px] leading-tight"></p>
        </div>
        
        <!-- Swap Button -->
        <div class="hidden md:flex shrink-0 pt-[26px]">
          <button type="button" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-primary-50 hover:text-primary-600 transition-colors border border-slate-200" onclick="swapHeroCities()" title="Swap Cities">
              <i data-lucide="arrow-left-right" style="width:16px;height:16px"></i>
          </button>
        </div>

        <div class="flex-1 w-full">
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Destination</label>
          <div class="relative" data-hero-select="to">
            <input type="hidden" name="to" id="hero-to" value="">
            <button type="button" class="w-full pl-10 pr-10 py-3.5 text-sm font-semibold border-2 border-slate-200 rounded-xl bg-white text-slate-800 outline-none focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 hover:border-slate-300 transition-colors shadow-sm text-left flex items-center justify-between gap-3"
                    onclick="toggleHeroSelect('to')">
              <span id="hero-to-text" class="truncate text-slate-500">Going to...</span>
              <i data-lucide="chevron-down" style="width:16px;height:16px;color:#94a3b8;flex-shrink:0"></i>
            </button>
            <i data-lucide="map-pin" style="width:16px;height:16px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none"></i>

            <div id="hero-to-menu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden max-h-60 overflow-y-auto">
              {{-- Filled by JS based on origin --}}
              <div class="px-4 py-2.5 text-sm text-slate-500">Select an origin first.</div>
            </div>
          </div>
          <p id="hero-to-error" class="hidden mt-1.5 ml-1 text-xs font-semibold text-red-600 min-h-[16px] leading-tight"></p>
        </div>
        
        <div class="flex-1 w-full">
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Travel Date</label>
          <div class="relative">
            <i data-lucide="calendar" style="width:16px;height:16px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
            <input type="date" name="date" required min="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}"
                   id="hero-date"
                   class="w-full pl-10 pr-4 py-3.5 text-sm font-semibold border-2 border-slate-200 rounded-xl bg-white text-slate-800 outline-none focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 hover:border-slate-300 transition-colors shadow-sm">
          </div>
          <p id="hero-date-error" class="hidden mt-1.5 ml-1 text-xs font-semibold text-red-600 min-h-[16px] leading-tight"></p>
        </div>
        
        <div class="w-full md:w-auto md:min-w-[140px] pt-[26px] md:pt-[26px]">
          <button type="submit" class="w-full py-3.5 px-6 bg-primary-600 hover:bg-primary-700 text-white text-base font-bold rounded-xl transition-all shadow-[0_4px_14px_0_rgba(234,88,12,0.39)] hover:shadow-[0_6px_20px_rgba(234,88,12,0.23)] flex items-center justify-center gap-2">
          Search
          </button>
        </div>
      </div>
    </form>

    {{-- Horizontal Track Form --}}
    <div id="hero-track-form" class="hidden p-4">
      <div class="flex flex-col md:flex-row items-end gap-3 max-w-2xl mx-auto">
        <div class="flex-1 w-full relative">
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Trip Code</label>
          <div class="relative">
            <i data-lucide="hash" style="width:16px;height:16px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
            <input type="text" id="track-trip-code" placeholder="Enter code (e.g. TR-A1B2C3)"
                   class="w-full pl-10 pr-4 py-3.5 text-sm font-semibold border-2 border-slate-200 rounded-xl focus:border-primary-500 focus:ring-0 uppercase hover:border-slate-300 transition-colors">
          </div>
        </div>
        <button onclick="trackTripCode()" id="btn-track-trip" class="w-full md:w-auto px-8 py-3.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition-all shadow-lg flex items-center justify-center gap-2 mt-2 md:mt-0 disabled:opacity-60 disabled:cursor-not-allowed">
          Track Status
        </button>
      </div>
      <div id="track-result" class="hidden max-w-2xl mx-auto mt-4 p-4 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm">
        <!-- Result goes here -->
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════ STATS ══════════════════════════════════ --}}
<section class="bg-white border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
      @foreach([
        ['map-pin',   $stats['totalCities']  ?? 0, 'Cities Served'],
        ['route',     $stats['totalRoutes']  ?? 0, 'Active Routes'],
        ['bus',       $stats['activeBuses']  ?? 0, 'Active Buses'],
        ['calendar',  $stats['todayTrips']   ?? 0, "Today's Trips"],
      ] as [$icon,$val,$label])
        <div class="flex flex-col items-center py-8 px-4 text-center hover:-translate-y-0.5 transition-transform">
          <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center mb-3">
            <i data-lucide="{{ $icon }}" style="width:18px;height:18px;color:#ea580c"></i>
          </div>
          <div class="text-2xl font-extrabold text-slate-900">{{ number_format($val) }}</div>
          <div class="text-xs text-slate-500 mt-0.5">{{ $label }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ══════════════════════════════ POPULAR ROUTES ══════════════════════════════ --}}
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-10">
      <div>
        <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">Popular Destinations</p>
        <h2 class="text-3xl font-extrabold text-slate-900">{{ $cmsSettings['popular_routes_title'] ?? 'Popular Routes' }}</h2>
        <p class="text-slate-500 text-sm mt-1.5">{{ $cmsSettings['popular_routes_subtitle'] ?? 'Our most-booked intercity routes — reserve early for the best fares.' }}</p>
      </div>
      <a href="{{ route('landing.booking_routes') }}"
         class="hidden sm:flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
        View all <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
      </a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @forelse($featuredRoutes ?? [] as $i => $route)
        @php
          $dur   = $route->estimated_duration_minutes
            ? floor($route->estimated_duration_minutes/60).'h '.str_pad($route->estimated_duration_minutes%60,2,'0',STR_PAD_LEFT).'m'
            : '—';
          $badges = [
            ['bg-red-100 text-red-700',          'flame',       'Hot'],
            ['bg-primary-100 text-primary-700',   'star',        'Popular'],
            ['bg-emerald-100 text-emerald-700',   'trending-up', 'Trending'],
          ];
          [$bcls,$bicon,$blabel] = $badges[$i % 3];

          $live = $featuredRouteLive[$route->id] ?? ['tripsToday' => 0, 'seatsToday' => 0];
          $tripsToday = $live['tripsToday'] ?? 0;
          $seatsToday = $live['seatsToday'] ?? 0;
        @endphp
        <div class="group bg-white rounded-2xl p-5 border border-slate-200 cursor-pointer
                    hover:shadow-lg hover:-translate-y-1 transition-all duration-200"
             onclick="location.href='{{ route('landing.ticket_booking') }}?from={{ urlencode($route->originCity?->name ?? '') }}&to={{ urlencode($route->destinationCity?->name ?? '') }}&date={{ today()->toDateString() }}'">

          <div class="flex items-start justify-between mb-4">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full {{ $bcls }}">
              <i data-lucide="{{ $bicon }}" style="width:10px;height:10px"></i>{{ $blabel }}
            </span>
            <span class="text-xs text-slate-400">{{ $route->distance_km ? $route->distance_km.' km' : '' }}</span>
          </div>

          <div class="flex items-center gap-3 mb-4">
            <div class="text-center shrink-0">
              <div class="text-sm font-bold text-slate-900 leading-tight">{{ $route->originCity?->name }}</div>
              <div class="text-[10px] text-slate-400">{{ $route->originCity?->province }}</div>
            </div>
            <div class="flex-1 flex flex-col items-center gap-0.5">
              <span class="text-[10px] text-slate-400">{{ $dur }}</span>
              <div class="w-full flex items-center">
                <div class="w-1.5 h-1.5 rounded-full bg-primary-500"></div>
                <div class="flex-1 h-px bg-slate-200"></div>
                <i data-lucide="bus" style="width:13px;height:13px;color:#ea580c"></i>
                <div class="flex-1 h-px bg-slate-200"></div>
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
              </div>
            </div>
            <div class="text-center shrink-0">
              <div class="text-sm font-bold text-slate-900 leading-tight">{{ $route->destinationCity?->name }}</div>
              <div class="text-[10px] text-slate-400">{{ $route->destinationCity?->province }}</div>
            </div>
          </div>

          <div class="flex items-center justify-between pt-3.5 border-t border-slate-100">
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1 text-xs text-slate-500">
                <i data-lucide="calendar" style="width:11px;height:11px"></i>
                {{ $route->upcoming_trips_count ?? 0 }} upcoming
              </div>
              @if($tripsToday > 0)
                <div class="flex items-center gap-1 text-xs text-emerald-600 font-semibold">
                  <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                  {{ $tripsToday }} today
                </div>
              @endif
            </div>
            <div class="text-right">
              <div class="text-[10px] text-slate-400">from</div>
              <div class="text-base font-extrabold text-primary-600">
                {{ $route->min_fare ? '₱'.number_format($route->min_fare, 0) : '—' }}
              </div>
              @if($seatsToday > 0)
                <div class="text-[10px] text-emerald-600 font-semibold">{{ $seatsToday }} seats</div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-3 py-16 text-center text-slate-400">
          <i data-lucide="route" style="width:36px;height:36px;margin:0 auto 10px;opacity:.3"></i>
          <p class="text-sm">No active routes yet. Check back soon!</p>
        </div>
      @endforelse
    </div>

    <div class="sm:hidden text-center mt-6">
      <a href="{{ route('landing.booking_routes') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
        View all routes <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
      </a>
    </div>
  </div>
</section>

{{-- ══════════════════════════════ HOW IT WORKS ══════════════════════════════ --}}
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-lg mx-auto mb-12">
      <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">Simple Process</p>
      <h2 class="text-3xl font-extrabold text-slate-900">Booked in <span class="text-primary-600">4 Steps</span></h2>
      <p class="text-slate-500 text-sm mt-1.5">From search to seat confirmation in under 2 minutes.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @php
        $steps = $cmsSettings['how_it_works'] ?? [
          ['icon' => 'search',      'title' => 'Search Your Trip',  'desc' => 'Enter your origin, destination, and travel date. See all available buses instantly.'],
          ['icon' => 'armchair',    'title' => 'Pick Your Seat',     'desc' => 'View the live seat map. Choose economy, business, or sleeper — window or aisle.'],
          ['icon' => 'credit-card', 'title' => 'Pay Securely',       'desc' => 'GCash, Maya, card, or OTC. 256-bit SSL encrypted. Zero hidden fees.'],
          ['icon' => 'ticket',      'title' => 'Board & Ride',       'desc' => 'Show your QR e-ticket at the terminal gate. Sit back and enjoy the journey.'],
        ];
      @endphp
      @foreach($steps as $index => $step)
        <div class="relative bg-slate-50 border border-slate-200 rounded-2xl p-5">
          <div class="absolute -top-3 -left-3 w-7 h-7 bg-primary-600 text-white text-xs font-extrabold rounded-full flex items-center justify-center shadow-md shadow-primary-200">{{ $index + 1 }}</div>
          <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center mb-3.5">
            <i data-lucide="{{ $step['icon'] ?? 'check' }}" style="width:19px;height:19px;color:#ea580c"></i>
          </div>
          <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $step['title'] ?? '' }}</h3>
          <p class="text-xs text-slate-500 leading-relaxed">{{ $step['desc'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ══════════════════════════════ QUICK TIMETABLES ══════════════════════════════ --}}
@if(isset($topSchedules) && count($topSchedules) > 0)
<section class="py-20 bg-slate-900 border-t border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-lg mx-auto mb-12">
      <p class="text-xs font-bold text-primary-400 uppercase tracking-widest mb-1">Daily Departures</p>
      <h2 class="text-3xl font-extrabold text-white">Quick <span class="text-primary-400">Timetables</span></h2>
      <p class="text-slate-400 text-sm mt-1.5">View our daily schedule for top routes across Mindanao.</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      @foreach(array_slice($topSchedules, 0, 4) as $item)
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
          <div class="flex items-center gap-3 mb-4 border-b border-white/10 pb-4">
            <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 shrink-0">
              <i data-lucide="route" style="width:20px;height:20px"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-white">{{ $item['route']->originCity->name }} to {{ $item['route']->destinationCity->name }}</h3>
              <p class="text-xs text-slate-400">{{ $item['route']->distance_km ? $item['route']->distance_km.' km' : 'Direct Route' }}</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            @foreach($item['schedules']->take(8) as $sched)
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 text-slate-300 text-xs font-semibold rounded-lg border border-slate-700">
                <i data-lucide="clock" style="width:12px;height:12px;color:#94a3b8"></i>
                {{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i A') }}
              </span>
            @endforeach
            @if($item['schedules']->count() > 8)
              <span class="inline-flex items-center px-3 py-1.5 text-slate-400 text-xs font-semibold">
                +{{ $item['schedules']->count() - 8 }} more
              </span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ══════════════════════════════ FLEET SHOWCASE ══════════════════════════════ --}}
@if(isset($busTypes) && $busTypes->isNotEmpty())
<section class="py-20 bg-white border-t border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-lg mx-auto mb-12">
      <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">Our Fleet</p>
      <h2 class="text-3xl font-extrabold text-slate-900">Travel in <span class="text-primary-600">Comfort</span></h2>
      <p class="text-slate-500 text-sm mt-1.5">Choose the class that fits your budget and style.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($busTypes as $type)
        @php
          $icon = match(strtolower($type->type_name)) {
            'deluxe' => 'star',
            'express' => 'zap',
            'aircon' => 'wind',
            'mini bus' => 'truck',
            default => 'bus'
          };
          $color = match(strtolower($type->type_name)) {
            'deluxe' => 'text-amber-600 bg-amber-50 border-amber-200',
            'express' => 'text-red-600 bg-red-50 border-red-200',
            'aircon' => 'text-blue-600 bg-blue-50 border-blue-200',
            default => 'text-slate-600 bg-slate-50 border-slate-200'
          };
        @endphp
        <div class="border border-slate-200 rounded-2xl p-6 hover:shadow-xl transition-shadow relative overflow-hidden group">
          <div class="absolute -right-6 -top-6 w-24 h-24 {{ explode(' ', $color)[1] }} rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
          
          <div class="w-12 h-12 {{ $color }} rounded-xl flex items-center justify-center mb-5 relative z-10 border">
            <i data-lucide="{{ $icon }}" style="width:24px;height:24px"></i>
          </div>
          
          <h3 class="text-xl font-bold text-slate-900 mb-2 relative z-10">{{ $type->type_name }}</h3>
          <p class="text-sm text-slate-500 mb-5 relative z-10 line-clamp-2 min-h-[40px]">{{ $type->description }}</p>
          
          <ul class="space-y-2 relative z-10">
            <li class="flex items-center gap-2 text-xs text-slate-600 font-medium">
              <i data-lucide="check" style="width:14px;height:14px;color:#10b981"></i> Spacious Seating
            </li>
            @if(in_array(strtolower($type->type_name), ['aircon', 'express', 'deluxe']))
              <li class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                <i data-lucide="check" style="width:14px;height:14px;color:#10b981"></i> Full Air-Conditioning
              </li>
            @endif
            @if(strtolower($type->type_name) === 'deluxe')
              <li class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                <i data-lucide="check" style="width:14px;height:14px;color:#10b981"></i> Extra Legroom & USB Ports
              </li>
            @endif
          </ul>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ══════════════════════════════ TRAVEL FAQ ══════════════════════════════ --}}
<section class="py-20 bg-slate-50 border-t border-slate-200">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">Got Questions?</p>
      <h2 class="text-3xl font-extrabold text-slate-900">Travel <span class="text-primary-600">FAQ</span></h2>
    </div>

    <div class="space-y-4">
      @php
        $faqs = $cmsSettings['faqs'] ?? [
          ['q' => 'What is your luggage policy?', 'a' => 'Each passenger is allowed 1 hand-carry bag (up to 7kg) and 1 checked bag (up to 15kg). Excess luggage is subject to additional fees at the terminal.'],
          ['q' => 'Are pets allowed on the bus?', 'a' => 'Yes, small pets are allowed but they must be placed in a secure, leak-proof pet carrier. They can be placed under the seat or in the cargo hold depending on the bus type.'],
          ['q' => 'Can I cancel or reschedule my ticket?', 'a' => 'Tickets can be rescheduled at least 24 hours before departure with a minor rebooking fee. Cancellations are non-refundable but can be converted to travel credits.'],
          ['q' => 'Do children or infants travel for free?', 'a' => 'Infants under 2 years old travel for free provided they sit on an adult\'s lap. Children 3 years and above require a regular ticket. Student discounts apply.'],
          ['q' => 'How do I claim my Senior Citizen or PWD discount?', 'a' => 'You can select the discount type during checkout. However, you MUST present your valid Senior Citizen or PWD ID to the bus conductor before boarding. Failure to do so will require you to pay the fare difference.']
        ];
      @endphp
      @foreach($faqs as $i => $faq)
        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
          <button onclick="toggleFaq({{ $i }})" class="w-full flex items-center justify-between p-5 text-left bg-white hover:bg-slate-50 transition-colors focus:outline-none">
            <span class="font-bold text-slate-900 text-sm">{{ $faq['q'] ?? '' }}</span>
            <i data-lucide="chevron-down" id="faq-icon-{{ $i }}" style="width:16px;height:16px;color:#64748b;transition:transform 0.3s"></i>
          </button>
          <div id="faq-content-{{ $i }}" class="hidden px-5 pb-5 pt-2 text-sm text-slate-600 leading-relaxed bg-white">
            {{ $faq['a'] ?? '' }}
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ══════════════════════════════ DISCOUNT TYPES STRIP ══════════════════════════════ --}}
@if(isset($discountTypes) && $discountTypes->isNotEmpty())
<section class="bg-slate-50 border-y border-slate-100 py-6">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center gap-4 justify-center">
      <div class="flex items-center gap-2 text-primary-600 mr-2">
        <i data-lucide="badge-percent" style="width:18px;height:18px"></i>
        <span class="text-sm font-bold">Government Discounts Available:</span>
      </div>
      @foreach($discountTypes as $dt)
        @if($dt->percentage > 0)
          <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
            <span class="text-xs font-bold text-primary-600">{{ number_format($dt->percentage * 100, 0) }}% OFF</span>
            <span class="text-xs text-slate-600">· {{ $dt->display_name }}</span>
          </div>
        @endif
      @endforeach
      <span class="text-xs text-slate-500">Valid ID required at boarding.</span>
    </div>
  </div>
</section>
@endif

{{-- ══════════════════════════════ TRUST BADGES ══════════════════════════════ --}}
<section class="py-16 bg-white border-y border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    {{-- Section Header --}}
    <div class="text-center mb-12">
      <h2 class="text-2xl font-extrabold text-slate-900 mb-4">
        Travel with <span class="text-primary-600">Confidence</span>
      </h2>
      <p class="text-slate-600 max-w-2xl mx-auto">
        We're accredited, insured, and committed to your safety and satisfaction.
      </p>
    </div>

    {{-- Trust Badges Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
      
      {{-- LTO Accredited --}}
      <div class="text-center group">
        <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-200 transition-colors">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#059669">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <path d="M9 12l2 2 4-4"></path>
          </svg>
        </div>
        <h3 class="font-bold text-slate-900 mb-2">LTO Accredited</h3>
        <p class="text-sm text-slate-600">Officially licensed by Land Transportation Office</p>
      </div>

      {{-- LTFRB Certified --}}
      <div class="text-center group">
        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#2563eb">
            <path d="M9 12l2 2 4-4"></path>
            <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1h18z"></path>
            <path d="M3 5v18h18"></path>
          </svg>
        </div>
        <h3 class="font-bold text-slate-900 mb-2">LTFRB Certified</h3>
        <p class="text-sm text-slate-600">Regulated by Land Transportation Franchising Board</p>
      </div>

      {{-- Secure Payments --}}
      <div class="text-center group">
        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-200 transition-colors">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#d97706">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
        </div>
        <h3 class="font-bold text-slate-900 mb-2">Secure Payments</h3>
        <p class="text-sm text-slate-600">Encrypted transactions with multiple payment options</p>
      </div>

      {{-- 24/7 Support --}}
      <div class="text-center group">
        <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-200 transition-colors">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#ea580c">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          </svg>
        </div>
        <h3 class="font-bold text-slate-900 mb-2">24/7 Support</h3>
        <p class="text-sm text-slate-600">Round-the-clock customer assistance</p>
      </div>

    </div>

    {{-- Additional Trust Indicators --}}
    <div class="mt-12 pt-8 border-t border-slate-200">
      <div class="flex flex-wrap items-center justify-center gap-8">
        
        {{-- Insurance Coverage --}}
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <span>Passenger Insurance Coverage</span>
        </div>

        {{-- COVID-19 Safety --}}
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
          </svg>
          <span>Health & Safety Protocols</span>
        </div>

        {{-- On-Time Guarantee --}}
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
          <span>95% On-Time Performance</span>
        </div>

      </div>
    </div>

  </div>
</section>

{{-- ══════════════════════════════ PROMOS ══════════════════════════════ --}}
@if(isset($promotions) && $promotions->isNotEmpty())
<section class="py-20 bg-slate-900">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-10">
      <div>
        <p class="text-xs font-bold text-primary-400 uppercase tracking-widest mb-1">Limited Time</p>
        <h2 class="text-3xl font-extrabold text-white">Promos &amp; <span class="text-primary-400">Deals</span></h2>
        <p class="text-slate-400 text-sm mt-1.5">Book before they run out.</p>
      </div>
      <a href="{{ route('landing.promos') }}"
         class="hidden sm:flex items-center gap-1.5 text-sm font-semibold text-primary-400 hover:text-primary-300 transition-colors">
        All promos <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
      </a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @foreach($promotions as $promo)
        @php
          $disc = $promo->discount_type === 'percent'
            ? number_format($promo->discount_value, 0).'% OFF'
            : '₱'.number_format($promo->discount_value, 0).' OFF';
        @endphp
        <div class="group bg-white/10 border border-white/20 rounded-2xl p-5 cursor-pointer
                    hover:-translate-y-1 hover:bg-white/15 transition-all duration-200"
             onclick="copyCode('{{ $promo->code }}')">
          <div class="flex items-start justify-between mb-3">
            <span class="text-xs font-bold text-primary-400 bg-primary-400/10 px-2.5 py-1 rounded-full">
              {{ $promo->discount_type === 'percent' ? 'Percent Discount' : 'Fixed Discount' }}
            </span>
            <span class="text-xl font-extrabold text-white">{{ $disc }}</span>
          </div>
          <h3 class="text-sm font-bold text-white mb-1">{{ $promo->name }}</h3>
          <p class="text-xs text-slate-400 clamp-2 mb-4">{{ $promo->description }}</p>
          <div class="flex items-center justify-between border-t border-white/10 pt-3">
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 px-3 py-1.5 rounded-xl group-hover:bg-white/10 transition-colors">
              <i data-lucide="ticket" style="width:12px;height:12px;color:#fb923c"></i>
              <span class="text-xs font-mono font-bold text-white tracking-widest">{{ $promo->code }}</span>
            </div>
            @if($promo->expires_at)
              <span class="text-xs text-primary-200 flex items-center gap-1">
                <i data-lucide="clock" style="width:11px;height:11px"></i> Until {{ $promo->expires_at->format('M d') }}
              </span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ══════════════════════════════ TESTIMONIALS CAROUSEL ══════════════════════════════ --}}
@if(isset($reviews) && $reviews->isNotEmpty())
<section class="py-20 bg-slate-50 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    {{-- Section Header --}}
    <div class="text-center max-w-lg mx-auto mb-12">
      <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1">Passenger Stories</p>
      <h2 class="text-3xl font-extrabold text-slate-900">Loved by <span class="text-primary-600">Travelers</span></h2>
      @if(isset($stats['avgRating']) && $stats['avgRating'])
        <p class="text-slate-500 text-sm mt-1.5">
          <span class="text-amber-500 font-bold">{{ number_format($stats['avgRating'], 1) }} ★</span> average from {{ $reviews->count() }}+ verified passengers
        </p>
      @endif
    </div>

    {{-- Swiper Container --}}
    <div class="relative px-4 sm:px-12">
      <div class="swiper testimonial-swiper !pb-14">
        <div class="swiper-wrapper">
          @foreach($reviews as $i => $review)
            @php
              $avCols = ['bg-blue-100 text-blue-700','bg-emerald-100 text-emerald-700','bg-violet-100 text-violet-700','bg-sky-100 text-sky-700','bg-pink-100 text-pink-700','bg-amber-100 text-amber-700'];
              $av = $avCols[$i % count($avCols)];
            @endphp
            <div class="swiper-slide h-auto">
              <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-shadow duration-300 h-full flex flex-col">
                
                <!-- Rating Stars -->
                <div class="flex gap-1 mb-4">
                  @for($s = 1; $s <= 5; $s++)
                    <i data-lucide="star" style="width:16px;height:16px;{{ $s <= $review->rating ? 'color:#f59e0b;fill:#f59e0b' : 'color:#e2e8f0;fill:#e2e8f0' }}"></i>
                  @endfor
                </div>

                <!-- Review Text -->
                <blockquote class="text-slate-700 text-base leading-relaxed mb-6 flex-1 italic">
                  "{{ $review->comment }}"
                </blockquote>

                <!-- Reviewer Info -->
                <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                  <div class="w-12 h-12 rounded-full {{ $av }} flex items-center justify-center text-lg font-bold shrink-0">
                    {{ strtoupper(substr($review->user?->name ?? 'P', 0, 1)) }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-slate-900 font-bold truncate">{{ $review->user?->name ?? 'Passenger' }}</div>
                    <div class="text-slate-500 text-xs flex items-center gap-1.5 mt-0.5">
                      <i data-lucide="check-circle" style="width:12px;height:12px;color:#10b981"></i>
                      Verified · {{ ucfirst($review->type ?? 'general') }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="swiper-pagination !bottom-0"></div>
      </div>
      
      <!-- Navigation Arrows -->
      <div class="swiper-button-prev bg-white w-12 h-12 rounded-full shadow-lg border border-slate-100 hidden sm:flex -left-4 transition-transform hover:scale-110" style="--swiper-navigation-color: #ea580c; --swiper-navigation-size: 20px;"></div>
      <div class="swiper-button-next bg-white w-12 h-12 rounded-full shadow-lg border border-slate-100 hidden sm:flex -right-4 transition-transform hover:scale-110" style="--swiper-navigation-color: #ea580c; --swiper-navigation-size: 20px;"></div>
    </div>
  </div>
</section>
@endif

{{-- ══════════════════════════════ CTA BANNER ══════════════════════════════ --}}
<section class="relative py-16 overflow-hidden" style="min-height: 400px;">
  <!-- Parallax Background Image -->
  <div class="parallax-bg absolute inset-0" 
       style="background-image: url('{{ asset('bus.jpg') }}'); 
              background-size: cover; 
              background-position: center; 
              background-repeat: no-repeat;
              background-attachment: fixed;
              z-index: 0;">
  </div>
  <!-- Overlay for better text visibility -->
  <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/60" style="z-index: 1;"></div>
  
  <div class="relative max-w-2xl mx-auto px-4 text-center" style="z-index: 2;">
    <i data-lucide="bus" style="width:36px;height:36px;color:rgba(255,255,255,.7);margin:0 auto 14px"></i>
    <h2 class="text-3xl font-extrabold text-white mb-3">{{ $cmsSettings['cta_title'] ?? 'Ready to ride?' }}</h2>
    <p class="text-primary-100 text-base mb-7">
      {{ $cmsSettings['cta_description'] ?? "Search from ".($stats['totalRoutes'] ?? 0)."+ routes and book your seat in under 2 minutes." }}
    </p>
    <a href="{{ route('landing.ticket_booking') }}"
       class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-primary-700 font-extrabold rounded-2xl hover:bg-primary-50 transition-colors shadow-lg">
      <i data-lucide="search" style="width:15px;height:15px"></i> Search Trips Now
    </a>
  </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const HOME_ROUTE_PAIRS = @json($routePairs ?? []);

  document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.hero-swiper', {
      loop: true,
      effect: 'fade',
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  });

  function swapHeroCities() {
    const from = document.getElementById('hero-from');
    const to   = document.getElementById('hero-to');
    const temp = from.value;
    from.value = to.value;
    to.value = temp;
    // Update visible labels
    document.getElementById('hero-from-text').textContent = from.value || 'Leaving from...';
    document.getElementById('hero-from-text').classList.toggle('text-slate-500', !from.value);
    document.getElementById('hero-from-text').classList.toggle('text-slate-800', !!from.value);

    // Refresh destination options based on new origin
    initHeroDependentDropdowns();

    // Keep swapped destination only if valid for the new origin
    const desiredTo = to.value;
    const validTos = window.__heroDestIndex?.get(from.value) || new Set();
    if (!desiredTo || !validTos.has(desiredTo)) {
      to.value = '';
      document.getElementById('hero-to-text').textContent = 'Going to...';
      document.getElementById('hero-to-text').classList.add('text-slate-500');
      document.getElementById('hero-to-text').classList.remove('text-slate-800');
    } else {
      document.getElementById('hero-to-text').textContent = desiredTo;
      document.getElementById('hero-to-text').classList.remove('text-slate-500');
      document.getElementById('hero-to-text').classList.add('text-slate-800');
    }
  }

  function buildDestinationIndex(routePairs) {
    const index = new Map();
    for (const pair of routePairs || []) {
      if (!pair?.from || !pair?.to) continue;
      if (!index.has(pair.from)) index.set(pair.from, new Set());
      index.get(pair.from).add(pair.to);
    }
    return index;
  }

  function setSelectOptions(selectEl, values, placeholder) {
    if (!selectEl) return;
    const current = selectEl.value;
    selectEl.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.textContent = placeholder;
    selectEl.appendChild(ph);

    const sorted = Array.from(values || []).sort((a, b) => a.localeCompare(b));
    for (const v of sorted) {
      const opt = document.createElement('option');
      opt.value = v;
      opt.textContent = v;
      selectEl.appendChild(opt);
    }

    // keep selection if still valid
    if (sorted.includes(current)) {
      selectEl.value = current;
    }
  }

  function initHeroDependentDropdowns() {
    const fromEl = document.getElementById('hero-from');
    const toEl = document.getElementById('hero-to');
    const toMenu = document.getElementById('hero-to-menu');
    if (!fromEl || !toEl) return;

    const destIndex = buildDestinationIndex(HOME_ROUTE_PAIRS);
    window.__heroDestIndex = destIndex;

    const fromValue = fromEl.value;
    const destinations = fromValue ? (destIndex.get(fromValue) || new Set()) : new Set();

    // Build destination menu
    if (!toMenu) return;
    toMenu.innerHTML = '';

    if (!fromValue) {
      toMenu.innerHTML = `<div class="px-4 py-2.5 text-sm text-slate-500">Select an origin first.</div>`;
      return;
    }

    const sorted = Array.from(destinations).sort((a, b) => a.localeCompare(b));
    if (sorted.length === 0) {
      toMenu.innerHTML = `<div class="px-4 py-2.5 text-sm text-slate-500">No destinations available.</div>`;
      return;
    }

    for (const d of sorted) {
      const item = document.createElement('div');
      item.className = 'px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors';
      item.dataset.value = d;
      item.textContent = d;
      item.addEventListener('click', () => selectHeroOption('to', d, d));
      toMenu.appendChild(item);
    }
  }

  // Copy Code
  function copyCode(code) {
    navigator.clipboard.writeText(code)
      .then(() => toast('Code "' + code + '" copied to clipboard!', 'success'))
      .catch(() => toast('Could not copy code.', 'error'));
  }

  // Hero Tabs Logic
  function switchHeroTab(tab) {
    const searchForm = document.getElementById('hero-search-form');
    const trackForm  = document.getElementById('hero-track-form');
    const searchBtn  = document.getElementById('tab-btn-search');
    const trackBtn   = document.getElementById('tab-btn-track');

    const activeClasses = 'bg-white text-primary-600 shadow-sm border border-slate-200'.split(' ');
    const inactiveClasses = 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50 border border-transparent'.split(' ');

    if (tab === 'search') {
      searchForm.classList.remove('hidden');
      trackForm.classList.add('hidden');
      
      searchBtn.classList.remove(...inactiveClasses);
      searchBtn.classList.add(...activeClasses);
      
      trackBtn.classList.remove(...activeClasses);
      trackBtn.classList.add(...inactiveClasses);
    } else {
      searchForm.classList.add('hidden');
      trackForm.classList.remove('hidden');
      
      trackBtn.classList.remove(...inactiveClasses);
      trackBtn.classList.add(...activeClasses);
      
      searchBtn.classList.remove(...activeClasses);
      searchBtn.classList.add(...inactiveClasses);
    }
  }

  // Track Trip AJAX
  async function trackTripCode() {
    const code = document.getElementById('track-trip-code').value.trim();
    const resultBox = document.getElementById('track-result');
    const btn = document.getElementById('btn-track-trip');
    const orig = btn.innerHTML;

    if (!code) return toast('Please enter a trip code.', 'error');

    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="animate-spin" style="width:14px;height:14px"></i> Tracking...`;
    lucide.createIcons();
    
    try {
      const res = await fetch(`{{ route('landing.track_trip') }}?trip_code=${code}`);
      const j = await res.json();
      
      resultBox.classList.remove('hidden');
      if (j.success) {
        const t = j.trip;
        const statusKey = (t.status || '').toLowerCase();
        const color = statusKey.includes('cancel')
          ? 'text-red-600'
          : (statusKey.includes('complete') ? 'text-slate-600' : (statusKey.includes('ongoing') ? 'text-blue-600' : 'text-emerald-600'));

        const badgeBg = statusKey.includes('cancel')
          ? 'bg-red-50 border-red-200'
          : (statusKey.includes('complete') ? 'bg-slate-50 border-slate-200' : (statusKey.includes('ongoing') ? 'bg-blue-50 border-blue-200' : 'bg-emerald-50 border-emerald-200'));

        resultBox.innerHTML = `
          <div class="rounded-xl border ${badgeBg} bg-white p-4">
            <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-100">
              <span class="font-extrabold text-slate-900">${t.code}</span>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold border ${badgeBg} ${color}">${t.status}</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-slate-700">
              <div class="flex items-center gap-2"><i data-lucide="map-pin" style="width:14px;height:14px;color:#64748b"></i><span>${t.origin} <span class="text-slate-400">→</span> ${t.destination}</span></div>
              <div class="flex items-center gap-2"><i data-lucide="bus" style="width:14px;height:14px;color:#64748b"></i><span>${t.bus_type}</span></div>
              <div class="flex items-center gap-2"><i data-lucide="clock" style="width:14px;height:14px;color:#64748b"></i><span>Departs: <span class="font-semibold">${t.departure}</span></span></div>
              <div class="flex items-center gap-2"><i data-lucide="clock-3" style="width:14px;height:14px;color:#64748b"></i><span>Arrives: <span class="font-semibold">${t.arrival || 'TBD'}</span></span></div>
            </div>
          </div>
        `;
      } else {
        resultBox.innerHTML = `
          <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 font-semibold">
            ${j.message}
          </div>
        `;
      }
      lucide.createIcons();
    } catch (e) {
      toast('Error tracking trip.', 'error');
    } finally {
      btn.innerHTML = orig;
      btn.disabled = false;
      lucide.createIcons();
    }
  }

  // Enter key triggers Track Trip
  document.addEventListener('DOMContentLoaded', () => {
    const trackInput = document.getElementById('track-trip-code');
    trackInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        trackTripCode();
      }
    });
  });

  // Testimonial Swiper Initialization
  if (document.querySelector('.testimonial-swiper')) {
    new Swiper('.testimonial-swiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: {
        delay: 6000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.testimonial-swiper .swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 2 }
      }
    });
  }

  // Travel FAQ Logic
  function toggleFaq(index) {
    const content = document.getElementById(`faq-content-${index}`);
    const icon = document.getElementById(`faq-icon-${index}`);
    
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      icon.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      icon.style.transform = 'rotate(0deg)';
    }
  }

  document.addEventListener('DOMContentLoaded', initHeroDependentDropdowns);

  // Custom Tailwind "select" logic (home page only)
  function setHeroFieldError(kind, message) {
    const input = document.getElementById(kind === 'date' ? 'hero-date' : (kind === 'from' ? 'hero-from' : 'hero-to'));
    const error = document.getElementById(kind === 'date' ? 'hero-date-error' : (kind === 'from' ? 'hero-from-error' : 'hero-to-error'));
    const container = kind === 'date'
      ? document.getElementById('hero-date')?.closest('.relative')
      : document.querySelector(`[data-hero-select="${kind}"]`)?.querySelector('button');

    if (error) {
      error.textContent = message || '';
      error.classList.toggle('hidden', !message);
    }
    if (container) {
      container.classList.toggle('border-red-300', !!message);
      container.classList.toggle('focus:border-red-500', !!message);
      container.classList.toggle('focus:ring-red-500/20', !!message);
      container.classList.toggle('border-slate-200', !message);
    }
  }

  function clearHeroErrors() {
    setHeroFieldError('from', '');
    setHeroFieldError('to', '');
    setHeroFieldError('date', '');
  }

  function closeHeroSelects() {
    document.getElementById('hero-from-menu')?.classList.add('hidden');
    document.getElementById('hero-to-menu')?.classList.add('hidden');
  }

  function toggleHeroSelect(kind) {
    const menu = document.getElementById(kind === 'from' ? 'hero-from-menu' : 'hero-to-menu');
    if (!menu) return;
    const isHidden = menu.classList.contains('hidden');
    closeHeroSelects();
    if (isHidden) menu.classList.remove('hidden');
  }

  function selectHeroOption(kind, value, label) {
    const input = document.getElementById(kind === 'from' ? 'hero-from' : 'hero-to');
    const text = document.getElementById(kind === 'from' ? 'hero-from-text' : 'hero-to-text');
    const menu = document.getElementById(kind === 'from' ? 'hero-from-menu' : 'hero-to-menu');
    if (!input || !text) return;

    input.value = value || '';
    text.textContent = value ? (label || value) : (kind === 'from' ? 'Leaving from...' : 'Going to...');
    text.classList.toggle('text-slate-500', !value);
    text.classList.toggle('text-slate-800', !!value);

    if (menu) menu.classList.add('hidden');
    setHeroFieldError(kind, '');

    if (kind === 'from') {
      // Reset destination and rebuild its menu
      const toInput = document.getElementById('hero-to');
      const toText = document.getElementById('hero-to-text');
      if (toInput) toInput.value = '';
      if (toText) {
        toText.textContent = 'Going to...';
        toText.classList.add('text-slate-500');
        toText.classList.remove('text-slate-800');
      }
      initHeroDependentDropdowns();
    }
  }

  document.addEventListener('click', (e) => {
    const container = e.target.closest('[data-hero-select]');
    if (!container) closeHeroSelects();
  });

  function isValidISODate(value) {
    if (!value || typeof value !== 'string') return false;
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
    const d = new Date(value + 'T00:00:00');
    return !Number.isNaN(d.getTime());
  }

  function prefillHeroSearchFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const from = params.get('from') || params.get('f') || '';
    const to = params.get('to') || params.get('t') || '';
    const date = params.get('date') || params.get('d') || '';

    const fromMenu = document.getElementById('hero-from-menu');
    const fromExists = fromMenu ? Array.from(fromMenu.querySelectorAll('[data-value]')).some(el => el.dataset.value === from) : false;

    if (from && fromExists) {
      selectHeroOption('from', from, from);
      // rebuild destination menu
      initHeroDependentDropdowns();

      const validTos = window.__heroDestIndex?.get(from) || new Set();
      if (to && validTos.has(to)) {
        selectHeroOption('to', to, to);
      }
    }

    const dateEl = document.getElementById('hero-date');
    if (dateEl && isValidISODate(date)) {
      const min = dateEl.getAttribute('min');
      if (!min || date >= min) {
        dateEl.value = date;
        setHeroFieldError('date', '');
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    // ensure lucide icons applied inside custom dropdown buttons
    try { lucide?.createIcons?.(); } catch (e) {}
    prefillHeroSearchFromUrl();
  });

  document.getElementById('hero-search-form')?.addEventListener('submit', (e) => {
    clearHeroErrors();

    const fromVal = document.getElementById('hero-from')?.value || '';
    const toVal = document.getElementById('hero-to')?.value || '';
    const dateEl = document.getElementById('hero-date');
    const dateVal = dateEl?.value || '';

    let hasError = false;

    if (!fromVal) {
      setHeroFieldError('from', 'Please select an origin city.');
      hasError = true;
    }
    if (!toVal) {
      setHeroFieldError('to', 'Please select a destination city.');
      hasError = true;
    }
    if (!dateVal) {
      setHeroFieldError('date', 'Please select a travel date.');
      hasError = true;
    } else if (dateEl?.min && dateVal < dateEl.min) {
      setHeroFieldError('date', `Date must be on or after ${dateEl.min}.`);
      hasError = true;
    }

    if (hasError) {
      e.preventDefault();
      return;
    }
  });

  // Simple parallax effect for CTA banner
  function initParallax() {
    const parallaxElement = document.querySelector('.parallax-bg');
    if (!parallaxElement) return;
    
    function updateParallax() {
      const scrolled = window.pageYOffset;
      const rect = parallaxElement.getBoundingClientRect();
      const elementTop = rect.top + scrolled;
      
      // Apply parallax effect when element is in view
      if (elementTop < window.innerHeight && elementTop > -rect.height) {
        const speed = 0.3;
        const yPos = -(scrolled * speed);
        parallaxElement.style.transform = `translateY(${yPos}px)`;
      }
    }
    
    // Throttled scroll handler
    let ticking = false;
    function handleScroll() {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
        setTimeout(() => { ticking = false; }, 100);
      }
    }
    
    // Initialize and add listeners
    updateParallax();
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', updateParallax, { passive: true });
  }
  
  // Initialize parallax
  initParallax();
</script>
@endpush