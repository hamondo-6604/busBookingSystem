<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusRoute;
use App\Models\City;
use App\Models\Stop;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $query = BusRoute::with(['originCity', 'destinationCity', 'originTerminal', 'destinationTerminal'])
            ->withCount('stops');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('route_name', 'like', "%{$search}%")
                    ->orWhereHas('originCity', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('destinationCity', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $routes = $query->latest()->paginate(15)->withQueryString();
        $cities = City::active()->orderBy('name')->get();
        $terminals = Terminal::active()->orderBy('name')->get();

        return view('admin.routes.index', compact('routes', 'cities', 'terminals'));
    }

    public function create()
    {
        $cities = City::active()->orderBy('name')->get();
        $terminals = Terminal::active()->with('city')->orderBy('name')->get();

        return view('admin.routes.form', ['route' => new BusRoute(), 'cities' => $cities, 'terminals' => $terminals]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRoute($request);

        BusRoute::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Route created successfully.']);
        }

        return redirect()->route('admin.routes.index')->with('success', 'Route created successfully.');
    }

    public function show(BusRoute $route)
    {
        $route->load([
            'originCity',
            'destinationCity',
            'originTerminal',
            'destinationTerminal',
            'stops.city',
            'stops.terminal',
        ]);

        $availableStops = Stop::with(['city', 'terminal'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.routes.show', compact('route', 'availableStops'));
    }

    public function edit(BusRoute $route)
    {
        $cities = City::active()->orderBy('name')->get();
        $terminals = Terminal::active()->with('city')->orderBy('name')->get();

        return view('admin.routes.form', compact('route', 'cities', 'terminals'));
    }

    public function update(Request $request, BusRoute $route)
    {
        $validated = $this->validateRoute($request, $route);

        $route->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Route updated successfully.']);
        }

        return redirect()->route('admin.routes.index')->with('success', 'Route updated successfully.');
    }

    public function syncStops(Request $request, BusRoute $route)
    {
        $validated = $request->validate([
            'stops'                      => 'nullable|array',
            'stops.*.stop_id'            => 'required|exists:stops,id',
            'stops.*.stop_order'         => 'required|integer|min:1',
            'stops.*.minutes_from_origin'=> 'nullable|integer|min:0',
            'stops.*.fare_from_origin'   => 'nullable|numeric|min:0',
            'stops.*.allows_boarding'    => 'nullable|boolean',
            'stops.*.allows_alighting'   => 'nullable|boolean',
        ]);

        $sync = [];

        foreach ($validated['stops'] ?? [] as $row) {
            $sync[$row['stop_id']] = [
                'stop_order'          => $row['stop_order'],
                'minutes_from_origin' => $row['minutes_from_origin'] ?? null,
                'fare_from_origin'    => $row['fare_from_origin'] ?? null,
                'allows_boarding'     => ! empty($row['allows_boarding']),
                'allows_alighting'    => ! empty($row['allows_alighting']),
            ];
        }

        $route->stops()->sync($sync);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Route stops updated successfully.']);
        }

        return redirect()
            ->route('admin.routes.show', $route)
            ->with('success', 'Route stops updated successfully.');
    }

    public function destroy(Request $request, BusRoute $route)
    {
        if ($route->trips()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete route because it is assigned to existing trips.'], 400);
            }

            return back()->with('error', 'Cannot delete route because it is assigned to existing trips.');
        }

        $route->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Route deleted successfully.']);
        }

        return redirect()->route('admin.routes.index')->with('success', 'Route deleted successfully.');
    }

    private function validateRoute(Request $request, ?BusRoute $route = null): array
    {
        $uniqueRule = Rule::unique('routes', 'origin_city_id')
            ->where('destination_city_id', $request->input('destination_city_id'))
            ->where('service_type', $request->input('service_type'));

        if ($route) {
            $uniqueRule->ignore($route->id);
        }

        $validated = $request->validate([
            'origin_city_id'             => ['required', 'exists:cities,id', $uniqueRule],
            'destination_city_id'        => 'required|exists:cities,id|different:origin_city_id',
            'service_type'               => 'required|in:non_stop,express,regular',
            'origin_terminal_id'         => 'nullable|exists:terminals,id',
            'destination_terminal_id'    => 'nullable|exists:terminals,id',
            'distance_km'                => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'status'                     => 'required|in:active,inactive',
            'description'                => 'nullable|string|max:1000',
            'route_name'                 => 'nullable|string|max:255',
        ]);

        $validated['route_name'] = $validated['route_name']
            ?: $this->buildRouteName(
                (int) $validated['origin_city_id'],
                (int) $validated['destination_city_id'],
                $validated['service_type']
            );

        return $validated;
    }

    private function buildRouteName(int $originCityId, int $destinationCityId, string $serviceType): string
    {
        $origin = City::find($originCityId);
        $dest   = City::find($destinationCityId);

        $label = match ($serviceType) {
            BusRoute::SERVICE_NON_STOP => 'Non-Stop',
            BusRoute::SERVICE_EXPRESS  => 'Express',
            BusRoute::SERVICE_REGULAR  => 'Regular',
            default                    => ucfirst($serviceType),
        };

        return ($origin?->name ?? 'Origin') . ' → ' . ($dest?->name ?? 'Destination') . ' (' . $label . ')';
    }
}
