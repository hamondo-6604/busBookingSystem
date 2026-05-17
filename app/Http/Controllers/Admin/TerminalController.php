<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TerminalController extends Controller
{
    public function index(Request $request)
    {
        $query = Terminal::with(['city']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $terminals = $query->orderBy('name')->paginate(15)->withQueryString();
        $cities = City::orderBy('name')->get();

        return view('admin.terminals.index', compact('terminals', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTerminal($request);

        $terminal = Terminal::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Terminal created successfully.',
                'data'    => $terminal->load(['city']),
            ]);
        }

        return redirect()->route('admin.terminals.index')->with('success', 'Terminal created successfully.');
    }

    public function update(Request $request, Terminal $terminal)
    {
        $validated = $this->validateTerminal($request, $terminal);

        $terminal->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Terminal updated successfully.',
                'data'    => $terminal->load(['city']),
            ]);
        }

        return redirect()->route('admin.terminals.index')->with('success', 'Terminal updated successfully.');
    }

    public function destroy(Request $request, Terminal $terminal)
    {
        if ($terminal->originRoutes()->exists() || $terminal->destinationRoutes()->exists() || $terminal->departingTrips()->exists() || $terminal->arrivingTrips()->exists()) {
            $message = 'Cannot delete this terminal because it is assigned to routes or trips.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return back()->with('error', $message);
        }

        $terminal->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Terminal deleted successfully.']);
        }

        return redirect()->route('admin.terminals.index')->with('success', 'Terminal deleted successfully.');
    }

    private function validateTerminal(Request $request, ?Terminal $terminal = null): array
    {
        return $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => [
                'required',
                'string',
                'max:20',
                Rule::unique('terminals', 'code')->ignore($terminal?->id),
            ],
            'city_id'        => 'nullable|exists:cities,id',
            'address'        => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'opening_time'   => 'nullable|date_format:H:i',
            'closing_time'   => 'nullable|date_format:H:i',
            'status'         => 'required|in:active,inactive',
        ]);
    }
}
