<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Mindanao Express') — Bus Booking</title>

  <!-- Prevent FOUC (Flash of Unstyled Content) -->
  <script>
    (function() {
      try {
        var theme = localStorage.getItem('theme');
        if (!theme) {
          theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') document.documentElement.classList.add('dark');
      } catch (e) {}
    })();
  </script>

  {{-- Tailwind CDN --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa',
              300: '#fdba74', 400: '#fb923c', 500: '#f97316',
              600: '#ea580c', 700: '#c2410c', 800: '#9a3412', 900: '#7c2d12',
            },
          },
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
          animation: {
            'fade-in':    'fadeIn .35s ease both',
            'slide-up':   'slideUp .4s ease both',
            'spin-slow':  'spin 2s linear infinite',
          },
          keyframes: {
            fadeIn:  { from: { opacity: 0 }, to: { opacity: 1 } },
            slideUp: { from: { opacity: 0, transform: 'translateY(14px)' }, to: { opacity: 1, transform: 'none' } },
          },
        }
      }
    }
  </script>

  {{-- Inter font --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

  {{-- Lucide icons --}}
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  {{-- Leaflet (global map support) --}}
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
  />

  <style>
    *, *::before, *::after { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    /* CSS Variables for Theme System */
    :root {
      /* Light Mode Colors */
      --bg-primary: #ffffff;
      --bg-secondary: #f8fafc;
      --bg-tertiary: #f1f5f9;
      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-tertiary: #64748b;
      --text-inverse: #ffffff;
      --border-primary: #e2e8f0;
      --border-secondary: #cbd5e1;
      --accent-primary: #ea580c;
      --accent-secondary: #0ea5e9;
      --accent-success: #10b981;
      --accent-warning: #f59e0b;
      --accent-error: #ef4444;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    }

    [data-theme="dark"] {
      /* Dark Mode Colors */
      --bg-primary: #0f172a;
      --bg-secondary: #1e293b;
      --bg-tertiary: #334155;
      --text-primary: #f1f5f9;
      --text-secondary: #e2e8f0;
      --text-tertiary: #94a3b8;
      --text-inverse: #0f172a;
      --border-primary: #334155;
      --border-secondary: #475569;
      --accent-primary: #f97316;
      --accent-secondary: #38bdf8;
      --accent-success: #10b981;
      --accent-warning: #f59e0b;
      --accent-error: #ef4444;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.4);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.5);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.6);
    }

    /* Apply CSS Variables */
    body {
      background-color: var(--bg-primary);
      color: var(--text-primary);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Dark Mode Utilities */
    .bg-primary { background-color: var(--bg-primary); }
    .bg-secondary { background-color: var(--bg-secondary); }
    .bg-tertiary { background-color: var(--bg-tertiary); }
    .text-primary { color: var(--text-primary); }
    .text-secondary { color: var(--text-secondary); }
    .text-tertiary { color: var(--text-tertiary); }
    .text-inverse { color: var(--text-inverse); }
    .border-primary { border-color: var(--border-primary); }
    .border-secondary { border-color: var(--border-secondary); }
    .accent-primary { color: var(--accent-primary); }
    .accent-secondary { color: var(--accent-secondary); }
    .accent-success { color: var(--accent-success); }
    .accent-warning { color: var(--accent-warning); }
    .accent-error { color: var(--accent-error); }

    /* Specific Dark Mode Overrides for Common Classes */
    [data-theme="dark"] .bg-white { background-color: var(--bg-secondary); }
    [data-theme="dark"] .bg-slate-50 { background-color: var(--bg-secondary); }
    [data-theme="dark"] .bg-slate-100 { background-color: var(--bg-tertiary); }
    [data-theme="dark"] .bg-slate-900 { background-color: var(--bg-primary); }
    [data-theme="dark"] .bg-slate-800 { background-color: var(--bg-secondary); }
    
    /* Hover states in dark mode */
    [data-theme="dark"] .hover\:bg-slate-50:hover { background-color: var(--bg-tertiary); }
    [data-theme="dark"] .hover\:bg-slate-100:hover { background-color: var(--bg-tertiary); }
    [data-theme="dark"] .hover\:bg-white:hover { background-color: var(--bg-tertiary); }
    
    /* Profile dropdown specific fixes */
    [data-theme="dark"] .bg-primary-100 { background-color: var(--bg-tertiary); }
    [data-theme="dark"] .text-primary-700 { color: var(--text-primary); }
    
    /* Force dropdown text colors in dark mode */
    [data-theme="dark"] #user-dd .text-slate-700 { color: var(--text-secondary) !important; }
    [data-theme="dark"] #user-dd .text-slate-800 { color: var(--text-primary) !important; }
    [data-theme="dark"] #user-dd .text-slate-400 { color: var(--text-tertiary) !important; }
    
    /* Force dropdown hover states in dark mode */
    [data-theme="dark"] #user-dd .hover\:bg-slate-50:hover { 
      background-color: var(--bg-tertiary) !important; 
    }
    
    /* Form inputs dark mode fixes */
    [data-theme="dark"] input {
      background-color: var(--bg-secondary) !important;
      color: var(--text-primary) !important;
      border-color: var(--border-primary) !important;
    }
    
    [data-theme="dark"] input:focus {
      background-color: var(--bg-secondary) !important;
      border-color: var(--accent-primary) !important;
    }
    
    [data-theme="dark"] input::placeholder {
      color: var(--text-tertiary) !important;
    }
    
    [data-theme="dark"] select {
      background-color: var(--bg-secondary) !important;
      color: var(--text-primary) !important;
      border-color: var(--border-primary) !important;
    }
    
    [data-theme="dark"] select:focus {
      background-color: var(--bg-secondary) !important;
      border-color: var(--accent-primary) !important;
    }
    
    [data-theme="dark"] textarea {
      background-color: var(--bg-secondary) !important;
      color: var(--text-primary) !important;
      border-color: var(--border-primary) !important;
    }
    
    [data-theme="dark"] textarea:focus {
      background-color: var(--bg-secondary) !important;
      border-color: var(--accent-primary) !important;
    }
    
    [data-theme="dark"] textarea::placeholder {
      color: var(--text-tertiary) !important;
    }
    
    /* Specific input styling overrides */
    [data-theme="dark"] .bg-white input,
    [data-theme="dark"] .bg-slate-50 input {
      background-color: var(--bg-tertiary) !important;
    }
    
    [data-theme="dark"] .text-slate-900 input,
    [data-theme="dark"] .text-slate-800 input {
      color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .border-slate-200 input,
    [data-theme="dark"] .border-slate-300 input {
      border-color: var(--border-primary) !important;
    }
    
    [data-theme="dark"] .text-slate-900 { color: var(--text-primary); }
    [data-theme="dark"] .text-slate-800 { color: var(--text-primary); }
    [data-theme="dark"] .text-slate-700 { color: var(--text-secondary); }
    [data-theme="dark"] .text-slate-600 { color: var(--text-secondary); }
    [data-theme="dark"] .text-slate-500 { color: var(--text-tertiary); }
    [data-theme="dark"] .text-slate-400 { color: var(--text-tertiary); }
    [data-theme="dark"] .text-slate-300 { color: var(--text-tertiary); }
    [data-theme="dark"] .text-white { color: var(--text-primary); }
    
    [data-theme="dark"] .border-slate-200 { border-color: var(--border-primary); }
    [data-theme="dark"] .border-slate-100 { border-color: var(--border-primary); }
    [data-theme="dark"] .border-white { border-color: var(--border-primary); }
    
    [data-theme="dark"] .text-primary-600 { color: var(--accent-primary); }
    [data-theme="dark"] .text-primary-700 { color: var(--accent-primary); }
    [data-theme="dark"] .text-emerald-600 { color: var(--accent-success); }
    [data-theme="dark"] .text-amber-300 { color: var(--accent-warning); }
    
    [data-theme="dark"] .bg-primary-50 { background-color: var(--bg-tertiary); }
    [data-theme="dark"] .bg-primary-600 { background-color: var(--accent-primary); }
    [data-theme="dark"] .bg-primary-700 { background-color: var(--accent-primary); }
    
    /* Policy sections dark mode fixes */
    [data-theme="dark"] .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.1); }
    [data-theme="dark"] .bg-amber-50 { background-color: rgba(245, 158, 11, 0.1); }
    [data-theme="dark"] .bg-red-50 { background-color: rgba(239, 68, 68, 0.1); }
    [data-theme="dark"] .bg-blue-50 { background-color: rgba(14, 165, 233, 0.1); }
    [data-theme="dark"] .bg-violet-50 { background-color: rgba(139, 92, 246, 0.1); }
    
    [data-theme="dark"] .border-emerald-200 { border-color: rgba(16, 185, 129, 0.3); }
    [data-theme="dark"] .border-amber-200 { border-color: rgba(245, 158, 11, 0.3); }
    [data-theme="dark"] .border-red-200 { border-color: rgba(239, 68, 68, 0.3); }
    [data-theme="dark"] .border-blue-200 { border-color: rgba(14, 165, 233, 0.3); }
    [data-theme="dark"] .border-violet-200 { border-color: rgba(139, 92, 246, 0.3); }
    
    [data-theme="dark"] .text-emerald-600 { color: #10b981; }
    [data-theme="dark"] .text-amber-600 { color: #f59e0b; }
    [data-theme="dark"] .text-red-600 { color: #ef4444; }
    [data-theme="dark"] .text-blue-600 { color: #0ea5e9; }
    [data-theme="dark"] .text-violet-600 { color: #8b5cf6; }
    
    [data-theme="dark"] .text-emerald-700 { color: #10b981; }
    [data-theme="dark"] .text-amber-700 { color: #f59e0b; }
    [data-theme="dark"] .text-violet-700 { color: #8b5cf6; }
    
    [data-theme="dark"] .bg-emerald-100 { background-color: rgba(16, 185, 129, 0.2); }
    [data-theme="dark"] .bg-amber-100 { background-color: rgba(245, 158, 11, 0.2); }
    [data-theme="dark"] .bg-violet-100 { background-color: rgba(139, 92, 246, 0.2); }
    
    /* Dark mode for specific components */
    [data-theme="dark"] .glass { 
      background: rgba(30, 41, 59, 0.8);
      border: 1px solid var(--border-primary);
    }

    /* Passenger type cards — unselected in register form */
    [data-theme="dark"] .ptype-card:not(:has(input:checked)) {
      background-color: var(--bg-tertiary) !important;
      border-color: var(--border-primary) !important;
    }
    [data-theme="dark"] .ptype-card:not(:has(input:checked)) .ptype-title {
      color: var(--text-secondary) !important;
    }
    [data-theme="dark"] .ptype-card:not(:has(input:checked)) .ptype-subtitle {
      color: var(--text-tertiary) !important;
    }
    [data-theme="dark"] .ptype-card:not(:has(input:checked)) .ptype-subtitle.text-emerald-600 {
      color: var(--accent-success) !important;
    }
    [data-theme="dark"] .ptype-card:not(:has(input:checked)) .ptype-check-off {
      background-color: var(--bg-secondary) !important;
      border-color: var(--border-secondary) !important;
    }

    /* Dark Mode Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: var(--bg-secondary); }
    ::-webkit-scrollbar-thumb { background: var(--border-secondary); border-radius: 99px; }

    /* Dark Mode Glass Effect */
    .glass { 
      background: var(--bg-primary);
      backdrop-filter: blur(14px); 
      -webkit-backdrop-filter: blur(14px); 
      border: 1px solid var(--border-primary);
    }

    /* Modal Backdrop */
    .modal-bg { background: rgba(15,23,42,.75); backdrop-filter: blur(3px); }

    /* Toast Animations */
    @keyframes toastIn  { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes toastOut { from { opacity: 1; } to { opacity: 0; } }
    .toast-enter { animation: toastIn .3s ease forwards; }
    .toast-leave { animation: toastOut .25s ease forwards; }

    /* Line Clamp Utility */
    .clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    /* Theme Transition - Applied only after page load to prevent initial paint transitions */
    .theme-loaded * {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
  </style>

  @stack('head')
</head>
<body class="bg-primary text-primary antialiased">

  {{-- NAV --}}
  @include('components.navbar')

  {{-- FLASH --}}
  @include('components.flash')

  {{-- MAIN --}}
  <main class="animate-fade-in">
    @yield('content')
  </main>

  {{-- FOOTER --}}
  @include('components.footer')

  {{-- BACK TO TOP --}}
  @include('components.back-to-top')

  {{-- AUTH MODAL --}}
  @include('auth.modal')

  {{-- GLOBAL JS --}}
  <script>
    // ── Theme System ─────────────────────────────────────────────────
    // Theme Management
    function getStoredTheme() {
      const stored = localStorage.getItem('theme');
      if (stored) {
        return stored;
      }
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function setStoredTheme(theme) {
      localStorage.setItem('theme', theme);
    }

    function getTheme() {
      return document.documentElement.getAttribute('data-theme') || 'light';
    }

    function setTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      setStoredTheme(theme);
      updateThemeToggle(theme);
      updateThemeTooltip(theme);
    }

    function toggleTheme() {
      const currentTheme = getTheme();
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      setTheme(newTheme);
    }

    function updateThemeToggle(theme) {
      // Update navbar theme toggle
      const navIcon = document.getElementById('nav-theme-icon');
      const navText = document.getElementById('nav-theme-text');
      
      if (navIcon && navText) {
        if (theme === 'dark') {
          navIcon.innerHTML = '<i data-lucide="sun" style="width:16px;height:16px;color:#f59e0b"></i>';
          navText.textContent = 'Switch to Light Mode';
        } else {
          navIcon.innerHTML = '<i data-lucide="moon" style="width:16px;height:16px;color:#64748b"></i>';
          navText.textContent = 'Switch to Dark Mode';
        }
        // Reinitialize Lucide icons
        lucide.createIcons();
      }
    }

    function updateThemeTooltip(theme) {
      // No longer needed since we removed the floating button
    }

    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', function() {
      const theme = getStoredTheme();
      setTheme(theme);
      
      // Enable transitions only after initial paint
      setTimeout(() => document.documentElement.classList.add('theme-loaded'), 50);
      
      // Listen for system theme changes
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
          setTheme(e.matches ? 'dark' : 'light');
        }
      });
    });

    // ── icons ─────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
      lucide.createIcons();
    });

    // ── csrf helper ───────────────────────────────────────────
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── toast ─────────────────────────────────────────────────
    function toast(msg, type = 'success') {
      const map = {
        success: ['bg-emerald-500', 'check-circle'],
        error:   ['bg-red-500',     'x-circle'],
        warning: ['bg-amber-500',   'alert-triangle'],
        info:    ['bg-blue-500',    'info'],
      };
      const [bg, icon] = map[type] ?? map.info;
      const el = Object.assign(document.createElement('div'), {
        className: `toast-enter fixed top-4 right-4 z-[9999] flex items-center gap-3 ${bg} text-white text-sm font-medium px-4 py-3 rounded-2xl shadow-xl max-w-xs`,
        innerHTML: `<i data-lucide="${icon}" style="width:16px;height:16px;flex-shrink:0"></i><span>${msg}</span>`,
      });
      document.body.appendChild(el);
      lucide.createIcons();
      setTimeout(() => { el.classList.replace('toast-enter','toast-leave'); setTimeout(()=>el.remove(), 280); }, 3400);
    }

    // ── modal helpers ─────────────────────────────────────────
    function openModal(id)  { const m=document.getElementById(id); m?.classList.remove('hidden'); m?.classList.add('flex');   document.body.style.overflow='hidden'; }
    function closeModal(id) { const m=document.getElementById(id); m?.classList.add('hidden');    m?.classList.remove('flex'); document.body.style.overflow=''; }

    // ── auth tabs ─────────────────────────────────────────────
    function authTab(name) {
      ['login','register','forgot'].forEach(t => {
        document.getElementById(`at-${t}`)?.classList.add('hidden');
        const btn = document.querySelector(`[data-tab="${t}"]`);
        if (btn) { btn.classList.remove('border-primary-600','text-primary-700'); btn.classList.add('border-transparent','text-slate-500'); }
      });
      document.getElementById(`at-${name}`)?.classList.remove('hidden');
      const active = document.querySelector(`[data-tab="${name}"]`);
      if (active) { active.classList.add('border-primary-600','text-primary-700'); active.classList.remove('border-transparent','text-slate-500'); }
    }

    // ── require auth guard ────────────────────────────────────
    function requireAuth(url) {
      @auth 
        window.location.href = url;
      @else 
        window.intendedUrl = url;
        openModal('auth-modal'); 
        authTab('login');
      @endauth
    }

    // ── toggle password visibility ────────────────────────────
    function togglePwd(id) {
      const inp = document.getElementById(id);
      inp.type = inp.type === 'password' ? 'text' : 'password';
    }

    // ── mobile nav ────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();

      // close dropdown on outside click
      document.addEventListener('click', e => {
        const dd = document.getElementById('user-dd');
        if (dd && !dd.closest('[data-dd]')?.contains(e.target)) dd.classList.add('hidden');
      });

      // mobile toggle
      document.getElementById('mob-btn')?.addEventListener('click', () => {
        document.getElementById('mob-menu')?.classList.toggle('hidden');
      });
    });
    // ── check open_login ──────────────────────────────────────
    @if(request()->has('open_login'))
      document.addEventListener('DOMContentLoaded', () => {
        openModal('auth-modal');
        authTab('login');
      });
    @endif
  </script>

  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
  ></script>

  @stack('scripts')
</body>
</html>