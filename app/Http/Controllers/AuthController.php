<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate
        $fields = $request->validate([
            'name' => 'required|min:5|max:20',
            'email' => 'required|max:255|email|unique:users',
            'password' => 'required|min:5|confirmed',
        ]);

        // Register
        $user = User::create($fields);

        // Login
        Auth::login($user);

        // Redirect
        return redirect('/');
    }

    public function login(Request $request)
    {
        // Validate
        $fields = $request->validate([
            'email' => 'required|max:255|email',
            'password' => 'required'
        ]);

        // Try to login user
        if (Auth::attempt($fields, $request->remember)) {
            return redirect('/');
        }

        // Failed to login
        return back()->withErrors([
            'failed' => 'The provided credentials do not match our records.'
        ]);
    }

    public function logout(Request $request)
    {
        // Logout user
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Redirect
        return redirect('/');
    }
}
