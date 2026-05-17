@php $selected = $selected ?? 'regular'; @endphp
<label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Service Type <span class="text-red-500">*</span></label>
<select name="service_type" required class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
    <option value="non_stop" {{ $selected === 'non_stop' ? 'selected' : '' }}>Non-Stop — 0 intermediate stops</option>
    <option value="express" {{ $selected === 'express' ? 'selected' : '' }}>Express — few major stops</option>
    <option value="regular" {{ $selected === 'regular' ? 'selected' : '' }}>Regular — many barangay/city stops</option>
</select>
<p class="text-xs text-slate-400 mt-1">Same origin & destination can have one route per service type.</p>
