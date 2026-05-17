@php $stop = $stop ?? null; @endphp
<div class="space-y-4 stop-form-fields">
    @if($stop)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Stop Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ $stop->name }}" required placeholder="e.g. Ulas, Toril, Digos"
                   class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Code</label>
            <input type="text" name="code" value="{{ $stop->code }}" placeholder="e.g. DVO-ULAS" maxlength="20"
                   class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
        </div>
    </div>
    @else
    <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Code</label>
        <input type="text" name="code" placeholder="e.g. DVO-ULAS" maxlength="20"
               class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
        <p class="text-xs text-slate-400 mt-1">Stop name can be added later when editing.</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            @include('admin.partials.stop-type-select', ['selected' => $stop?->type ?? 'barangay'])
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">City / Municipality</label>
            <select name="city_id" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                <option value="">— Not set —</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ (string) $stop?->city_id === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="stop-terminal-field {{ ($stop?->type ?? 'barangay') === 'terminal' ? '' : 'hidden' }}">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Linked Terminal</label>
        <select name="terminal_id" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
            <option value="">— Select terminal —</option>
            @foreach($terminals as $terminal)
                <option value="{{ $terminal->id }}" {{ (string) $stop?->terminal_id === (string) $terminal->id ? 'selected' : '' }}>
                    {{ $terminal->name }} ({{ $terminal->city?->name }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Address / landmark</label>
        <input type="text" name="address" value="{{ $stop?->address }}" placeholder="Optional roadside landmark"
               class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" required class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
            <option value="active" {{ ($stop?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ ($stop?->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>
