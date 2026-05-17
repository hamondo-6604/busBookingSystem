@extends('layouts.admin')

@section('title', $route->exists ? 'Edit Route' : 'Add Route')

@section('content')
<div class="mb-6 flex items-center gap-4">
    <a href="{{ route('admin.routes.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-primary-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $route->exists ? 'Edit Route' : 'Add New Route' }}</h1>
    </div>
</div>

<div class="max-w-4xl bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
    <form action="{{ $route->exists ? route('admin.routes.update', $route) : route('admin.routes.store') }}" method="POST">
        @csrf
        @if($route->exists)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Route Name</label>
                    <input type="text" name="route_name" value="{{ old('route_name', $route->route_name) }}" placeholder="Auto-generated if blank"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    @error('route_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    @include('admin.partials.route-service-type-select', ['selected' => old('service_type', $route->service_type ?? 'regular')])
                    @error('service_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Origin -->
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-primary-500"></i> Origin
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">City <span class="text-red-500">*</span></label>
                            <select name="origin_city_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">-- Select Origin City --</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('origin_city_id', $route->origin_city_id) == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_city_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Terminal</label>
                            <select name="origin_terminal_id" class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">-- Select Origin Terminal --</option>
                                @foreach($terminals as $terminal)
                                    <option value="{{ $terminal->id }}" {{ old('origin_terminal_id', $route->origin_terminal_id) == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->name }} ({{ $terminal->city->name ?? 'Unknown' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_terminal_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Destination -->
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-flag-checkered text-emerald-500"></i> Destination
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">City <span class="text-red-500">*</span></label>
                            <select name="destination_city_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">-- Select Destination City --</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('destination_city_id', $route->destination_city_id) == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_city_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Terminal</label>
                            <select name="destination_terminal_id" class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">-- Select Destination Terminal --</option>
                                @foreach($terminals as $terminal)
                                    <option value="{{ $terminal->id }}" {{ old('destination_terminal_id', $route->destination_terminal_id) == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->name }} ({{ $terminal->city->name ?? 'Unknown' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_terminal_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Distance (km)</label>
                    <input type="number" step="0.1" name="distance_km" value="{{ old('distance_km', $route->distance_km) }}" min="0"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    @error('distance_km')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Est. Duration (minutes)</label>
                    <input type="number" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', $route->estimated_duration_minutes) }}" min="1"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    @error('estimated_duration_minutes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="active" {{ old('status', $route->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $route->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Description / Notes</label>
                <textarea name="description" rows="3"
                          class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">{{ old('description', $route->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
            <a href="{{ route('admin.routes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 text-white font-semibold hover:bg-primary-700 transition-colors">
                {{ $route->exists ? 'Save Changes' : 'Create Route' }}
            </button>
        </div>
    </form>
</div>
@endsection
