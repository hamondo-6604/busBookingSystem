<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        // Clear new driver notifications when admin visits the drivers page
        if (auth()->check()) {
            auth()->user()->appNotifications()
                ->unread()
                ->where('type', 'new_driver')
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $query = Driver::with(['user']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('license_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
        }

        $drivers = $query->latest()->paginate(15)->withQueryString();

        $availableUsers = User::where('role', 'driver')->doesntHave('driver')->get();
        $allDriverUsers = User::where('role', 'driver')->get();

        return view('admin.drivers.index', compact('drivers', 'availableUsers', 'allDriverUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:drivers,user_id',
            'license_number' => 'required|string|max:50|unique:drivers,license_number',
            'license_expiry' => 'required|date|after:today',
            'experience_years' => 'nullable|integer|min:0',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:available,on_trip,off_duty,suspended',
        ]);

        $driver = Driver::create($validated);

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        $user = User::find($validated['user_id']);
        $userName = $user ? $user->name : 'Unknown User';

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Driver Profile Created',
                'message' => "A new driver profile for {$userName} has been created by an admin.",
                'type' => 'new_driver',
                'notifiable_type' => Driver::class,
                'notifiable_id' => $driver->id,
                'is_read' => false,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Driver created successfully.']);
        }

        return redirect()->route('admin.drivers.index')->with('success', 'Driver created successfully.');
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'license_number' => ['required', 'string', 'max:50', Rule::unique('drivers')->ignore($driver->id)],
            'license_expiry' => 'required|date',
            'experience_years' => 'nullable|integer|min:0',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:available,on_trip,off_duty,suspended',
        ]);

        $driver->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Driver updated successfully.']);
        }

        return redirect()->route('admin.drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->trips()->exists()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete driver because they are assigned to trips.'], 400);
            }
            return back()->with('error', 'Cannot delete driver because they are assigned to trips.');
        }

        $driver->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Driver deleted successfully.']);
        }

        return redirect()->route('admin.drivers.index')->with('success', 'Driver deleted successfully.');
    }
}
