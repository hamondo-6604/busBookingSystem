@extends('layouts.admin')

@section('title', 'Manage Routes')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Routes</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage the available bus routes and their configurations.</p>
    </div>
    <button onclick="openAdminModal('create-route-modal')" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2 shadow-sm">
        <i class="fa-solid fa-plus"></i> Add Route
    </button>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6">
    <form method="GET" action="{{ route('admin.routes.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Search Routes</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by route name, origin, or destination..." 
                   class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
        </div>
        <div class="w-full sm:w-44">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Service</label>
            <select name="service_type" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm">
                <option value="">All services</option>
                <option value="non_stop" {{ request('service_type') === 'non_stop' ? 'selected' : '' }}>Non-Stop</option>
                <option value="express" {{ request('service_type') === 'express' ? 'selected' : '' }}>Express</option>
                <option value="regular" {{ request('service_type') === 'regular' ? 'selected' : '' }}>Regular</option>
            </select>
        </div>
        <div class="w-full sm:w-48 relative" data-custom-select>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
            <input type="hidden" name="status" value="{{ request('status') }}" class="custom-select-input">
            
            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full px-4 py-2 flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                <span class="custom-select-text">
                    @if(request('status') === 'active') Active
                    @elseif(request('status') === 'inactive') Inactive
                    @else All Statuses
                    @endif
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
            </button>
            
            <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50 overflow-hidden">
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors" data-value="" onclick="selectCustomOption(this)">
                    All Statuses
                </div>
                <div class="px-4 py-2.5 text-sm font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 cursor-pointer transition-colors" data-value="active" onclick="selectCustomOption(this)">
                    Active
                </div>
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors" data-value="inactive" onclick="selectCustomOption(this)">
                    Inactive
                </div>
            </div>
        </div>
        <div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'service_type']))
                <a href="{{ route('admin.routes.index') }}" class="ml-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">Clear</a>
            @endif
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                    <th class="p-4">Route Name / Path</th>
                    <th class="p-4">Service</th>
                    <th class="p-4">Stops</th>
                    <th class="p-4">Terminals</th>
                    <th class="p-4">Distance & Duration</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($routes as $route)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-slate-800 dark:text-slate-200 mb-1">{{ $route->route_name }}</div>
                        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="truncate max-w-[120px]">{{ $route->originCity->name ?? 'Unknown' }}</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-slate-300"></i>
                            <span class="truncate max-w-[120px]">{{ $route->destinationCity->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="p-4">@include('admin.partials.service-type-badge', ['serviceType' => $route->service_type])</td>
                    <td class="p-4"><span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $route->stops_count }}</span><span class="text-xs text-slate-500 block">intermediate</span></td>
                    <td class="p-4">
                        <div class="text-sm font-medium text-slate-800 dark:text-slate-200"><i class="fa-solid fa-location-dot text-emerald-500 w-4"></i> {{ $route->originTerminal->name ?? 'N/A' }}</div>
                        <div class="text-sm text-slate-500 mt-1"><i class="fa-solid fa-flag-checkered text-red-500 w-4"></i> {{ $route->destinationTerminal->name ?? 'N/A' }}</div>
                    </td>
                    <td class="p-4">
                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ number_format($route->distance_km, 1) }} km</div>
                        <div class="text-sm text-slate-500 mt-0.5">
                            @php
                                $hours = floor($route->estimated_duration_minutes / 60);
                                $mins = $route->estimated_duration_minutes % 60;
                            @endphp
                            <i class="fa-regular fa-clock w-4"></i> {{ $hours > 0 ? $hours.'h ' : '' }}{{ $mins }}m
                        </div>
                    </td>
                    <td class="p-4">
                        @if($route->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.routes.show', $route) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            <button onclick="openAdminModal('edit-route-modal-{{ $route->id }}')" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <button onclick="openAdminModal('delete-route-modal-{{ $route->id }}')" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Edit Route Modal -->
                <x-modal id="edit-route-modal-{{ $route->id }}" title="Edit Route" size="lg">
                    <form id="edit-route-form-{{ $route->id }}" action="{{ route('admin.routes.update', $route) }}" method="POST" onsubmit="handleAjaxForm(this, 'edit-route-modal-{{ $route->id }}', () => window.location.reload(), event)">
                        @csrf
                        @method('PUT')
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Route Name</label>
                                    <input type="text" name="route_name" value="{{ $route->route_name }}" placeholder="Auto if blank" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                                </div>
                                <div>
                                    @include('admin.partials.route-service-type-select', ['selected' => $route->service_type ?? 'regular'])
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <h3 class="text-xs font-bold text-primary-500 uppercase tracking-wider"><i class="fa-solid fa-location-dot"></i> Origin</h3>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">City <span class="text-red-500">*</span></label>
                                        <select name="origin_city_id" required class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                                            <option value="">-- Select Origin City --</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}" {{ $route->origin_city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terminal</label>
                                        <select name="origin_terminal_id" class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                                            <option value="">-- Select Terminal --</option>
                                            @foreach($terminals as $terminal)
                                                <option value="{{ $terminal->id }}" {{ $route->origin_terminal_id == $terminal->id ? 'selected' : '' }}>{{ $terminal->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <h3 class="text-xs font-bold text-emerald-500 uppercase tracking-wider"><i class="fa-solid fa-flag-checkered"></i> Destination</h3>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">City <span class="text-red-500">*</span></label>
                                        <select name="destination_city_id" required class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                                            <option value="">-- Select Destination City --</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}" {{ $route->destination_city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terminal</label>
                                        <select name="destination_terminal_id" class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                                            <option value="">-- Select Terminal --</option>
                                            @foreach($terminals as $terminal)
                                                <option value="{{ $terminal->id }}" {{ $route->destination_terminal_id == $terminal->id ? 'selected' : '' }}>{{ $terminal->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Distance (km)</label>
                                    <input type="number" step="0.1" name="distance_km" value="{{ $route->distance_km }}" min="0" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Est. Duration (mins)</label>
                                    <input type="number" name="estimated_duration_minutes" value="{{ $route->estimated_duration_minutes }}" min="1" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status <span class="text-red-500">*</span></label>
                                    <div class="relative" data-custom-select>
                                        <input type="hidden" name="status" value="{{ $route->status }}" class="custom-select-input" required>
                                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                                            <span class="custom-select-text">{{ ucfirst($route->status) }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                                        </button>
                                        <div class="custom-select-menu hidden mt-2 bg-slate-100 dark:bg-slate-700/50 rounded-xl p-1 flex gap-1 overflow-hidden">
                                            <div class="flex-1 text-center px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 rounded-lg cursor-pointer transition-colors shadow-sm" data-value="active" onclick="selectCustomOption(this)">Active</div>
                                            <div class="flex-1 text-center px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white dark:hover:bg-slate-800 dark:text-slate-300 rounded-lg cursor-pointer transition-colors" data-value="inactive" onclick="selectCustomOption(this)">Inactive</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">{{ $route->description }}</textarea>
                            </div>
                        </div>
                        <x-slot:footer>
                            <button type="button" onclick="closeAdminModal('edit-route-modal-{{ $route->id }}')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors font-semibold text-sm">Cancel</button>
                            <button type="submit" form="edit-route-form-{{ $route->id }}" class="px-4 py-2 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition-colors">Save Changes</button>
                        </x-slot:footer>
                    </form>
                </x-modal>

                <!-- Delete Route Modal -->
                <x-modal id="delete-route-modal-{{ $route->id }}" title="Delete Route" size="sm">
                    <form id="delete-route-form-{{ $route->id }}" action="{{ route('admin.routes.destroy', $route) }}" method="POST" onsubmit="handleAjaxForm(this, 'delete-route-modal-{{ $route->id }}', () => window.location.reload(), event)">
                        @csrf
                        @method('DELETE')
                        <div class="text-center py-4">
                            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 flex items-center justify-center text-3xl mx-auto mb-4">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Are you sure?</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Do you really want to delete route <strong>{{ $route->route_name }}</strong>? This action cannot be undone if there are no existing trips.</p>
                        </div>
                        <x-slot:footer>
                            <button type="button" onclick="closeAdminModal('delete-route-modal-{{ $route->id }}')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors font-semibold text-sm">Cancel</button>
                            <button type="submit" form="delete-route-form-{{ $route->id }}" class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-colors">Yes, Delete</button>
                        </x-slot:footer>
                    </form>
                </x-modal>

                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <div class="text-4xl mb-2"><i class="fa-solid fa-map-location-dot text-slate-300 dark:text-slate-600"></i></div>
                        <p>No routes found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($routes->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">
        {{ $routes->links() }}
    </div>
    @endif
</div>

<!-- Create Route Modal -->
<x-modal id="create-route-modal" title="Add New Route" size="lg">
    <form id="create-route-form" action="{{ route('admin.routes.store') }}" method="POST" onsubmit="handleAjaxForm(this, 'create-route-modal', () => window.location.reload(), event)">
        @csrf
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Route Name</label>
                    <input type="text" name="route_name" placeholder="Auto if blank" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                </div>
                <div>
                    @include('admin.partials.route-service-type-select', ['selected' => 'regular'])
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-primary-500 uppercase tracking-wider"><i class="fa-solid fa-location-dot"></i> Origin</h3>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">City <span class="text-red-500">*</span></label>
                        <select name="origin_city_id" required class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                            <option value="">-- Select Origin City --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terminal</label>
                        <select name="origin_terminal_id" class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                            <option value="">-- Select Terminal --</option>
                            @foreach($terminals as $terminal)
                                <option value="{{ $terminal->id }}">{{ $terminal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-emerald-500 uppercase tracking-wider"><i class="fa-solid fa-flag-checkered"></i> Destination</h3>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">City <span class="text-red-500">*</span></label>
                        <select name="destination_city_id" required class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                            <option value="">-- Select Destination City --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terminal</label>
                        <select name="destination_terminal_id" class="w-full px-4 py-2 pr-8 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                            <option value="">-- Select Terminal --</option>
                            @foreach($terminals as $terminal)
                                <option value="{{ $terminal->id }}">{{ $terminal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Distance (km)</label>
                    <input type="number" step="0.1" name="distance_km" min="0" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Est. Duration (mins)</label>
                    <input type="number" name="estimated_duration_minutes" min="1" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status <span class="text-red-500">*</span></label>
                    <div class="relative" data-custom-select>
                        <input type="hidden" name="status" value="active" class="custom-select-input" required>
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                            <span class="custom-select-text">Active</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div class="custom-select-menu hidden mt-2 bg-slate-100 dark:bg-slate-700/50 rounded-xl p-1 flex gap-1 overflow-hidden">
                            <div class="flex-1 text-center px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 rounded-lg cursor-pointer transition-colors shadow-sm" data-value="active" onclick="selectCustomOption(this)">Active</div>
                            <div class="flex-1 text-center px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white dark:hover:bg-slate-800 dark:text-slate-300 rounded-lg cursor-pointer transition-colors" data-value="inactive" onclick="selectCustomOption(this)">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white outline-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"></textarea>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" onclick="closeAdminModal('create-route-modal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors font-semibold text-sm">Cancel</button>
            <button type="submit" form="create-route-form" class="px-4 py-2 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition-colors">Create Route</button>
        </x-slot:footer>
    </form>
</x-modal>
@endsection
