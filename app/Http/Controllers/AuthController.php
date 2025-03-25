<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Hardcoded admin logic
        if ($request->username === 'admin') {
            if ($request->password === env('ADMIN_PASSWORD', 'defaultadminpass')) {
                session(['is_admin' => true]);
                return redirect()->route('crop-add');
            }
            return back()->withErrors(['password' => 'Invalid admin password.']);
        }

        // Normal user login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('crop-add');
        }

        return back()->withErrors(['username' => 'Invalid credentials.']);
    }


    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Prevent users from registering as "admin"
        if (strtolower($request->username) === 'admin') {
            return back()->withErrors(['username' => 'This username is reserved.']);
        }

        // Create user with default 'farmer' role
        $user = User::create([
            'UserName' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'farmer', // Default role
        ]);

        Auth::login($user);

        return redirect()->route('crop-add');
    }


    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login');
    }

}
