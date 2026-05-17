<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Payment;
use Carbon\Carbon;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        
        $selectedDate = $request->input('date');

        $stats = [
            'total_users'     => User::where('role', 'customer')->count(),
            'today_bookings'  => Booking::whereDate('created_at', $today)->count(),
            'active_trips'    => Trip::where('status', 'scheduled')->whereDate('trip_date', '>=', $today)->count(),
            'today_revenue'   => Payment::whereIn('status', ['paid', 'completed'])->whereDate('created_at', $today)->sum('amount'),
        ];

        $recentBookings = Booking::with(['user', 'trip.route'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $upcomingTrips = Trip::with(['route', 'bus'])
            ->where('status', 'scheduled')
            ->whereDate('trip_date', '>=', $today)
            ->orderBy('trip_date', 'asc')
            ->orderBy('departure_time', 'asc')
            ->take(5)
            ->get();

        // Chart Data: Trip Status
        $tripStatuses = Trip::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Chart Data: Revenue per Route
        $revenueQuery = Booking::join('trips', 'bookings.trip_id', '=', 'trips.id')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->where('bookings.status', 'confirmed');
            
        if ($selectedDate) {
            // Assume the user wants revenue from bookings CREATED on this date
            // or perhaps trips scheduled on this date? The chart says "Revenue per Route". 
            // Usually we filter by when the booking was made.
            $revenueQuery->whereDate('bookings.created_at', $selectedDate);
        }

        $revenuePerRoute = $revenueQuery->select('routes.route_name', \DB::raw('SUM(bookings.amount_paid) as total_revenue'))
            ->groupBy('routes.id', 'routes.route_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->pluck('total_revenue', 'route_name')
            ->toArray();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'upcomingTrips', 'tripStatuses', 'revenuePerRoute', 'selectedDate'));
    }
}