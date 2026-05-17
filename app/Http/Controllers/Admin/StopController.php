<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Stop;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StopController extends Controller
{
    public function index(Request $request)
    {
        $query = Stop::with(['city', 'terminal']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('city', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $stops = $query->orderBy('name')->paginate(15)->withQueryString();
        $cities = City::orderBy('name')->get();
        $terminals = Terminal::with('city')->orderBy('name')->get();

        return view('admin.stops.index', compact('stops', 'cities', 'terminals'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateStop($request);

        $stop = Stop::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stop created successfully.',
                'data'    => $stop->load(['city', 'terminal']),
            ]);
        }

        return redirect()->route('admin.stops.index')->with('success', 'Stop created successfully.');
    }

    public function update(Request $request, Stop $stop)
    {
        $validated = $this->validateStop($request, $stop);

        $stop->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stop updated successfully.',
                'data'    => $stop->load(['city', 'terminal']),
            ]);
        }

        return redirect()->route('admin.stops.index')->with('success', 'Stop updated successfully.');
    }

    public function destroy(Request $request, Stop $stop)
    {
        if ($stop->routes()->exists()) {
            $message = 'Cannot delete this stop because it is assigned to one or more routes.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return back()->with('error', $message);
        }

        $stop->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Stop deleted successfully.']);
        }

        return redirect()->route('admin.stops.index')->with('success', 'Stop deleted successfully.');
    }

    private function validateStop(Request $request, ?Stop $stop = null): array
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('stops', 'code')->ignore($stop?->id),
            ],
            'city_id'     => 'nullable|exists:cities,id',
            'terminal_id' => 'nullable|exists:terminals,id',
            'address'     => 'nullable|string|max:500',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'type'        => 'required|in:terminal,pickup,dropoff,waypoint,barangay',
            'status'      => 'required|in:active,inactive',
        ]);

        if ($validated['type'] !== 'terminal') {
            $validated['terminal_id'] = null;
        }

        return $validated;
    }
}
