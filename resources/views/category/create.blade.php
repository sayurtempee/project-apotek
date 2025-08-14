@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Tambah Kategori</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-lg">
        @csrf
        <div class="mb-3">
            <label for="foto" class="form-label">Icon</label>
            <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
            @error('foto')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block font-medium">Nama</label>
            <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block font-medium">Slug (opsional)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full border p-2 rounded">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Simpan</button>
            <a href="{{ route('category.index') }}" class="bg-gray-300 px-6 py-2 rounded">Batal</a>
        </div>
    </form>
@endsection
