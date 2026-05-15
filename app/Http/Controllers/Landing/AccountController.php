<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\DiscountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\Cloudinary;

class AccountController extends Controller
{
    public function index()
    {
        $user          = Auth::user()->load('userType', 'discountType');
        $discountTypes = DiscountType::active()->get();

        return view('pages.account', compact('user', 'discountTypes'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('name', 'phone');

        if ($request->hasFile('image_url')) {
            try {
                $data['image_url'] = Cloudinary::upload(
                    $request->file('image_url')->getRealPath(),
                    ['folder' => Cloudinary::FOLDER_PROFILE_PHOTOS]
                )->getSecurePath();
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                return back()->withErrors(['image_url' => 'Failed to upload image to the cloud. Error: ' . $e->getMessage()]);
            }
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}