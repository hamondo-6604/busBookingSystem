<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Mindanao Express</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Prevent FOUC (Flash of Unstyled Content) -->
    <script>
      (function() {
        try {
          var theme = localStorage.getItem('admin_theme');
          if (!theme) {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
          }
          if (theme === 'dark') {
            document.documentElement.classList.add('dark');
          }
        } catch (e) {}
      })();
    </script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    
    <!-- Flowbite -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              primary: {"50":"#f0f9ff","100":"#e0f2fe","200":"#bae6fd","300":"#7dd3fc","400":"#38bdf8","500":"#0ea5e9","600":"#0284c7","700":"#0369a1","800":"#075985","900":"#0c4a6e"}
            }
          }
        }
      }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }

        @keyframes adminToastIn  { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes adminToastOut { from { opacity: 1; } to { opacity: 0; } }
        .admin-toast-enter { animation: adminToastIn .3s ease forwards; }
        .admin-toast-leave { animation: adminToastOut .25s ease forwards; }

        /* Native selects: custom chevron with proper right padding (all admin modals & forms) */
        .admin-main select:not([multiple]):not([size]) {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-position: right 0.875rem center;
            background-repeat: no-repeat;
            background-size: 0.875rem;
            padding-right: 2.5rem !important;
        }
        .dark .admin-main select:not([multiple]):not([size]) {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        }
        
        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
        }

        .sidebar-brand {
            height: 64px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 800;
            font-size: 1.125rem;
            color: #0f172a;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        .sidebar-nav::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }

        .nav-group-label {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #94a3b8;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.625rem 1.5rem;
            color: #475569;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
            background: transparent;
        }

        .nav-item:hover {
            color: #0f172a;
            background: transparent;
        }

        .nav-item.active {
            color: #0284c7;
            font-weight: 600;
            background: transparent;
        }

        
        .nav-icon {
            width: 24px;
            font-size: 1rem;
            color: #94a3b8;
            margin-right: 0.75rem;
            text-align: center;
        }

        .nav-item:hover .nav-icon {
            color: #64748b;
        }

        .nav-item.active .nav-icon {
            color: #0ea5e9;
        }

        .nav-badge {
            margin-left: auto;
            background: #e2e8f0;
            color: #475569;
            font-size: 0.7rem;
            padding: 0.125rem 0.375rem;
            border-radius: 999px;
            font-weight: 700;
        }

        .nav-badge.gold {
            background: #fef08a;
            color: #854d0e;
        }

        /* Top Header */
        .admin-header {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: fixed;
            top: 0;
            right: 0;
            left: 260px;
            z-index: 30;
        }

        /* Main Content */
        .admin-main {
            margin-left: 260px;
            margin-top: 64px;
            padding: 2rem;
            min-height: calc(100vh - 64px);
        }
        /* Dark Mode Overrides */
        .dark body { background-color: #0f172a; color: #f8fafc; }
        .dark .admin-sidebar { background: #1e293b; border-right-color: #334155; }
        .dark .sidebar-brand { border-bottom-color: #334155; color: #f8fafc; }
        .dark .nav-group-label { color: #64748b; }
        .dark .nav-item { color: #cbd5e1; background: transparent; }
        .dark .nav-item:hover { color: #f8fafc; background: transparent; }
        .dark .nav-item.active { color: #38bdf8; background: transparent; }
        .dark .nav-icon { color: #64748b; }
        .dark .nav-item:hover .nav-icon { color: #94a3b8; }
        .dark .nav-item.active .nav-icon { color: #38bdf8; }
        .dark .nav-badge { background: #334155; color: #cbd5e1; }
        .dark .nav-badge.gold { background: rgba(133, 77, 14, 0.4); color: #fef08a; }
        /* Flowbite Datepicker Disabled State */
        .datepicker-cell.disabled,
        .datepicker-cell[disabled] {
            color: #94a3b8 !important; /* text-slate-400 */
            cursor: not-allowed !important;
            background-color: transparent !important;
            opacity: 0.4;
        }
        .dark .datepicker-cell.disabled,
        .dark .datepicker-cell[disabled] {
            color: #475569 !important; /* text-slate-600 */
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar shadow-sm">
        <a href="{{ route('landing.home') }}" class="sidebar-brand text-primary-600 hover:text-primary-700 transition">
            <i class="fa-solid fa-bus mr-2"></i>
            Mindanao Express
        </a>
        <nav class="sidebar-nav" aria-label="Admin navigation">
            {{-- Overview --}}
            <div class="nav-group-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span> Dashboard
            </a>
            {{-- <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Analytics
            </a> --}}

            {{-- Operations --}}
            @php
                $newBookingsCount = auth()->user()->appNotifications()
                    ->unread()
                    ->where('type', 'booking_confirmed')
                    ->count();
                $pendingFeedbackCount = auth()->user()->appNotifications()
                    ->unread()
                    ->where('type', 'new_feedback')
                    ->count();
            @endphp
            <div class="nav-group-label">Operations</div>
            <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-ticket"></i></span> Bookings
                <span id="sidebar-bookings-badge" class="nav-badge {{ $newBookingsCount > 0 ? '' : 'hidden' }}">{{ $newBookingsCount }}</span>
            </a>
            <a href="{{ route('admin.trips.index') }}" class="nav-item {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-location-dot"></i></span> Trips &amp; Schedules
            </a>
            <a href="{{ route('admin.routes.index') }}" class="nav-item {{ request()->routeIs('admin.routes.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-route"></i></span> Routes
            </a>
            <a href="{{ route('admin.stops.index') }}" class="nav-item {{ request()->routeIs('admin.stops.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-map-pin"></i></span> Bus Stops
            </a>
            <a href="{{ route('admin.terminals.index') }}" class="nav-item {{ request()->routeIs('admin.terminals.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-building"></i></span> Terminals
            </a>
            <a href="{{ route('admin.payments.index') }}" class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-credit-card"></i></span> Payments
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="nav-item {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-tag"></i></span> Promotions
            </a>

            {{-- Fleet --}}
            <div class="nav-group-label">Fleet</div>
            <a href="{{ route('admin.buses.index') }}" class="nav-item {{ request()->routeIs('admin.buses.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-bus"></i></span> Buses
            </a>
            <a href="{{ route('admin.bus-types.index') }}" class="nav-item {{ request()->routeIs('admin.bus-types.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-couch"></i></span> Bus Types
            </a>
            <a href="{{ route('admin.seats.index') }}" class="nav-item {{ request()->routeIs('admin.seats.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-chair"></i></span> Seats &amp; Layouts
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="nav-item {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-wrench"></i></span> Maintenance
            </a>

            {{-- People --}}
            @php
                $newUserCount = auth()->user()->appNotifications()
                    ->unread()
                    ->where('type', 'new_user')
                    ->count();
                $newDriverCount = auth()->user()->appNotifications()
                    ->unread()
                    ->where('type', 'new_driver')
                    ->count();
            @endphp
            <div class="nav-group-label">People</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Users
                <span id="sidebar-users-badge" class="nav-badge {{ $newUserCount > 0 ? '' : 'hidden' }}">{{ $newUserCount }}</span>
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="nav-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-id-badge"></i></span> Drivers
                <span id="sidebar-drivers-badge" class="nav-badge {{ $newDriverCount > 0 ? '' : 'hidden' }}">{{ $newDriverCount }}</span>
            </a>
            <a href="{{ route('admin.feedback.index') }}" class="nav-item {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-comments"></i></span> Feedback
                <span id="sidebar-feedback-badge" class="nav-badge {{ $pendingFeedbackCount > 0 ? '' : 'hidden' }}">{{ $pendingFeedbackCount }}</span>
            </a>

            {{-- System --}}
            <div class="nav-group-label">System</div>
            <a href="{{ route('admin.cms.landing') }}" class="nav-item {{ request()->routeIs('admin.cms.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-laptop-code"></i></span> Content Management
            </a>
            <a href="{{ route('admin.roles.index') }}" class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span> Roles &amp; Permissions
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-bell"></i></span> Notifications
            </a>
            <a href="{{ route('admin.cities.index') }}" class="nav-item {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-city"></i></span> Cities
            </a>
            <a href="{{ route('admin.trash.index') }}" class="nav-item {{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-trash-can"></i></span> Trash
            </a>
        </nav>
    </aside>

    <!-- Header -->
    <header class="admin-header shadow-sm dark:bg-slate-900 dark:border-slate-800 transition-colors">
        
        {{-- Left: Global Search --}}
        <div class="flex-1 flex items-center pr-4">
            <button onclick="openSearch()" class="relative w-full max-w-md hidden sm:flex items-center text-left bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors border border-transparent rounded-xl py-2 pl-3 pr-16 group">
                <i class="fa-solid fa-magnifying-glass text-slate-400 group-hover:text-slate-500 text-sm mr-2"></i>
                <span class="text-sm text-slate-500 dark:text-slate-400">Search bookings, users, or trips...</span>
                <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-60">
                  <kbd class="px-1.5 py-0.5 text-[10px] font-bold text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded shadow-sm">Ctrl</kbd>
                  <kbd class="px-1.5 py-0.5 text-[10px] font-bold text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded shadow-sm">K</kbd>
                </div>
            </button>
        </div>

        {{-- Right: Actions & Profile --}}
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
          
          {{-- Quick Action Dropdown --}}
          <div class="relative hidden sm:block" data-dd>
            <button onclick="document.getElementById('create-dd').classList.toggle('hidden')" class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white rounded-lg text-xs font-bold transition-colors shadow-sm">
                <i class="fa-solid fa-plus text-[10px]"></i> Create
            </button>
            <div id="create-dd" class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-1 z-50">
                <div class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Quick Add</div>
                <a href="{{ route('admin.trips.create') ?? '#' }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">New Trip</a>
                <a href="{{ route('admin.routes.create') ?? '#' }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">New Route</a>
                <a href="{{ route('admin.promotions.create') ?? '#' }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">New Promotion</a>
            </div>
          </div>

          {{-- Notification Dropdown --}}
          @php
            $unreadNotifs = auth()->user()->appNotifications()->unread()->latest()->take(5)->get();
            $unreadCount = auth()->user()->appNotifications()->unread()->count();
          @endphp
          <div class="relative" data-dd>
            <button onclick="document.getElementById('notif-dd').classList.toggle('hidden')" class="relative p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <i class="fa-regular fa-bell text-[1.1rem]"></i>
                <span id="notif-badge" class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900 transition-opacity"></span>
            </button>
            <div id="notif-dd" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <span class="font-bold text-sm text-slate-800 dark:text-white">Notifications</span>
                    <div id="notif-mark-read" class="{{ $unreadCount > 0 ? '' : 'hidden' }}">
                      <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" class="m-0">
                        @csrf
                        <button type="submit" class="text-xs text-primary-600 dark:text-primary-400 cursor-pointer hover:underline">Mark all read</button>
                      </form>
                    </div>
                </div>
                <div class="max-h-64 overflow-y-auto" id="notif-list" data-count="{{ $unreadCount }}">
                    @include('admin.notifications.partials.dropdown-items')
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="block text-center px-4 py-2 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">View all notifications</a>
            </div>
          </div>

          <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 hidden sm:block mx-1"></div>

          {{-- User dropdown --}}
          <div class="relative" data-dd>
            <button onclick="document.getElementById('user-dd').classList.toggle('hidden')"
                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
              @if(auth()->user()->image_url)
                <img src="{{ asset('storage/' . auth()->user()->image_url) }}" alt="Admin Profile" class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-slate-700">
              @else
                <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-slate-800 text-primary-700 dark:text-primary-400 text-xs font-bold flex items-center justify-center">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
              @endif
              <span class="text-sm font-medium text-slate-700 dark:text-slate-200 max-w-[110px] truncate">{{ auth()->user()->name }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
            </button>

            <div id="user-dd"
                 class="hidden absolute right-0 top-full mt-2 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50">

              {{-- User info --}}
              <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Signed in as Admin</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
              </div>

              <a href="{{ route('landing.home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-house w-4 text-center text-primary-500"></i> Back to Site
              </a>
              
              {{-- Theme Toggle --}}
              <button onclick="toggleAdminTheme(); document.getElementById('user-dd').classList.add('hidden')" 
                      class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-left">
                <div id="admin-theme-icon" class="flex items-center justify-center w-4 text-center">
                  <i class="fa-solid fa-moon text-slate-500"></i>
                </div>
                <span id="admin-theme-text">Switch to Dark Mode</span>
              </button>
              
              <div class="border-t border-slate-100 dark:border-slate-700 mt-1.5 pt-1">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Sign Out
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
    </header>

    <!-- Search Modal -->
    <div id="search-modal" class="hidden fixed inset-0 z-[100] items-start justify-center pt-16 sm:pt-24 px-4 pb-4">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeSearch()"></div>
      <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-700 transform transition-all">
        
        <div class="flex items-center px-4 py-3 border-b border-slate-100 dark:border-slate-700">
          <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg mr-3"></i>
          <input type="text" id="search-input" placeholder="Search routes, trips, or bookings..." class="flex-1 bg-transparent border-0 focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400 text-lg py-2" autocomplete="off" onkeyup="filterSearch()">
          <kbd class="hidden sm:inline-block px-2 py-1 text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 rounded ml-3">ESC</kbd>
        </div>

        <div class="max-h-96 overflow-y-auto p-2" id="search-results">
          <!-- Predefined Links -->
          <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider search-category">Quick Links</div>
          <a href="{{ route('admin.bookings.index') }}" class="search-item flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-ticket"></i></div>
            <div class="flex-1"><div class="text-sm font-semibold">Manage Bookings</div><div class="text-xs text-slate-500">View and update reservations</div></div>
          </a>
          <a href="{{ route('admin.trips.index') }}" class="search-item flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-location-dot"></i></div>
            <div class="flex-1"><div class="text-sm font-semibold">Trip Schedules</div><div class="text-xs text-slate-500">Manage daily trips</div></div>
          </a>
          <a href="{{ route('admin.users.index') }}" class="search-item flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-users"></i></div>
            <div class="flex-1"><div class="text-sm font-semibold">User Directory</div><div class="text-xs text-slate-500">Search customers and staff</div></div>
          </a>
          
          <!-- Hidden No Results State -->
          <div id="search-empty" class="hidden text-center py-8 text-slate-500 dark:text-slate-400">
            <i class="fa-regular fa-face-frown text-3xl mb-3 opacity-50"></i>
            <p class="text-sm">No results found for your search.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <main class="admin-main transition-colors dark:bg-slate-900 dark:text-slate-200">
        @yield('content')
    </main>

    <script>
      // Global Search Modal Logic
      const searchModal = document.getElementById('search-modal');
      const searchInput = document.getElementById('search-input');
      
      function openSearch() {
        searchModal.classList.remove('hidden');
        searchModal.classList.add('flex');
        setTimeout(() => searchInput.focus(), 50);
        document.body.style.overflow = 'hidden';
      }

      function closeSearch() {
        searchModal.classList.add('hidden');
        searchModal.classList.remove('flex');
        document.body.style.overflow = '';
      }

      function filterSearch() {
        const val = searchInput.value.toLowerCase();
        let hasVisible = false;
        document.querySelectorAll('.search-item').forEach(item => {
          const text = item.textContent.toLowerCase();
          if (text.includes(val)) {
            item.style.display = 'flex';
            hasVisible = true;
          } else {
            item.style.display = 'none';
          }
        });
        document.getElementById('search-empty').classList.toggle('hidden', hasVisible);
        document.querySelectorAll('.search-category').forEach(cat => {
            cat.style.display = val.length > 0 ? 'none' : 'block';
        });
      }

      // Keyboard shortcuts
      document.addEventListener('keydown', (e) => {
        // Ctrl+K to open search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
          e.preventDefault();
          openSearch();
        }
        // ESC to close search
        if (e.key === 'Escape' && !searchModal.classList.contains('hidden')) {
          closeSearch();
        }
      });
      // Close dropdowns on outside click
      document.addEventListener('click', e => {
        const dd = document.getElementById('user-dd');
        if (dd && !dd.closest('[data-dd]')?.contains(e.target)) dd.classList.add('hidden');
        const notifDd = document.getElementById('notif-dd');
        if (notifDd && !notifDd.closest('[data-dd]')?.contains(e.target)) notifDd.classList.add('hidden');
        
        // Close custom selects
        document.querySelectorAll('[data-custom-select]').forEach(container => {
            if (!container.contains(e.target)) {
                const menu = container.querySelector('.custom-select-menu');
                if (menu) menu.classList.add('hidden');
            }
        });
      });

      // Custom Select Logic
      function selectCustomOption(el) {
          const container = el.closest('[data-custom-select]');
          const input = container.querySelector('.custom-select-input');
          const textBtn = container.querySelector('.custom-select-text');
          const menu = container.querySelector('.custom-select-menu');
          
          input.value = el.dataset.value;
          textBtn.textContent = el.textContent.trim();
          menu.classList.add('hidden');
          
          // Clear error border if exists
          input.classList.remove('border-red-500');
          const errorMsg = container.parentElement.querySelector('.error-text');
          if (errorMsg) errorMsg.remove();
      }

      // Real-time Notification Polling
      function pollNotifications() {
        fetch("{{ route('admin.notifications.poll') }}", {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          const list = document.getElementById('notif-list');
          const badge = document.getElementById('notif-badge');
          const markRead = document.getElementById('notif-mark-read');
          const bookingsBadge = document.getElementById('sidebar-bookings-badge');
          const feedbackBadge = document.getElementById('sidebar-feedback-badge');
          
          if (list && parseInt(list.dataset.count) !== data.count) {
            list.dataset.count = data.count;
            list.innerHTML = data.html;
            
            if (data.count > 0) {
              badge.classList.remove('hidden');
              markRead.classList.remove('hidden');
            } else {
              badge.classList.add('hidden');
              markRead.classList.add('hidden');
            }
          }

          // Update sidebar bookings badge
          if (bookingsBadge) {
            if (data.new_bookings_count > 0) {
              bookingsBadge.textContent = data.new_bookings_count;
              bookingsBadge.classList.remove('hidden');
            } else {
              bookingsBadge.classList.add('hidden');
            }
          }

          // Update sidebar feedback badge
          if (feedbackBadge) {
            if (data.pending_feedback_count > 0) {
              feedbackBadge.textContent = data.pending_feedback_count;
              feedbackBadge.classList.remove('hidden');
            } else {
              feedbackBadge.classList.add('hidden');
            }
          }

          // Update sidebar users badge
          const usersBadge = document.getElementById('sidebar-users-badge');
          if (usersBadge) {
            if (data.new_user_count > 0) {
              usersBadge.textContent = data.new_user_count;
              usersBadge.classList.remove('hidden');
            } else {
              usersBadge.classList.add('hidden');
            }
          }

          // Update sidebar drivers badge
          const driversBadge = document.getElementById('sidebar-drivers-badge');
          if (driversBadge) {
            if (data.new_driver_count > 0) {
              driversBadge.textContent = data.new_driver_count;
              driversBadge.classList.remove('hidden');
            } else {
              driversBadge.classList.add('hidden');
            }
          }
        })
        .catch(err => console.error('Notification polling error', err));
      }

      // Admin Theme Management
      function getAdminTheme() {
        return localStorage.getItem('admin_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      }

      function setAdminTheme(theme) {
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('admin_theme', theme);
        updateAdminThemeToggle(theme);
      }

      function toggleAdminTheme() {
        const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        setAdminTheme(currentTheme === 'light' ? 'dark' : 'light');
      }

      function updateAdminThemeToggle(theme) {
        const iconContainer = document.getElementById('admin-theme-icon');
        const textContainer = document.getElementById('admin-theme-text');
        
        if (iconContainer && textContainer) {
          if (theme === 'dark') {
            iconContainer.innerHTML = '<i class="fa-solid fa-sun text-amber-500"></i>';
            textContainer.textContent = 'Switch to Light Mode';
          } else {
            iconContainer.innerHTML = '<i class="fa-solid fa-moon text-slate-500"></i>';
            textContainer.textContent = 'Switch to Dark Mode';
          }
        }
      }

      // Admin Modal Management
      function openAdminModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          // small delay to allow display:flex to apply before animating opacity
          setTimeout(() => {
            modal.classList.remove('opacity-0');
            const panel = modal.querySelector('.admin-modal-panel');
            if (panel) {
              panel.classList.remove('scale-95');
              panel.classList.add('scale-100');
            }
          }, 10);
        }
      }

      function closeAdminModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
          modal.classList.add('opacity-0');
          const panel = modal.querySelector('.admin-modal-panel');
          if (panel) {
            panel.classList.remove('scale-100');
            panel.classList.add('scale-95');
          }
          setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
          }, 300);
        }
      }

      // Toast notifications (add / edit / delete success)
      const ADMIN_TOAST_DURATION_MS = 5000;

      function showAdminToast(msg, type = 'success', durationMs = ADMIN_TOAST_DURATION_MS) {
        const styles = {
          success: 'bg-emerald-500',
          error:   'bg-red-500',
          warning: 'bg-amber-500',
          info:    'bg-blue-500',
        };
        const icons = {
          success: 'fa-circle-check',
          error:   'fa-circle-xmark',
          warning: 'fa-triangle-exclamation',
          info:    'fa-circle-info',
        };
        const el = document.createElement('div');
        el.className = `admin-toast-enter fixed top-20 right-4 z-[9999] flex items-center gap-3 ${styles[type] || styles.success} text-white text-sm font-medium px-4 py-3 rounded-2xl shadow-xl max-w-sm`;
        el.innerHTML = `<i class="fa-solid ${icons[type] || icons.success}"></i><span>${msg}</span>`;
        document.body.appendChild(el);
        setTimeout(() => {
          el.classList.replace('admin-toast-enter', 'admin-toast-leave');
          setTimeout(() => el.remove(), 280);
        }, durationMs);
      }

      // Generic AJAX Form Handler
      async function handleAjaxForm(form, modalId, onSuccess, submitEvent = null) {
        if (submitEvent?.preventDefault) {
          submitEvent.preventDefault();
        } else if (window.event?.preventDefault) {
          window.event.preventDefault();
        }
        
        // Clear previous errors
        form.querySelectorAll('.error-text').forEach(el => el.remove());
        form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
        
        const submitBtn =
          (submitEvent && submitEvent.submitter) ||
          form.querySelector('button[type="submit"]') ||
          (form.id ? document.querySelector(`button[type="submit"][form="${form.id}"]`) : null);
        const originalText = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
        }

        try {
          const formData = new FormData(form);
          const response = await fetch(form.action, {
            method: form.method === 'get' ? 'GET' : 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          const contentType = response.headers.get('content-type') || '';
          const data = contentType.includes('application/json')
            ? await response.json()
            : { message: await response.text() };

          if (!response.ok || data.success === false) {
            if (response.status === 422 && data.errors) {
              for (const [field, errors] of Object.entries(data.errors)) {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                  input.classList.add('border-red-500');
                  const errorP = document.createElement('p');
                  errorP.className = 'error-text text-red-500 text-xs mt-1';
                  errorP.textContent = errors[0];
                  input.parentNode.appendChild(errorP);
                }
              }
            } else {
              showAdminToast(data.message || 'An error occurred.', 'error');
            }
            throw new Error('Request failed');
          }

          // Success
          if (modalId) {
            closeAdminModal(modalId);
          }
          if (data.message) {
            showAdminToast(data.message, 'success');
          }
          if (onSuccess) {
            onSuccess(data);
          } else {
            setTimeout(() => window.location.reload(), ADMIN_TOAST_DURATION_MS + 200);
          }
        } catch (error) {
          console.error(error);
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          }
        }
      }

      // Initialize Theme + flash messages
      document.addEventListener('DOMContentLoaded', () => {
        setAdminTheme(getAdminTheme());
        @if(session('success'))
          showAdminToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
          showAdminToast(@json(session('error')), 'error');
        @endif
      });
    </script>
</body>
</html>