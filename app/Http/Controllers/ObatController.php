<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Obat;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $query = Obat::query();

        // FILTER ROLE KASIR → hanya tampil stok > 0 & belum kadaluarsa
        if (Auth::user()->role === 'kasir') {
            $query->where('stok', '>', 0)
                ->where(function ($q) use ($today) {
                    $q->whereNull('kadaluarsa')
                        ->orWhere('kadaluarsa', '>=', $today);
                });
        }

        // FILTER KATEGORI (opsional, via query param category_id)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 🔎 FILTER SEARCH (opsional, via query param search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $obats = $query->orderByRaw(
            'CASE WHEN kadaluarsa IS NOT NULL AND kadaluarsa < ? THEN 1 ELSE 0 END',
            [$today]
        )
            ->orderByRaw('CASE WHEN stok = 0 THEN 1 ELSE 0 END')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Ambil kategori
        $categories = \App\Models\Category::orderBy('nama')->get();

        return view('obat.index', [
            'obats'      => $obats,
            'categories' => $categories,
            'title'      => 'Daftar Obat',
            'project'    => 'Apotek Mii',
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('obat.create', compact('categories'), [
            'title' => 'Tambah Obat',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kode'             => 'required|string|max:100|unique:obats,kode',
            'deskripsi'        => 'nullable|string',
            'harga'            => 'required|numeric|min:0',
            'stok'             => 'required|integer|min:0',
            'kategori_select'  => 'required',
            'kategori_lainnya' => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'kadaluarsa'       => 'nullable|date|after_or_equal:today',
        ]);

        if ($validated['kategori_select'] === 'lainnya' && !empty($validated['kategori_lainnya'])) {
            // buat kategori baru
            $namaKategori = trim($validated['kategori_lainnya']);
            $category = Category::firstOrCreate(
                ['nama' => $namaKategori],
                ['slug' => Str::slug($namaKategori)]
            );
        } else {
            // ambil kategori berdasarkan ID
            $category = Category::find($validated['kategori_select']);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('obat', 'public');
        }

        Obat::create([
            'nama'        => $validated['nama'],
            'kode'        => $validated['kode'],
            'deskripsi'   => $validated['deskripsi'],
            'harga'       => $validated['harga'],
            'stok'        => $validated['stok'],
            'category_id' => $category?->id,
            'foto'        => $fotoPath,
            'kadaluarsa'  => $validated['kadaluarsa'] ?? null,
        ]);

        return redirect()->route('obat.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Obat $obat)
    {
        return view('obat.show', [
            'obat'    => $obat,
            'title'   => 'Detail Obat',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Obat $obat)
    {
        $categories = Category::orderBy('nama')->get();
        return view('obat.edit', [
            'obat' => $obat,
            'categories' => $categories,
            'title' => 'Edit Obat',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kode'             => 'required|string|max:100|unique:obats,kode,' . $obat->id,
            'deskripsi'        => 'nullable|string',
            'harga'            => 'required|numeric|min:0',
            'stok'             => 'required|integer|min:0',
            'kategori_select'  => 'required|string',
            'kategori_lainnya' => 'nullable|string|max:100',
            'foto'             => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'kadaluarsa'       => 'nullable|date|after_or_equal:today',
        ]);

        if ($validated['kategori_select'] === 'lainnya' && !empty($validated['kategori_lainnya'])) {
            $namaKategori = trim($validated['kategori_lainnya']);
            $category = Category::firstOrCreate(
                ['nama' => $namaKategori],
                ['slug' => Str::slug($namaKategori)]
            );
        } else {
            $category = Category::firstWhere('nama', $validated['kategori_select']);
        }

        if ($request->hasFile('foto')) {
            // hapus foto lama jika ada
            if ($obat->foto && Storage::disk('public')->exists($obat->foto)) {
                Storage::disk('public')->delete($obat->foto);
            }
            $fotoPath = $request->file('foto')->store('obat', 'public');
        } else {
            $fotoPath = $obat->foto; // tetap yang lama
        }

        $obat->update([
            'nama' => $validated['nama'],
            'kode' => $validated['kode'],
            'deskripsi' => $validated['deskripsi'],
            'harga' => $validated['harga'],
            'stok' => $validated['stok'],
            'category_id' => $category?->id,
            'foto' => $fotoPath,
            'kadaluarsa' => $validated['kadaluarsa'] ?? null,
        ]);

        return redirect()->route('obat.index')->with('success', 'Obat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Obat $obat)
    {
        if ($obat->stok > 0) {
            return redirect()->route('obat.index')
                ->with('error', 'Obat tidak bisa dihapus karena masih memiliki stok (' . $obat->stok . ').');
        }

        if ($obat->foto && Storage::disk('public')->exists($obat->foto)) {
            Storage::disk('public')->delete($obat->foto);
        }

        $obat->delete();
        return redirect()->route('obat.index')->with('success', 'Obat berhasil dihapus.');
    }
}
