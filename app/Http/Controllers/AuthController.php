<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Form Login
    public function showLogin()
    {
        $title = 'Login';
        $project = 'Apotek Mii';
        return view('auth.login', compact('title', 'project'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {


            // Saat login, set status jadi aktif
            $user = Auth::user();

            if ($user->status) {
                return redirect()->route('login')
                ->with('error', 'Akun ini sedang aktif di perangkat lain. Silakan logout dulu.');
            }

            $request->session()->regenerate();

            $user->status = true;
            $user->save();

            return redirect()
                ->route('dashboard')
                ->with([
                    'login_success' => true,
                    'login_name' => $user->name,
                    'login_role' => $user->role
                ]);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    // Logout
    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Saat logout, set status jadi tidak aktif
            $user = Auth::user();
            $user->status = false;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
