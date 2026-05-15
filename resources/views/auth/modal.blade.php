{{-- ────────────────────────────────────────────────────────────────
     AUTH MODAL  (Sign In · Create Account · Forgot Password)
     Included by layouts/app.blade.php on every page.
     $discountTypes is shared globally via AppServiceProvider.
──────────────────────────────────────────────────────────────── --}}
@php
  // Fallback: query directly if not already shared via View::share()
  $discountTypes = $discountTypes ?? \App\Models\DiscountType::where('is_active', true)->orderBy('name')->get();
@endphp

<div id="auth-modal"
     class="hidden fixed inset-0 z-[9999] items-center justify-center modal-bg"
     onclick="if(event.target===this)closeModal('auth-modal')">

  <div class="bg-white w-full max-w-md mx-4 rounded-3xl shadow-2xl overflow-hidden animate-slide-up">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 pt-6 pb-0">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 bg-primary-600 rounded-lg flex items-center justify-center">
          <i data-lucide="bus" style="width:13px;height:13px;color:#fff"></i>
        </div>
        <span class="font-extrabold text-slate-900 text-sm">Mindanao Express</span>
      </div>
      <button onclick="closeModal('auth-modal')"
              class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 transition-colors">
        <i data-lucide="x" style="width:17px;height:17px"></i>
      </button>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-slate-200 px-6 mt-4">
      <button data-tab="login" onclick="authTab('login')"
              class="pb-3 px-1 mr-5 text-sm font-semibold border-b-2 border-primary-600 text-primary-700 transition-all">
        Sign In
      </button>
      <button data-tab="register" onclick="authTab('register')"
              class="pb-3 px-1 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
        Create Account
      </button>
    </div>

    {{-- INJECTED SECTIONS --}}
    @include('auth.login')
    @include('auth.register')
    @include('auth.forgot-password')

  </div>
</div>

{{-- ── SCRIPTS ──────────────────────────────────────────────────────── --}}
<script>
const _spin = `<svg class="animate-spin-slow w-4 h-4" viewBox="0 0 24 24" fill="none">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
</svg>`;

// ── Passenger type picker ────────────────────────────────────────
function pickType(card) {
  document.querySelectorAll('.ptype-card').forEach(c => {
    c.className = c.className.replace(c.dataset.sel, '').trim();
    c.dataset.base.split(' ').forEach(cls => { if (cls) c.classList.add(cls); });
    c.dataset.sel.split(' ').forEach(cls => { if (cls) c.classList.remove(cls); });

    const chk  = c.querySelector('.ptype-check');
    const icon = c.querySelector('.check-icon');
    if (chk)  { chk.classList.remove('bg-primary-500','border-primary-500','bg-amber-500','border-amber-500','bg-blue-500','border-blue-500','bg-violet-500','border-violet-500','ptype-check-on'); chk.classList.add('border-slate-300','bg-white','ptype-check-off'); }
    if (icon) icon.style.display = 'none';

    c.querySelector('input[type=radio]').checked = false;
  });

  card.dataset.base.split(' ').forEach(cls => { if (cls) card.classList.remove(cls); });
  card.dataset.sel.split(' ').forEach(cls => { if (cls) card.classList.add(cls); });

  const chk  = card.querySelector('.ptype-check');
  const icon = card.querySelector('.check-icon');
  if (chk)  { chk.classList.remove('border-slate-300','bg-white','ptype-check-off'); chk.classList.add('border-primary-500','bg-primary-500','ptype-check-on'); }
  if (icon) icon.style.display = '';

  card.querySelector('input[type=radio]').checked = true;

  const notice = document.getElementById('ptype-notice');
  const noticeT = document.getElementById('ptype-notice-text');
  const pct    = parseFloat(card.dataset.pct ?? 0);
  const desc   = card.dataset.desc ?? '';

  if (pct > 0) {
    noticeT.textContent = desc;
    notice.classList.remove('hidden');
    notice.classList.add('flex');
  } else {
    notice.classList.add('hidden');
    notice.classList.remove('flex');
  }
  lucide.createIcons();
}

