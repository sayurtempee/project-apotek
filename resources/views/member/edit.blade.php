@extends('components.app')

@section('content')
    <div class="max-w-xl mx-auto mt-10 bg-gradient-to-br from-green-50 to-white shadow-lg rounded-xl p-8 border border-green-100">
        <div class="flex items-center mb-8">
            <div class="flex-shrink-0">
                @if($member->foto)
                    <img src="{{ asset('storage/' . $member->foto) }}" alt="Foto Member" class="w-16 h-16 rounded-full object-cover border-2 border-green-300 shadow">
                @else
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-green-400 text-2xl font-bold border-2 border-green-300 shadow">
                        <span>{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>
            <div class="ml-4">
                <h2 class="text-2xl font-bold text-green-700">Edit Member</h2>
                <p class="text-gray-500">Perbarui data member di bawah ini.</p>
            </div>
        </div>

        <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama -->
            <div>
                <label for="name" class="block text-sm font-semibold text-green-700 mb-1">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $member->name) }}"
                    class="mt-1 block w-full border-green-200 rounded-lg shadow-sm focus:ring focus:ring-green-200 focus:border-green-400 px-3 py-2"
                    required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telepon -->
            <div>
                <label for="phone" class="block text-sm font-semibold text-green-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $member->phone) }}"
                    class="mt-1 block w-full border-green-200 rounded-lg shadow-sm focus:ring focus:ring-green-200 focus:border-green-400 px-3 py-2"
                    required>
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div>
                <label for="is_active" class="block text-sm font-semibold text-green-700 mb-1">Status</label>
                <select name="is_active" id="is_active"
                    class="mt-1 block w-full border-green-200 rounded-lg shadow-sm focus:ring focus:ring-green-200 focus:border-green-400 px-3 py-2">
                    <option value="1" {{ $member->is_active ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !$member->is_active ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Upload Foto Baru -->
            <div>
                <label for="foto" class="block text-sm font-semibold text-green-700 mb-1">Ganti Foto (opsional)</label>
                <input type="file" name="foto" id="foto"
                    class="block w-full text-sm text-gray-700 border border-green-200 rounded-lg cursor-pointer focus:outline-none focus:ring focus:ring-green-200"
                    accept="image/*">
                @error('foto')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tombol -->
            <div class="flex justify-between items-center mt-6">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg shadow transition duration-150 ease-in-out font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update
                </button>
                <a href="{{ route('members.index') }}" class="text-green-600 hover:underline flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
        </form>
    </div>
@endsection
