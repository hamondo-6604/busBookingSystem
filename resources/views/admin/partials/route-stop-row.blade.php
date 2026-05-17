@php
    $selectedStopId = $stop?->id ?? null;
    $pivot = $stop?->pivot ?? null;
@endphp
<div class="route-stop-row grid grid-cols-1 md:grid-cols-12 gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 items-end">
    <div class="md:col-span-4">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Stop</label>
        <select name="stops[{{ $index }}][stop_id]" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
            <option value="">— Select —</option>
            @foreach($availableStops as $available)
                <option value="{{ $available->id }}" {{ (string) $selectedStopId === (string) $available->id ? 'selected' : '' }}>
                    {{ $available->name }} ({{ $available->type_label }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-1">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Order</label>
        <input type="number" name="stops[{{ $index }}][stop_order]" value="{{ $pivot?->stop_order ?? ($index + 1) }}" min="1" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Minutes</label>
        <input type="number" name="stops[{{ $index }}][minutes_from_origin]" value="{{ $pivot?->minutes_from_origin }}" min="0" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Fare (₱)</label>
        <input type="number" step="0.01" name="stops[{{ $index }}][fare_from_origin]" value="{{ $pivot?->fare_from_origin }}" min="0" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
    </div>
    <div class="md:col-span-2 flex gap-3 pb-2">
        <label class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300">
            <input type="hidden" name="stops[{{ $index }}][allows_boarding]" value="0">
            <input type="checkbox" name="stops[{{ $index }}][allows_boarding]" value="1" {{ ($pivot?->allows_boarding ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
            Board
        </label>
        <label class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300">
            <input type="hidden" name="stops[{{ $index }}][allows_alighting]" value="0">
            <input type="checkbox" name="stops[{{ $index }}][allows_alighting]" value="1" {{ ($pivot?->allows_alighting ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
            Alight
        </label>
    </div>
    <div class="md:col-span-1 flex justify-end pb-2">
        <button type="button" onclick="this.closest('.route-stop-row').remove()" class="text-red-500 hover:text-red-600 text-sm" title="Remove row"><i class="fa-solid fa-trash-can"></i></button>
    </div>
</div>