// ── Login ────────────────────────────────────────────────────────
async function doLogin(e) {
  e.preventDefault();
  const btn  = document.getElementById('btn-login');
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = _spin + ' Signing in…';

  // Clear previous errors
  document.querySelectorAll('#f-login .error-msg').forEach(el => {
      el.textContent = '';
      el.classList.add('hidden');
  });
  document.querySelectorAll('#f-login .log-input').forEach(el => {
      el.classList.remove('border-red-500', 'focus:ring-red-500');
      el.classList.add('border-slate-200', 'focus:ring-primary-500');
  });

  try {
    const r = await fetch('{{ route("login_post") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: new FormData(document.getElementById('f-login')),
    });
    const j = await r.json();
    if (j.success) {
      toast(j.message, 'success');
      closeModal('auth-modal');
      setTimeout(() => {
        if (window.intendedUrl) {
          location.href = window.intendedUrl;
        } else {
          location.reload();
        }
      }, 400);
    } else {
      if (j.errors) {
        for (const [field, messages] of Object.entries(j.errors)) {
            const input = document.getElementById('input-login-' + field);
            const errP = document.getElementById('err-login-' + field);
            if (input) {
                input.classList.remove('border-slate-200', 'focus:ring-primary-500');
                input.classList.add('border-red-500', 'focus:ring-red-500');
            }
            if (errP) {
                errP.textContent = messages[0];
                errP.classList.remove('hidden');
            }
        }
      } else {
        toast(j.message, 'error');
      }
    }
  } catch { toast('Something went wrong. Please try again.', 'error'); }
  finally  { btn.disabled = false; btn.innerHTML = orig; lucide.createIcons(); }
}

// ── Register ─────────────────────────────────────────────────────
async function doRegister(e) {
  e.preventDefault();
  const btn  = document.getElementById('btn-register');
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = _spin + ' Creating account…';

  // Clear previous errors
  document.querySelectorAll('.error-msg').forEach(el => {
      el.textContent = '';
      el.classList.add('hidden');
  });
  document.querySelectorAll('.reg-input').forEach(el => {
      el.classList.remove('border-red-500', 'focus:ring-red-500');
      el.classList.add('border-slate-200', 'focus:ring-primary-500');
  });

  try {
    const r = await fetch('{{ route("register_post") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: new FormData(document.getElementById('f-register')),
    });
    const j = await r.json();
    if (j.success) {
      toast(j.message, 'success');
      document.getElementById('f-register').reset();
      const first = document.querySelector('.ptype-card');
      if (first) pickType(first);
      document.getElementById('ptype-notice')?.classList.add('hidden');
      authTab('login');
    } else {
      if (j.errors) {
        for (const [field, messages] of Object.entries(j.errors)) {
            const input = document.getElementById('input-reg-' + field);
            const errP = document.getElementById('err-reg-' + field);
            if (input) {
                input.classList.remove('border-slate-200', 'focus:ring-primary-500');
                input.classList.add('border-red-500', 'focus:ring-red-500');
            }
            if (errP) {
                errP.textContent = messages[0];
                errP.classList.remove('hidden');
            }
        }
      } else {
        toast(j.message, 'error');
      }
    }
  } catch { toast('Something went wrong. Please try again.', 'error'); }
  finally  { btn.disabled = false; btn.innerHTML = orig; lucide.createIcons(); }
}

// ── Forgot password ───────────────────────────────────────────────
async function doForgot(e) {
  e.preventDefault();
  try {
    const r = await fetch('{{ route("forgot-password") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: new FormData(document.getElementById('f-forgot')),
    });
    const j = await r.json();
    toast(j.message, j.success ? 'success' : 'error');
    if (j.success) document.getElementById('f-forgot').reset();
  } catch { toast('Something went wrong. Please try again.', 'error'); }
}
</script>