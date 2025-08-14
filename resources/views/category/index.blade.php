@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Daftar Kategori</h1>
        <a href="{{ route('category.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Tambah Kategori</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <table class="w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">#</th>
                <th class="p-3 text-left">Icon</th>
                <th class="p-3 text-left">Nama</th>
                <th class="p-3 text-left">Slug</th>
                <th class="p-3 text-left">Obat</th>
                <th class="p-3 text-left">Tanggal Dibuat</th>
                <th class="p-3 text-left">Tanggal Diupdate</th>
                <th class="p-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $index => $category)
                <tr class="border-t">
                    <td class="p-3">{{ $index + 1 }}</td>
                    <td class="p-3">
                        @if ($category->foto)
                            <img src="{{ asset('storage/' . $category->foto) }}" alt="" class="w-10 h-10 object-cover rounded-full" style="aspect-ratio: 1 / 1;">
                        @else
                            <span>NO IMAGE</span>
                        @endif
                    </td>
                    <td class="p-3">{{ $category->nama }}</td>
                    <td class="p-3">{{ $category->slug }}</td>
                    <td class="p-3">{{ $category->obats_count }} Obat</td>
                    <td class="p-3">{{ $category->created_at->locale('id')->translatedFormat('l, d-m-Y') }}</td>
                    <td class="p-3">
                        {{ $category->updated_at ? $category->updated_at->locale('id')->translatedFormat('l, d-m-Y') : '-' }}
                    </td>
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ route('category.edit', $category) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('category.destroy', $category) }}" method="POST" class="inline"
                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">Belum ada kategori.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $categories->withQueryString()->links() }}
    </div>
@endsection
