<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function index()
    {
        $user = auth()->user(); // Fetch the currently authenticated user
    
        // Fetch the latest 4 login details for the authenticated user
        $loginDetails = LoginDetail::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    
        return view('pages.profile.mail-settings', compact('user', 'loginDetails'));
    }

    public function update(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'address' => 'nullable|string|max:255',
            'contact_details' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        // Get the authenticated user
        $user = auth()->user();
    
        try {
            // Prepare profile data
            $profileData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'middle_name' => $request->middle_name,
                'name_extension' => $request->name_extension,
                'address' => $request->address,
                'contact_details' => $request->contact_details,
            ];
    
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old profile photo if it exists
                if ($user->profile && $user->profile->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile->profile_photo_path);
                }
    
                // Store the new photo with the user's UUID as the filename
                $filename = $user->id . '.' . $request->file('profile_photo')->getClientOriginalExtension();
                $path = $request->file('profile_photo')->storeAs('profile_photos', $filename, 'public');
    
                // Add profile photo path to the profile data
                $profileData['profile_photo_path'] = $path;
            }
    
            // Update the user's main details
            $user->update([
                'email' => $validatedData['email'],
                'username' => $validatedData['username'],
            ]);
    
            // Update or create the user's profile
            $user->profile()->updateOrCreate([], $profileData);
    
            // Redirect to the profile page with a success message
            return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            // Handle errors and return an error message
            return back()->withErrors(['error' => 'An error occurred while updating the profile. Please try again later.']);
        }
    }
    
    

    public function updatePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
            'confirm_password' => 'required|same:new_password',
        ]);
    
        $user = auth()->user();
    
        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
    
        // Update the password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
    
        return redirect()->route('profile.index')->with('success', 'Password updated successfully.');
    }
    
}
