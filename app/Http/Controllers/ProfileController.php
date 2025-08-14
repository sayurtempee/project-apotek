<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile', ['title' => 'Profile', 'project' => 'Apotek Mii']);
    }


    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kasir') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($user->foto && file_exists(storage_path('app/public/' . $user->foto))) {
                unlink(storage_path('app/public/' . $user->foto));
            }

            $user->foto = $request->file('foto')->store('profile', 'public');
        }

        $user->save();

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }
}
