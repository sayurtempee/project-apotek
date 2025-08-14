@extends('components.app')

@section('content')
    <div class="max-w-xl mx-auto mt-10 bg-white shadow-md rounded p-6">
        <h2 class="text-2xl font-semibold mb-6">Edit Member</h2>

        <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama -->
            <div class="mb-3">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $member->name) }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-300"
                    required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telepon -->
            <div class="mb-3">
                <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $member->phone) }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-300"
                    required>
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="mb-3">
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="is_active" id="is_active"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-300">
                    <option value="1" {{ $member->is_active ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !$member->is_active ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Upload Foto Baru -->
            <div class="mb-3">
                <label for="foto" class="form-label">Ganti Foto (opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror"
                    accept="image/*">
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tombol -->
            <div class="flex justify-between">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">Update</button>
                <a href="{{ route('members.index') }}" class="text-gray-600 hover:underline">← Kembali</a>
            </div>
        </form>
    </div>
@endsection
