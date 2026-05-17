@extends('layouts.admin')

@section('title', 'Route Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.routes.index') }}" class="text-sm font-medium text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1 mb-2">
        <i class="fa-solid fa-arrow-left"></i> Back to Routes
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex flex-wrap items-center gap-3">
                {{ $route->route_name }}
                @include('admin.partials.service-type-badge', ['serviceType' => $route->service_type])
                @if($route->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $route->description ?? 'No description provided.' }}</p>
        </div>
        <a href="{{ route('admin.stops.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Manage stop library
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-map text-primary-500"></i> Route Journey & Stops
                </h2>
                <span class="text-sm text-slate-500 font-semibold">{{ number_format($route->distance_km, 1) }} km</span>
            </div>
            <div class="p-6">
                <div class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-4 space-y-8 pb-4">
                    <div class="relative pl-8">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-emerald-50 dark:ring-emerald-900/20"></div>
                        <p class="text-xs text-slate-500 uppercase font-semibold mb-0.5">Origin</p>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $route->originTerminal->name ?? $route->originCity->name ?? 'Unknown' }}</h3>
                        <p class="text-sm text-slate-500 mt-1"><i class="fa-solid fa-city w-4"></i> {{ $route->originCity->name ?? '' }}</p>
                    </div>
                    @forelse($route->stops as $stop)
                        <div class="relative pl-8">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-slate-300 border-2 border-white dark:border-slate-800 dark:bg-slate-600"></div>
                            <p class="text-xs text-slate-500 uppercase font-semibold mb-0.5">Stop #{{ $stop->pivot->stop_order }} · {{ $stop->type_label }}</p>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white">{{ $stop->name }}</h3>
                            <p class="text-sm text-slate-500 mt-1"><i class="fa-solid fa-location-dot w-4"></i> {{ $stop->location_label }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                                @if($stop->pivot->minutes_from_origin !== null)
                                    <span class="bg-slate-50 dark:bg-slate-700/50 px-2.5 py-1 rounded-lg">+{{ $stop->pivot->minutes_from_origin }} mins</span>
                                @endif
                                @if($stop->pivot->fare_from_origin !== null)
                                    <span class="bg-slate-50 dark:bg-slate-700/50 px-2.5 py-1 rounded-lg">₱{{ number_format($stop->pivot->fare_from_origin, 2) }}</span>
                                @endif
                                @if($stop->pivot->allows_boarding)<span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded-lg">Boarding</span>@endif
                                @if($stop->pivot->allows_alighting)<span class="bg-amber-50 text-amber-600 px-2 py-1 rounded-lg">Alighting</span>@endif
                            </div>
                        </div>
                    @empty
                        <div class="relative pl-8 pb-4">
                            <p class="text-sm text-slate-400 italic">No intermediate stops — direct service (Non-Stop pattern).</p>
                        </div>
                    @endforelse
                    <div class="relative pl-8">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-red-500 ring-4 ring-red-50 dark:ring-red-900/20"></div>
                        <p class="text-xs text-slate-500 uppercase font-semibold mb-0.5">Destination</p>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $route->destinationTerminal->name ?? $route->destinationCity->name ?? 'Unknown' }}</h3>
                        <p class="text-sm text-slate-500 mt-1"><i class="fa-solid fa-city w-4"></i> {{ $route->destinationCity->name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white"><i class="fa-solid fa-list-ol text-primary-500 mr-2"></i>Assign Intermediate Stops</h2>
                <p class="text-sm text-slate-500 mt-1">Pick stops from your library and set order, time, and fare for this route.</p>
            </div>
            <form action="{{ route('admin.routes.stops.sync', $route) }}" method="POST" class="p-5" id="route-stops-form">
                @csrf @method('PUT')
                <div id="route-stops-rows" class="space-y-3">
                    @foreach($route->stops as $index => $stop)
                        @include('admin.partials.route-stop-row', ['index' => $index, 'stop' => $stop, 'availableStops' => $availableStops])
                    @endforeach
                </div>
                <button type="button" onclick="addRouteStopRow()" class="mt-4 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    <i class="fa-solid fa-plus mr-1"></i> Add stop row
                </button>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">Save route stops</button>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 space-y-4">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Route metrics</h2>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold mb-1">Service type</p>
                @include('admin.partials.service-type-badge', ['serviceType' => $route->service_type])
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold mb-1">Intermediate stops</p>
                <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $route->stops->count() }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold mb-1">Distance</p>
                <p class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($route->distance_km, 1) }} km</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase font-semibold mb-1">Duration</p>
                @php $hours = floor($route->estimated_duration_minutes / 60); $mins = $route->estimated_duration_minutes % 60; @endphp
                <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $hours > 0 ? $hours.'h ' : '' }}{{ $mins }}m</p>
            </div>
        </div>
    </div>
</div>

<template id="route-stop-row-template">
    @include('admin.partials.route-stop-row', ['index' => '__INDEX__', 'stop' => null, 'availableStops' => $availableStops])
</template>

<script>
let routeStopIndex = {{ $route->stops->count() }};
function addRouteStopRow() {
    const tpl = document.getElementById('route-stop-row-template').innerHTML.replace(/__INDEX__/g, routeStopIndex++);
    document.getElementById('route-stops-rows').insertAdjacentHTML('beforeend', tpl);
}
</script>
@endsection
