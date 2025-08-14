<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KasirController extends Controller
{
    public function index()
    {
        $kasirs = User::where('role', 'kasir')->get();
        $title = "Daftar Kasir";
        $project = "Apotek Mii";
        return view('kasir.index', compact('kasirs', 'title', 'project'));
    }

    public function create()
    {
        $title = "Tambah Kasir";
        $project = "Apotek Mii";
        return view('kasir.create', compact('title', 'project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'password.min' => 'Password harus minimal 8 karakter.',
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('profile', 'public');
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'kasir',
            'foto'     => $path,
        ]);

        return redirect()->route('kasir.index')->with('success', 'Kasir berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kasir = User::where('role', 'kasir')->findOrFail($id);
        $title = "Detail Kasir";
        $project = "Apotek Mii";
        return view('kasir.show', compact('kasir', 'title', 'project'));
    }

    public function edit($id)
    {
        $kasir = User::where('role', 'kasir')->findOrFail($id);
        $title = "Edit Kasir";
        $project = "Apotek Mii";
        return view('kasir.edit', compact('kasir', 'title', 'project'));
    }


    public function update(Request $request, $id)
    {
        $kasir = User::where('role', 'kasir')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $kasir->id,
            'password' => 'nullable|string|min:8',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'password.min' => 'Password harus minimal 8 karakter.',
        ]);

        $kasir->name  = $request->name;
        $kasir->email = $request->email;
        if ($request->filled('password')) {
            $kasir->password = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($kasir->foto && file_exists(storage_path('app/public/' . $kasir->foto))) {
                unlink(storage_path('app/public/' . $kasir->foto));
            }
            $path = $request->file('foto')->store('profile', 'public');
            $kasir->foto = $path;
        }

        $kasir->save();

        return redirect()->route('kasir.index')->with('success', 'Kasir berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kasir = User::where('role', 'kasir')->findOrFail($id);
        $kasir->delete();

        return redirect()->route('kasir.index')->with('success', 'Kasir berhasil dihapus.');
    }
}
