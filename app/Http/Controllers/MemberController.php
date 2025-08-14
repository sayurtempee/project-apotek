<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = Member::all();
        $title = 'Daftar Member';
        $project = 'Apotek Mii';
        return view('member.index', compact('members', 'title', 'project'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Tambah Member';
        $project = 'Apotek Mii';
        return view('member.create', compact('title', 'project'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:members,phone',
            'points' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'foto' => 'nullable|image|max:2048'
        ]);

        // Set default values jika tidak dikirim dari form
        $validated['points'] = $validated['points'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? $validated['is_active'] : true;

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('member', 'public');
        }

        Member::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'points' => $validated['points'],
            'is_active' => $validated['is_active'],
            'foto' => $fotoPath
        ]);
        return redirect()->route('dashboard')->with('success', 'Member Berhasil Ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        return view('member.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $title = 'Edit Member';
        $project = 'Apotek Mii';
        return view('member.edit', compact('member', 'title', 'project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:members,phone,' . $member->id,
            'is_active' => 'nullable|boolean',
            'foto' => 'nullable|image|max:2048'
        ]);

        $validated['is_active'] = $request->has('is_active') ? $validated['is_active'] : true;

        // Variabel fotoPath default ke foto lama kalau edit
        $fotoPath = $member->foto ?? null;

        // Kalau ada upload foto baru, simpan dan update fotoPath
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($member->foto && Storage::exists('public/' . $member->foto)) {
                Storage::delete('public/' . $member->foto);
            }
            $fotoPath = $request->file('foto')->store('member', 'public');
        }

        $member->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'],
            'foto' => $fotoPath
        ]);
        return redirect()->route('members.index')->with('success', 'Member berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.l
     */
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $phone = $request->get('phone');

        $member = Member::where('phone', $phone)->first();

        if ($member) {
            return response()->json([
                'status' => 'found',
                'nama' => $member->name,
                'poin' => $member->points,
                'is_active' => $member->is_active,
            ]);
        }

        return response()->json([
            'status' => 'not_found'
        ]);
    }
}
