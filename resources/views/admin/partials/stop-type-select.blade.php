@php $selected = $selected ?? 'barangay'; @endphp
<label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Stop Type <span class="text-red-500">*</span></label>
<select name="type" required class="stop-type-select w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
    <option value="barangay" {{ $selected === 'barangay' ? 'selected' : '' }}>Barangay / roadside pickup</option>
    <option value="terminal" {{ $selected === 'terminal' ? 'selected' : '' }}>Terminal</option>
    <option value="pickup" {{ $selected === 'pickup' ? 'selected' : '' }}>Pickup point</option>
    <option value="dropoff" {{ $selected === 'dropoff' ? 'selected' : '' }}>Drop-off point</option>
    <option value="waypoint" {{ $selected === 'waypoint' ? 'selected' : '' }}>Waypoint only</option>
</select>
