@php
    $terminal = $terminal ?? new \App\Models\Terminal();
@endphp

<div class="space-y-4 p-4 terminal-form-fields">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Name -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Terminal Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $terminal->name) }}" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors"
                   placeholder="e.g. Davao City Overland Transport Terminal">
            @error('name')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Code -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Terminal Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $terminal->code) }}" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors"
                   placeholder="e.g. DCOTT">
            @error('code')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <!-- City -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">City</label>
        <select name="city_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            <option value="">Select a city (optional)</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}" {{ old('city_id', $terminal->city_id) == $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
        @error('city_id')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Address -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address</label>
        <input type="text" name="address" value="{{ old('address', $terminal->address) }}"
               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors"
               placeholder="e.g. Ecoland, Davao City">
        @error('address')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Contact Number -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Number</label>
            <input type="text" name="contact_number" value="{{ old('contact_number', $terminal->contact_number) }}"
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            @error('contact_number')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Email -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $terminal->email) }}"
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            @error('email')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Opening Time -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Opening Time</label>
            <input type="time" name="opening_time" value="{{ old('opening_time', $terminal->opening_time ? \Carbon\Carbon::parse($terminal->opening_time)->format('H:i') : '') }}"
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            @error('opening_time')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Closing Time -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Closing Time</label>
            <input type="time" name="closing_time" value="{{ old('closing_time', $terminal->closing_time ? \Carbon\Carbon::parse($terminal->closing_time)->format('H:i') : '') }}"
                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            @error('closing_time')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <!-- Status -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
            <option value="active" {{ old('status', $terminal->status) == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $terminal->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')<p class="error-text text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
