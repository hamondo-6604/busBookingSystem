{{-- ══════════════════════════════════════════════════════════
     CREATE ACCOUNT
══════════════════════════════════════════════════════════ --}}
<div id="at-register" class="hidden px-6 py-6 overflow-y-auto" style="max-height:82vh">
  <p class="text-xs text-slate-500 mb-5">Create your free account to book and manage trips instantly.</p>

  <form id="f-register" class="space-y-4" onsubmit="doRegister(event)">
    @csrf

    {{-- Username --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username</label>
      <div class="relative">
        <i data-lucide="user"
           style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
        <input type="text" name="name" id="input-reg-name" autocomplete="name" placeholder="Juan dela Cruz"
               class="reg-input w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                      focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
      </div>
      <p class="error-msg text-xs text-red-500 mt-1 hidden" id="err-reg-name"></p>
    </div>

    {{-- Email --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
      <div class="relative">
        <i data-lucide="mail"
           style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
        <input type="email" name="email" id="input-reg-email" autocomplete="email" placeholder="you@email.com"
               class="reg-input w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                      focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
      </div>
      <p class="error-msg text-xs text-red-500 mt-1 hidden" id="err-reg-email"></p>
    </div>

    {{-- Phone --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1.5">
        Phone Number
        <span class="font-normal text-slate-400">(optional)</span>
      </label>
      <div class="relative">
        <i data-lucide="phone"
           style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
        <input type="tel" name="phone" id="input-reg-phone" autocomplete="tel" placeholder="09XXXXXXXXX"
               maxlength="11"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               class="reg-input w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                      focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
      </div>
      <p class="error-msg text-xs text-red-500 mt-1 hidden" id="err-reg-phone"></p>
    </div>

    {{-- ── PASSENGER TYPE ──────────────────────────────────────── --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
        Passenger Type
      </label>
      <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-2.5">
        Select the category that applies to you for automatic fare discounts.
      </p>

      <div class="grid grid-cols-2 gap-2">
        @foreach($discountTypes as $dt)
          @php
            $cfg = [
              'regular'        => ['user',           'border-slate-200 bg-slate-50',       'border-primary-500 bg-primary-50 ring-1 ring-primary-400'],
              'senior_citizen' => ['user-check',     'border-amber-200 bg-amber-50',    'border-amber-500  bg-amber-50 ring-1 ring-amber-400'],
              'pwd'            => ['accessibility',  'border-blue-200  bg-blue-50',     'border-blue-500   bg-blue-50 ring-1 ring-blue-400'],
              'student'        => ['graduation-cap', 'border-violet-200 bg-violet-50',  'border-violet-500 bg-violet-50 ring-1 ring-violet-400'],
            ];
            [$ico, $baseCls, $selCls] = $cfg[$dt->name] ?? ['user','border-slate-200 bg-slate-50','border-primary-500 bg-primary-50 ring-1 ring-primary-400'];
            $isDefault = $dt->name === 'regular';
          @endphp

          <label class="ptype-card relative flex items-start gap-2.5 p-3 border-2 rounded-xl cursor-pointer
                        transition-all duration-150 select-none
                        {{ $isDefault ? $selCls : $baseCls }}"
                 data-base="{{ $baseCls }}"
                 data-sel="{{ $selCls }}"
                 data-name="{{ $dt->name }}"
                 data-pct="{{ $dt->percentage }}"
                 data-desc="{{ $dt->description }}"
                 onclick="pickType(this)">

            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0
                        @if($dt->name==='regular') bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400
                        @elseif($dt->name==='senior_citizen') bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400
                        @elseif($dt->name==='pwd') bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400
                        @else bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400 @endif">
              <i data-lucide="{{ $ico }}" style="width:15px;height:15px"></i>
            </div>

            <div class="min-w-0 flex-1">
              <div class="ptype-title text-xs font-bold text-slate-900 leading-tight">
                {{ $dt->display_name }}
              </div>
              <div class="ptype-subtitle text-[10px] mt-0.5 font-semibold
                          {{ $dt->percentage > 0 ? 'text-emerald-600' : 'text-slate-600' }}">
                {{ $dt->percentage > 0
                    ? number_format($dt->percentage * 100, 0).'% discount'
                    : 'Standard fare' }}
              </div>
            </div>

            <div class="ptype-check absolute top-2 right-2 w-4 h-4 rounded-full border-2 flex items-center justify-center
                        {{ $isDefault ? 'border-primary-500 bg-primary-500 ptype-check-on' : 'border-slate-300 bg-white ptype-check-off' }}">
              <i data-lucide="check"
                 style="width:9px;height:9px;color:white;{{ $isDefault ? '' : 'display:none' }}"
                 class="check-icon"></i>
            </div>

            <input type="radio" name="discount_type_id" value="{{ $dt->id }}"
                   {{ $isDefault ? 'checked' : '' }} class="sr-only">
          </label>
        @endforeach
      </div>

      <div id="ptype-notice"
           class="mt-2.5 hidden items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
        <i data-lucide="info" style="width:13px;height:13px;margin-top:1px;flex-shrink:0;color:#d97706"></i>
        <p id="ptype-notice-text" class="text-[11px] text-amber-700 leading-relaxed"></p>
      </div>
    </div>

    {{-- Password --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
      <div class="relative">
        <i data-lucide="lock"
           style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
        <input type="password" id="input-reg-password" name="password" autocomplete="new-password"
               placeholder="Min. 6 characters"
               class="reg-input w-full pl-9 pr-10 py-2.5 text-sm border border-slate-200 rounded-xl
                      focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
        <button type="button" onclick="togglePwd('input-reg-password')"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
          <i data-lucide="eye" style="width:14px;height:14px"></i>
        </button>
      </div>
      <p class="error-msg text-xs text-red-500 mt-1 hidden" id="err-reg-password"></p>
    </div>

    {{-- Confirm password --}}
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1.5">Confirm Password</label>
      <div class="relative">
        <i data-lucide="lock"
           style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
        <input type="password" id="input-reg-password_confirmation" name="password_confirmation" autocomplete="new-password"
               placeholder="••••••••"
               class="reg-input w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl
                      focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
      </div>
      <p class="error-msg text-xs text-red-500 mt-1 hidden" id="err-reg-password_confirmation"></p>
    </div>

    {{-- ID disclaimer --}}
    <div class="flex items-start gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5">
      <i data-lucide="shield-check"
         style="width:13px;height:13px;margin-top:1px;flex-shrink:0;color:#ea580c"></i>
      <p class="text-[11px] text-slate-500 leading-relaxed">
        Discounted passenger types (Senior Citizen, PWD, Student) require a valid
        government-issued ID presented at the terminal gate. Your account will be
        flagged for verification at boarding.
      </p>
    </div>

    <button type="submit" id="btn-register"
            class="w-full flex items-center justify-center gap-2 py-2.5 bg-primary-600 hover:bg-primary-700
                   text-white text-sm font-semibold rounded-xl transition-colors">
      <i data-lucide="user-plus" style="width:14px;height:14px"></i>
      Create Account
    </button>
  </form>

  <p class="text-center text-xs text-slate-500 mt-4">
    Already have an account?
    <button onclick="authTab('login')"
            class="text-primary-600 font-semibold hover:text-primary-700">
      Sign in
    </button>
  </p>
</div>