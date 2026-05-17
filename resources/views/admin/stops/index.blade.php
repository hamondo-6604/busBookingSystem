@extends('layouts.admin')

@section('title', 'Manage Stops')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Bus Stops</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add barangays, terminals, and pickup points used on routes.</p>
    </div>
    <button onclick="openAdminModal('create-stop-modal')" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2 shadow-sm">
        <i class="fa-solid fa-plus"></i> Add Stop
    </button>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6">
    <form method="GET" action="{{ route('admin.stops.index') }}" class="flex flex-col lg:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, code, city, address..."
                   class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors">
        </div>

        <div class="w-full sm:w-44 relative" data-custom-select>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Type</label>
            <input type="hidden" name="type" value="{{ request('type') }}" class="custom-select-input">
            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full px-4 py-2 flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                <span class="custom-select-text">{{ request('type') ? ucfirst(request('type')) : 'All types' }}</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-2 shrink-0"></i>
            </button>
            <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50 max-h-48 overflow-y-auto">
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer" data-value="" onclick="selectCustomOption(this)">All types</div>
                @foreach(['barangay','terminal','pickup','dropoff','waypoint'] as $t)
                    <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer {{ request('type') === $t ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : '' }}" data-value="{{ $t }}" onclick="selectCustomOption(this)">{{ ucfirst($t) }}</div>
                @endforeach
            </div>
        </div>

        <div class="w-full sm:w-48 relative" data-custom-select>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">City</label>
            <input type="hidden" name="city_id" value="{{ request('city_id') }}" class="custom-select-input">
            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full px-4 py-2 flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                <span class="custom-select-text truncate">{{ $cities->firstWhere('id', request('city_id'))?->name ?? 'All cities' }}</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-2 shrink-0"></i>
            </button>
            <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50 max-h-56 overflow-y-auto">
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer" data-value="" onclick="selectCustomOption(this)">All cities</div>
                @foreach($cities as $city)
                    <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer {{ (string) request('city_id') === (string) $city->id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : '' }}" data-value="{{ $city->id }}" onclick="selectCustomOption(this)">{{ $city->name }}</div>
                @endforeach
            </div>
        </div>

        <div class="w-full sm:w-44 relative" data-custom-select>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
            <input type="hidden" name="status" value="{{ request('status') }}" class="custom-select-input">
            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full px-4 py-2 flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors cursor-pointer">
                <span class="custom-select-text">
                    @if(request('status') === 'active') Active
                    @elseif(request('status') === 'inactive') Inactive
                    @else All
                    @endif
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-2 shrink-0"></i>
            </button>
            <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50 overflow-hidden">
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer" data-value="" onclick="selectCustomOption(this)">All</div>
                <div class="px-4 py-2.5 text-sm font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400 hover:bg-emerald-100 cursor-pointer" data-value="active" onclick="selectCustomOption(this)">Active</div>
                <div class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer" data-value="inactive" onclick="selectCustomOption(this)">Inactive</div>
            </div>
        </div>

        <div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl transition-colors">Filter</button>
            @if(request()->hasAny(['search','type','city_id','status']))
                <a href="{{ route('admin.stops.index') }}" class="ml-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                    <th class="p-4">Stop</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">City</th>
                    <th class="p-4">Code</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($stops as $stop)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                    <td class="p-4">
                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $stop->name }}</div>
                        @if($stop->terminal)
                            <div class="text-xs text-slate-500 mt-0.5"><i class="fa-solid fa-bus"></i> {{ $stop->terminal->name }}</div>
                        @endif
                    </td>
                    <td class="p-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ $stop->type_label }}</span>
                    </td>
                    <td class="p-4 text-sm text-slate-700 dark:text-slate-300">{{ $stop->city?->name ?? '—' }}</td>
                    <td class="p-4 text-sm font-mono text-slate-500">{{ $stop->code ?? '—' }}</td>
                    <td class="p-4">
                        @if($stop->status === 'active')
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                        @else
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Inactive</span>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="openAdminModal('edit-stop-modal-{{ $stop->id }}')" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                            <button onclick="openAdminModal('delete-stop-modal-{{ $stop->id }}')" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/20 text-red-600 flex items-center justify-center"><i class="fa-solid fa-trash-can text-sm"></i></button>
                        </div>
                    </td>
                </tr>

                <x-modal id="edit-stop-modal-{{ $stop->id }}" title="Edit Stop" size="md">
                    <form id="edit-stop-form-{{ $stop->id }}" action="{{ route('admin.stops.update', $stop) }}" method="POST" onsubmit="handleAjaxForm(this, 'edit-stop-modal-{{ $stop->id }}', () => setTimeout(() => window.location.reload(), 700), event)">
                        @csrf @method('PUT')
                        @include('admin.partials.stop-form-fields', ['stop' => $stop])
                        <x-slot:footer>
                            <button type="button" onclick="closeAdminModal('edit-stop-modal-{{ $stop->id }}')" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 font-semibold text-sm">Cancel</button>
                            <button type="submit" form="edit-stop-form-{{ $stop->id }}" class="px-4 py-2 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700">Save</button>
                        </x-slot:footer>
                    </form>
                </x-modal>

                <x-modal id="delete-stop-modal-{{ $stop->id }}" title="Delete Stop" size="sm">
                    <form action="{{ route('admin.stops.destroy', $stop) }}" method="POST" onsubmit="handleAjaxForm(this, 'delete-stop-modal-{{ $stop->id }}', () => setTimeout(() => window.location.reload(), 700), event)">
                        @csrf @method('DELETE')
                        <div class="text-center py-4">
                            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 flex items-center justify-center text-3xl mx-auto mb-4">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Delete <strong>{{ $stop->name }}</strong>? This cannot be undone if the stop is on a route.</p>
                        </div>
                        <x-slot:footer>
                            <button type="button" onclick="closeAdminModal('delete-stop-modal-{{ $stop->id }}')" class="px-4 py-2 rounded-xl text-slate-600 font-semibold text-sm">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold text-sm hover:bg-red-700">Yes, Delete</button>
                        </x-slot:footer>
                    </form>
                </x-modal>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-slate-500">No stops found. Click <strong>Add Stop</strong> to create barangay or terminal stops.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stops->hasPages())<div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $stops->links() }}</div>@endif
</div>

<x-modal id="create-stop-modal" title="Add Bus Stop" size="md">
    <form id="create-stop-form" action="{{ route('admin.stops.store') }}" method="POST" onsubmit="handleAjaxForm(this, 'create-stop-modal', () => setTimeout(() => window.location.reload(), 700), event)">
        @csrf
        @include('admin.partials.stop-form-fields')
        <x-slot:footer>
            <button type="button" onclick="closeAdminModal('create-stop-modal')" class="px-4 py-2 rounded-xl text-slate-600 font-semibold text-sm">Cancel</button>
            <button type="submit" form="create-stop-form" class="px-4 py-2 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700">Create Stop</button>
        </x-slot:footer>
    </form>
</x-modal>

<script>
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('stop-type-select')) return;
    const form = e.target.closest('form') || e.target.closest('.stop-form-fields') || document;
    const field = form.querySelector('.stop-terminal-field');
    if (field) field.classList.toggle('hidden', e.target.value !== 'terminal');
});
</script>
@endsection
