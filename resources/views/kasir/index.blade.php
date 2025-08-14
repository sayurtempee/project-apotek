@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Daftar Kasir</h1>
                <p class="text-gray-500 mt-1">Total Kasir: <span class="font-medium text-green-700">{{ $kasirs->count() }}</span></p>
            </div>
            <a href="{{ route('kasir.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kasir
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full bg-white rounded shadow overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Foto</th>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Role</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kasirs as $index => $kasir)
                    <tr class="border-t">
                        <td class="p-3">{{ $index + 1 }}</td>
                        <td class="p-3">
                            @if ($kasir->foto)
                                <img src="{{ asset('storage/' . $kasir->foto) }}" alt="Foto Profil"
                                    class="w-10 h-10 rounded-full object-cover">
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $kasir->name }}</td>
                        <td class="p-3">{{ $kasir->email }}</td>
                        <td class="p-3">{{ $kasir->role }}</td>
                        <td class="p-3">
                            @if ($kasir->status)
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">Aktif</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-sm rounded-full">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="p-3 text-center flex justify-center gap-2">
                            @if (Auth::user()->role === 'admin' || Auth::id() === $kasir->id)
                                <a href="{{ route('kasir.edit', $kasir->id) }}"
                                    class="text-blue-600 hover:underline">Edit</a>
                            @endif

                            @if (Auth::user()->role === 'admin')
                                <form action="{{ route('kasir.destroy', $kasir->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($kasirs->isEmpty())
                    <tr>
                        <td colspan="3" class="text-center p-4 text-gray-500">Belum ada kasir.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
