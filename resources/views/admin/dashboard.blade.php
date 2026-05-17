@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard Overview</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Here is what's happening with Mindanao Express today.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Card: Users -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-0.5">Total Customers</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['total_users']) }}</h3>
        </div>
    </div>

    <!-- Stat Card: Bookings -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-0.5">Today's Bookings</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['today_bookings']) }}</h3>
        </div>
    </div>

    <!-- Stat Card: Active Trips -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-bus"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-0.5">Active Trips</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['active_trips']) }}</h3>
        </div>
    </div>

    <!-- Stat Card: Revenue -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-0.5">Today's Revenue</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">₱{{ number_format($stats['today_revenue'], 2) }}</h3>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Revenue per Route Bar Chart -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Revenue per Route</h2>
            
            <!-- Flowbite Datepicker -->
            <div class="relative max-w-sm w-40">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z"/>
                    </svg>
                </div>
                <input id="revenue-datepicker" 
                       datepicker 
                       datepicker-buttons 
                       datepicker-autoselect-today 
                       datepicker-format="yyyy-mm-dd" 
                       datepicker-min-date="2025-01-01"
                       datepicker-max-date="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                       type="text" 
                       class="block w-full ps-9 pe-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm placeholder:text-slate-400" 
                       placeholder="Filter by date" 
                       value="{{ $selectedDate ?? '' }}">
            </div>
        </div>

        <div class="relative h-64 mt-auto">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Trip Status Donut Chart -->
    <div class="lg:col-span-1 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Trip Status</h2>
        <div class="relative h-64 flex justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Recent Bookings Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Recent Bookings</h2>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-700">
                        <th class="p-4">Passenger</th>
                        <th class="p-4">Route</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($recentBookings as $booking)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-white text-sm">{{ $booking->user->name ?? 'Guest' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->pnr }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm text-slate-800 dark:text-white">{{ $booking->trip->route->route_name ?? 'Unknown Route' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->trip->trip_date ? Carbon\Carbon::parse($booking->trip->trip_date)->format('M d, Y') : '' }}</div>
                        </td>
                        <td class="p-4">
                            @if($booking->status == 'confirmed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Confirmed</span>
                            @elseif($booking->status == 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">Pending</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Cancelled</span>
                            @endif
                        </td>
                        <td class="p-4 text-right text-sm font-bold text-slate-800 dark:text-white">
                            ₱{{ number_format($booking->amount_paid, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">No recent bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upcoming Trips Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Upcoming Trips</h2>
            <a href="{{ route('admin.trips.index') }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-700">
                        <th class="p-4">Trip Code</th>
                        <th class="p-4">Route & Bus</th>
                        <th class="p-4">Departure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($upcomingTrips as $trip)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-800 dark:text-white text-sm">{{ $trip->trip_code }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-white">{{ $trip->route->route_name ?? 'Unknown' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400"><i class="fa-solid fa-bus text-[10px] mr-1"></i>{{ $trip->bus->bus_number ?? 'Not assigned' }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm text-slate-800 dark:text-white">{{ Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</div>
                            <div class="text-xs font-semibold text-primary-600 dark:text-primary-400">{{ Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">No upcoming trips scheduled.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Determine Theme for chart colors
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#cbd5e1' : '#475569';
    const gridColor = isDarkMode ? '#334155' : '#e2e8f0';

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Inter', sans-serif";

    // 1. Revenue per Route Bar Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($revenuePerRoute);
    const routeLabels = Object.keys(revenueData);
    const routeRevenues = Object.values(revenueData);
    
    // Create a beautiful premium gradient
    let barGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
    barGradient.addColorStop(0, '#0ea5e9'); // Tailwind primary-500
    barGradient.addColorStop(1, isDarkMode ? 'rgba(14, 165, 233, 0.1)' : 'rgba(14, 165, 233, 0.2)');
    
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: routeLabels.length ? routeLabels : ['No Data'],
            datasets: [{
                label: 'Revenue (₱)',
                data: routeRevenues.length ? routeRevenues : [0],
                backgroundColor: barGradient,
                borderColor: '#0ea5e9',
                borderWidth: { top: 2, right: 2, left: 2, bottom: 0 },
                borderRadius: { topLeft: 8, topRight: 8 },
                borderSkipped: false,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDarkMode ? '#1e293b' : '#ffffff',
                    titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                    bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                    borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    titleFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                    bodyFont: { size: 13, family: "'Inter', sans-serif" }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: gridColor, drawBorder: false }, 
                    ticks: { color: textColor, padding: 10 } 
                },
                x: { 
                    grid: { display: false, drawBorder: false }, 
                    ticks: { 
                        color: textColor, 
                        maxRotation: 0, 
                        minRotation: 0,
                        padding: 10,
                        callback: function(value, index, values) {
                            // Truncate long labels so they don't slant or overlap
                            let label = this.getLabelForValue(value);
                            if (label && label.length > 20) {
                                return label.substr(0, 18) + '...';
                            }
                            return label;
                        }
                    } 
                }
            }
        }
    });

    // 2. Trip Status Donut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($tripStatuses);
    const statusLabels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
    const statusCounts = Object.values(statusData);

    // Color mapping
    const statusColors = {
        'Scheduled': '#38bdf8', // blue
        'Ongoing': '#f59e0b',   // amber
        'Completed': '#10b981', // emerald
        'Cancelled': '#ef4444', // red
        'Delayed': '#8b5cf6'    // violet
    };
    
    const backgroundColors = statusLabels.map(label => statusColors[label] || '#94a3b8');

    new Chart(statusCtx, {
        type: 'doughnut',
        plugins: [ChartDataLabels],
        data: {
            labels: statusLabels.length ? statusLabels : ['No Data'],
            datasets: [{
                data: statusCounts.length ? statusCounts : [1],
                backgroundColor: statusCounts.length ? backgroundColors : ['#cbd5e1'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: { position: 'right', labels: { padding: 20, usePointStyle: true, color: textColor } },
                tooltip: {
                    backgroundColor: isDarkMode ? '#1e293b' : '#ffffff',
                    titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                    bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                    borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    titleFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                    bodyFont: { size: 13, family: "'Inter', sans-serif" }
                },
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: (value, ctx) => {
                        let sum = 0;
                        let dataArr = ctx.chart.data.datasets[0].data;
                        dataArr.forEach(data => { sum += Number(data); });
                        if (sum === 0) return '';
                        let percentage = (value * 100 / sum).toFixed(1) + "%";
                        return percentage;
                    },
                    display: function(context) {
                        return context.dataset.data[context.dataIndex] > 0; // hide 0% labels
                    }
                }
            }
        }
    });

    // 3. Handle Datepicker Selection
    const datepickerInput = document.getElementById('revenue-datepicker');
    if (datepickerInput) {
        // Use a timeout to ensure the input value has been fully updated by Flowbite
        const handleDateChange = function() {
            setTimeout(() => {
                const selectedDate = datepickerInput.value;
                const url = new URL(window.location.href);
                
                if (selectedDate) {
                    url.searchParams.set('date', selectedDate);
                } else {
                    url.searchParams.delete('date');
                }
                
                window.location.href = url.toString();
            }, 50);
        };

        // Listen for Flowbite's specific date change event
        datepickerInput.addEventListener('changeDate', handleDateChange);
        // Fallback for clear/today button clicks that might not fire changeDate
        datepickerInput.addEventListener('hideDatepicker', handleDateChange);
        
        // Handle manual typing or native clear
        datepickerInput.addEventListener('change', handleDateChange);

        // Handle clear button click (value becomes empty)
        datepickerInput.addEventListener('input', function(e) {
            if (!e.target.value) {
                const url = new URL(window.location.href);
                url.searchParams.delete('date');
                window.location.href = url.toString();
            }
        });
    }
});
</script>
@endsection
