<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Ambil semua user yang role-nya admin
        $admins = User::where('role', 'admin')->orderBy('created_at', 'desc')->get();

        return view('admin.index', [
            'admins' => $admins,
            'title' => 'Daftar Admin',
            'project' => 'Apotek Mii'
        ]);
    }

    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'foto'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $admin->name  = $request->name;
        $admin->email = $request->email;

        if ($request->hasFile('foto')) {
            if ($admin->foto && file_exists(storage_path('app/public/' . $admin->foto))) {
                unlink(storage_path('app/public/' . $admin->foto));
            }
            $admin->foto = $request->file('foto')->store('profile', 'public');
        }

        $admin->save();

        return redirect()->route('admin.index')->with('success', 'Profil admin berhasil diperbarui.');
    }
}
