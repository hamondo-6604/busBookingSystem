@php
  $route   = request()->route()?->getName() ?? '';
  $navLinks = [
    ['route' => 'landing.home',           'label' => 'Home',           'icon' => 'home'],
    ['route' => 'landing.ticket_booking', 'label' => 'Search Trips',   'icon' => 'search'],
    ['route' => 'landing.booking_routes', 'label' => 'Routes',         'icon' => 'map-pin'],
  ];
  $authLinks = [
    ['route' => 'manage.bookings',  'label' => 'My Bookings',      'icon' => 'ticket'],
    ['route' => 'landing.account',  'label' => 'Account & Support', 'icon' => 'life-buoy'],
  ];

  function isActive(string $current, string $target): bool {
    return $current === $target || str_starts_with($current, explode('.', $target)[0]);
  }
@endphp

<header class="glass sticky top-0 z-50 border-b border-primary shadow-sm">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

    {{-- Logo --}}
    <a href="{{ route('landing.home') }}" class="flex items-center gap-2 shrink-0 group">
      <div class="w-8 h-8 bg-primary-600 rounded-xl flex items-center justify-center shadow group-hover:bg-primary-700 transition-colors">
        <i data-lucide="bus" style="width:16px;height:16px;color:#fff"></i>
      </div>
      <span class="text-base font-extrabold tracking-tight text-primary">Mindanao<span class="accent-primary">Express</span></span>
    </a>

    {{-- Desktop links --}}
    <div class="hidden md:flex items-center gap-0.5">
      @foreach($navLinks as $l)
        <a href="{{ route($l['route']) }}"
           class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all
                  {{ $route === $l['route'] ? 'bg-secondary accent-primary font-semibold' : 'text-secondary dark:text-secondary hover:text-primary dark:hover:text-primary hover:bg-slate-100 dark:hover:bg-tertiary' }}">
          @if($route === $l['route'])
            <i data-lucide="{{ $l['icon'] }}" style="width:14px;height:14px"></i>
          @endif
          {{ $l['label'] }}
        </a>
      @endforeach

      @auth
        @if(!auth()->user()->isAdmin())
          @foreach($authLinks as $l)
            <a href="{{ route($l['route']) }}"
               class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $route === $l['route'] ? 'bg-secondary accent-primary font-semibold' : 'text-secondary dark:text-secondary hover:text-primary dark:hover:text-primary hover:bg-slate-100 dark:hover:bg-tertiary' }}">
              @if($route === $l['route'])
                <i data-lucide="{{ $l['icon'] }}" style="width:14px;height:14px"></i>
              @endif
              {{ $l['label'] }}
            </a>
          @endforeach
        @endif
      @endauth
    </div>

    {{-- Right side --}}
    <div class="hidden md:flex items-center gap-2">
      @auth
        @if(auth()->user()->isAdmin() || auth()->user()->isDriver())
          @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors shadow-sm flex items-center gap-2">
              <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Dashboard
            </a>
          @else
            <a href="{{ route('driver.dashboard') }}" class="px-4 py-2 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors shadow-sm flex items-center gap-2">
              <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Driver Portal
            </a>
          @endif
          
          <form method="POST" action="{{ route('logout') }}" class="m-0 flex">
            @csrf
            <button type="submit" class="p-2 ml-1 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Sign Out">
              <i data-lucide="log-out" style="width:18px;height:18px"></i>
            </button>
          </form>
        @else
          {{-- User dropdown --}}
          <div class="relative" data-dd>
            <button onclick="document.getElementById('user-dd').classList.toggle('hidden')"
                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-tertiary transition-colors border border-transparent hover:border-slate-200 dark:hover:border-primary">
              @if(auth()->user()->image_url)
                <img src="{{ auth()->user()->avatar }}" alt="Profile" class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-primary">
              @else
                <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-tertiary text-primary-700 dark:text-primary text-xs font-bold flex items-center justify-center">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
              @endif
              <span class="text-sm font-medium text-slate-700 dark:text-secondary max-w-[110px] truncate">{{ auth()->user()->name }}</span>
              <i data-lucide="chevron-down" style="width:13px;height:13px;color:#94a3b8"></i>
            </button>

            <div id="user-dd"
                 class="hidden absolute right-0 top-full mt-2 w-56 bg-white dark:bg-secondary rounded-2xl shadow-xl border border-slate-100 dark:border-primary py-1.5 z-50 animate-slide-up">

              {{-- User info --}}
              <div class="px-4 py-3 border-b border-slate-100 dark:border-primary">
                <p class="text-xs text-slate-400 dark:text-tertiary mb-0.5">Signed in as</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-primary truncate">{{ auth()->user()->name }}</p>
                @if(auth()->user()->discountType)
                  <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300">
                    <i data-lucide="badge-percent" style="width:9px;height:9px"></i>
                    {{ auth()->user()->discountType->display_name }}
                  </span>
                @endif
              </div>

              <a href="{{ route('manage.bookings') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary transition-colors">
                <i data-lucide="ticket" style="width:14px;height:14px;color:#ea580c"></i> My Bookings
              </a>
              <a href="{{ route('landing.account') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary transition-colors">
                <i data-lucide="user" style="width:14px;height:14px;color:#ea580c"></i> My Account
              </a>
              <a href="{{ route('landing.account') }}#support" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary transition-colors">
                <i data-lucide="life-buoy" style="width:14px;height:14px;color:#ea580c"></i> Support
              </a>
              
              {{-- Theme Toggle --}}
              <button onclick="toggleTheme(); document.getElementById('user-dd').classList.add('hidden')" 
                      class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary transition-colors text-left">
                <div id="nav-theme-icon" class="flex items-center justify-center w-5 h-5">
                  <!-- Icons will be set by JavaScript -->
                </div>
                <span id="nav-theme-text">Switch to Dark Mode</span>
              </button>
              
              <div class="border-t border-slate-100 mt-1.5 pt-1">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <i data-lucide="log-out" style="width:14px;height:14px"></i> Sign Out
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endif

      @else
        <button onclick="openModal('auth-modal'); authTab('login')"
                class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-secondary hover:text-slate-900 dark:hover:text-primary hover:bg-slate-100 dark:hover:bg-tertiary rounded-xl transition-colors">
          Sign In
        </button>
        <button onclick="openModal('auth-modal'); authTab('register')"
                class="px-5 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors shadow-sm">
          Get Started
        </button>
      @endauth
    </div>

    {{-- Mobile burger --}}
    <button id="mob-btn" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors">
      <i data-lucide="menu" style="width:20px;height:20px"></i>
    </button>
  </nav>

  {{-- Mobile menu --}}
  <div id="mob-menu" class="hidden md:hidden bg-white border-t border-slate-100">
    <div class="px-4 py-3 space-y-0.5">
      @foreach($navLinks as $l)
        <a href="{{ route($l['route']) }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                  {{ $route === $l['route'] ? 'bg-primary-50 text-primary-700' : 'text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary' }}">
          @if($route === $l['route'])
            <i data-lucide="{{ $l['icon'] }}" style="width:16px;height:16px"></i>
          @endif
          {{ $l['label'] }}
        </a>
      @endforeach

      @auth
        @if(auth()->user()->isAdmin() || auth()->user()->isDriver())
          @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary">
              <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Dashboard
            </a>
          @else
            <a href="{{ route('driver.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary">
              <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Driver Portal
            </a>
          @endif
          
          <div class="pt-2 mt-1 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
                <i data-lucide="log-out" style="width:16px;height:16px"></i> Sign Out
              </button>
            </form>
          </div>
        @else
          @foreach($authLinks as $l)
            <a href="{{ route($l['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ $route === $l['route'] ? 'bg-primary-50 text-primary-700' : 'text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary' }}">
              @if($route === $l['route'])
                <i data-lucide="{{ $l['icon'] }}" style="width:16px;height:16px"></i>
              @endif
              {{ $l['label'] }}
            </a>
          @endforeach
          <div class="pt-2 mt-1 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
                <i data-lucide="log-out" style="width:16px;height:16px"></i> Sign Out
              </button>
            </form>
          </div>
        @endif
      @else
        <div class="pt-2 mt-1 border-t border-slate-100 grid grid-cols-2 gap-2">
          <button onclick="document.getElementById('mob-menu').classList.add('hidden'); openModal('auth-modal'); authTab('login')"
                  class="py-2.5 text-sm font-semibold border border-slate-300 rounded-xl text-slate-700 dark:text-secondary hover:bg-slate-50 dark:hover:bg-tertiary transition-colors">
            Sign In
          </button>
          <button onclick="document.getElementById('mob-menu').classList.add('hidden'); openModal('auth-modal'); authTab('register')"
                  class="py-2.5 text-sm font-semibold bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
            Get Started
          </button>
        </div>
      @endauth
    </div>
  </div>
</header>