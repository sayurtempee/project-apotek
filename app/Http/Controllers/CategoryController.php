<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('obats') // hitung jumlah obat di tiap kategori
            ->orderBy('nama')
            ->paginate(10);

        return view('category.index', compact('categories'), [
            'title' => 'Daftar Kategori',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create', [
            'title' => 'Tambah Kategori',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:categories,nama',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'foto' => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('category', 'public');
        }

        Category::create([
            'nama' => $validated['nama'],
            'slug' => $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['nama']),
            'foto' => $fotoPath,
        ]);
        return redirect()->route('category.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('category.show', [
            'category' => $category,
            'title' => 'Detail Kategori',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('category.edit', [
            'category' => $category,
            'title' => 'Edit Kategori',
            'project' => 'Apotek Mii',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:categories,nama,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'foto' => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('category', 'public');
        }

        $category->update([
            'nama' => $validated['nama'],
            'slug' => $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['nama']),
            'foto' => $fotoPath,
        ]);
        return redirect()->route('category.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->obats()->exists()) {
            return redirect()
                ->route('category.index')
                ->with('error', 'Kategori ini sedang digunakan oleh obat dan tidak bisa dihapus.');
        }

        $category->delete();
        return redirect()
            ->route('category.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
