@extends('layouts.admin')

@section('title', 'Trash / Recycle Bin')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Trash</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">View and restore deleted records.</p>
    </div>
</div>

<!-- Tabs -->
<div class="flex overflow-x-auto space-x-2 border-b border-slate-200 dark:border-slate-700 mb-6 pb-2 scrollbar-hide">
    <a href="{{ route('admin.trash.index', ['type' => 'cities']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'cities' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-city mr-2"></i> Cities
        @if(($counts['cities'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['cities'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.trash.index', ['type' => 'routes']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'routes' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-route mr-2"></i> Routes
        @if(($counts['routes'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['routes'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.trash.index', ['type' => 'trips']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'trips' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-location-dot mr-2"></i> Trips
        @if(($counts['trips'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['trips'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.trash.index', ['type' => 'buses']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'buses' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-bus mr-2"></i> Buses
        @if(($counts['buses'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['buses'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.trash.index', ['type' => 'bus-types']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'bus-types' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-couch mr-2"></i> Bus Types
        @if(($counts['bus-types'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['bus-types'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.trash.index', ['type' => 'terminals']) }}" class="px-4 py-2 text-sm font-semibold rounded-xl whitespace-nowrap transition-colors flex items-center {{ $type === 'terminals' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <i class="fa-solid fa-building mr-2"></i> Terminals
        @if(($counts['terminals'] ?? 0) > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white shadow-sm">{{ $counts['terminals'] }}</span>
        @endif
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6">
    <form method="GET" action="{{ route('admin.trash.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Search {{ ucfirst($type) }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or number..." 
                   class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
        </div>
        <div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl transition-colors">
                Search
            </button>
            @if(request()->has('search'))
                <a href="{{ route('admin.trash.index', ['type' => $type]) }}" class="ml-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">Clear</a>
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
                    @if($type === 'cities')
                        <th class="p-4">Name</th>
                        <th class="p-4">Province/Region</th>
                    @elseif($type === 'routes')
                        <th class="p-4">Route Name</th>
                        <th class="p-4">Distance / Duration</th>
                    @elseif($type === 'trips')
                        <th class="p-4">Trip Code</th>
                        <th class="p-4">Trip Date</th>
                    @elseif($type === 'buses')
                        <th class="p-4">Bus Number</th>
                        <th class="p-4">Bus Name</th>
                    @elseif($type === 'bus-types')
                        <th class="p-4">Type Name</th>
                    @elseif($type === 'terminals')
                        <th class="p-4">Terminal Name</th>
                        <th class="p-4">Terminal Code</th>
                    @endif
                    <th class="p-4">Deleted At</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($trashedItems as $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    @if($type === 'cities')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->name }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $item->province }}, {{ $item->region }}
                        </td>
                    @elseif($type === 'routes')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->route_name ?? $item->full_route_name }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $item->distance_km }} km / {{ $item->estimated_duration_minutes }} mins
                        </td>
                    @elseif($type === 'trips')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->trip_code }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $item->trip_date ? $item->trip_date->format('M d, Y') : '—' }}
                        </td>
                    @elseif($type === 'buses')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->bus_number }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $item->bus_name }}
                        </td>
                    @elseif($type === 'bus-types')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->type_name }}</div>
                        </td>
                    @elseif($type === 'terminals')
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->name }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $item->code }}
                        </td>
                    @endif
                    <td class="p-4 text-sm text-slate-500 dark:text-slate-400">
                        {{ $item->deleted_at ? $item->deleted_at->format('M d, Y h:i A') : '—' }}
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" 
                                onclick="openConfirmModal('{{ route('admin.trash.restore', ['type' => $type, 'id' => $item->id]) }}', 'POST', 'Are you sure you want to restore this record?')"
                                class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center transition-colors" title="Restore">
                                <i class="fa-solid fa-arrow-rotate-left text-sm"></i>
                            </button>
                            
                            <button type="button" 
                                onclick="openConfirmModal('{{ route('admin.trash.force-delete', ['type' => $type, 'id' => $item->id]) }}', 'DELETE', 'Are you sure you want to PERMANENTLY delete this record? This action cannot be undone.')"
                                class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center transition-colors" title="Delete Permanently">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <div class="text-4xl mb-2"><i class="fa-solid fa-trash-can text-slate-300 dark:text-slate-600"></i></div>
                        <p>No deleted {{ str_replace('-', ' ', $type) }} found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($trashedItems->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">
        {{ $trashedItems->links() }}
    </div>
    @endif
</div>

<!-- Confirm Modal -->
<x-modal id="confirm-modal" title="Confirm Action" size="sm">
    <form id="confirm-form" method="POST" action="">
        @csrf
        <input type="hidden" name="_method" id="confirm-method" value="POST">
        
        <div class="p-6 text-center">
            <div id="confirm-icon-container" class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                <i id="confirm-icon" class="fa-solid fa-circle-exclamation text-2xl text-slate-500 dark:text-slate-400"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Are you sure?</h3>
            <p id="confirm-message" class="text-sm text-slate-500 dark:text-slate-400 mb-6"></p>
            
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeAdminModal('confirm-modal')" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition-colors shadow-sm shadow-primary-600/20">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </form>
</x-modal>

<script>
    function openConfirmModal(actionUrl, method, message) {
        document.getElementById('confirm-form').action = actionUrl;
        document.getElementById('confirm-method').value = method;
        document.getElementById('confirm-message').textContent = message;
        
        // Update styling based on method
        const submitBtn = document.querySelector('#confirm-modal button[type="submit"]');
        const iconContainer = document.getElementById('confirm-icon-container');
        const icon = document.getElementById('confirm-icon');
        
        if (method === 'DELETE') {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition-colors shadow-sm shadow-red-600/20';
            submitBtn.textContent = 'Yes, Delete Permanently';
            iconContainer.className = 'w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center mx-auto mb-4';
            icon.className = 'fa-solid fa-trash-can text-2xl text-red-600 dark:text-red-500';
        } else {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors shadow-sm shadow-emerald-600/20';
            submitBtn.textContent = 'Yes, Restore';
            iconContainer.className = 'w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/20 flex items-center justify-center mx-auto mb-4';
            icon.className = 'fa-solid fa-arrow-rotate-left text-2xl text-emerald-600 dark:text-emerald-500';
        }
        
        openAdminModal('confirm-modal');
    }
</script>
@endsection
