<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ROle;
use App\Models\LoginDetail; // Import the LoginDetail model
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    /**
     * Show the sign-up form.
     */
    public function showSignUpForm()
    {
        $roles = Role::all();
        return view('auth.sign-up', compact('roles'));
    }
    
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the sign-up form submission.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|exists:roles,name', // Ensure the role exists in the roles table
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        // Create the user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);
    
        // Assign the selected role to the user
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->assignRole($role);
        }
    
        return redirect()->route('login')->with('success', 'Account created successfully. Please log in.');
    }
    

    /**
     * Handle login attempts.
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        Log::info('Login attempt started.', [
            'email' => $validatedData['email'],
            'ip_address' => $ip,
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'device' => $userAgent,
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $user = Auth::user(); // Get authenticated user

            // Insert login details into the database
            $this->storeLoginDetails($user, $ip, $validatedData, $userAgent);

            Log::info('User logged in successfully.', [
                'email' => $validatedData['email'],
                'user_id' => $user->id,
                'ip_address' => $ip,
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'device' => $userAgent,
            ]);

            return redirect()->intended('/');
        }

        Log::warning('Failed login attempt.', [
            'email' => $validatedData['email'],
            'ip_address' => $ip,
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'device' => $userAgent,
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Store login details in the database.
     */
    protected function storeLoginDetails($user, $ip, $validatedData, $userAgent)
    {
        $location = "https://www.google.com/maps?q={$validatedData['latitude']},{$validatedData['longitude']}";
        $address = $this->fetchAddress($validatedData['latitude'], $validatedData['longitude']);
    
        try {
            LoginDetail::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'ip_address' => $ip,
                'device' => $userAgent,
                'location' => $location,
                'address' => $address,
            ]);
    
            Log::info('Login details saved successfully.', [
                'user_id' => $user->id,
                'ip_address' => $ip,
                'device' => $userAgent,
                'location' => $location,
                'address' => $address,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save login details.', [
                'user_id' => $user->id,
                'ip_address' => $ip,
                'device' => $userAgent,
                'location' => $location,
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
        }
    }
    

    /**
     * Fetch address using latitude and longitude.
     */
    protected function fetchAddress($latitude, $longitude)
    {
        try {
            $query = "{$latitude},{$longitude}";
            Log::info('Positionstack API request', ['query' => $query]);
    
            $geoResponse = Http::get("http://api.positionstack.com/v1/reverse", [
                'access_key' => env('POSITIONSTACK_API_KEY'),
                'query' => $query,
                'limit' => 1,
            ]);
    
            Log::info('Positionstack API response', ['response' => $geoResponse->json()]);
    
            if ($geoResponse->ok() && isset($geoResponse->json()['data'][0])) {
                $addressData = $geoResponse->json()['data'][0];
    
                // Use county if locality is not available
                $cityOrCounty = $addressData['locality'] ?? $addressData['county'] ?? 'Unknown City';
                $province = $addressData['region'] ?? 'Unknown Province';
                $country = $addressData['country'] ?? 'Unknown Country';
    
                return "{$cityOrCounty}, {$province}, {$country}";
            }
        } catch (\Exception $e) {
            Log::error('Error fetching address from Positionstack.', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);
        }
    
        return 'Unknown Address';
    }
    
    

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();
        Log::info('User logged out.', ['user_id' => $userId]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
