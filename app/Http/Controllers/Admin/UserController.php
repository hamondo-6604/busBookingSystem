<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use App\Models\DiscountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Clear new user notifications when admin visits the users page
        if (auth()->check()) {
            auth()->user()->appNotifications()
                ->unread()
                ->where('type', 'new_user')
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $query = User::with(['userType', 'discountType']);

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        
        $userTypes = UserType::all();
        $discountTypes = DiscountType::all();

        return view('admin.users.index', compact('users', 'userTypes', 'discountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,driver,customer',
            'status' => 'required|in:active,inactive,suspended',
            'user_type_id' => 'nullable|exists:user_types,id',
            'discount_type_id' => 'nullable|exists:discount_types,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        
        $type = $user->role === 'driver' ? 'new_driver' : 'new_user';
        $title = $user->role === 'driver' ? 'New Driver Added' : 'New User Added';
        $message = $user->role === 'driver' 
            ? "A new driver ({$user->name}) has been created by an admin." 
            : "A new user ({$user->name}) has been created by an admin.";

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'is_read' => false,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User created successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,driver,customer',
            'status' => 'required|in:active,inactive,suspended',
            'user_type_id' => 'nullable|exists:user_types,id',
            'discount_type_id' => 'nullable|exists:discount_types,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->bookings()->exists() || $user->driver()->exists()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete user because they have associated records.'], 400);
            }
            return back()->with('error', 'Cannot delete user because they have associated records.');
        }

        $user->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
