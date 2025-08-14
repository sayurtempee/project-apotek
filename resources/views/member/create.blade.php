@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="container mx-auto p-6 max-w-xl">
        <div class="bg-white shadow-md rounded-xl p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tambah Member</h2>

            <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="foto" class="block text-sm font-semibold text-gray-700 mb-1">Foto Member</label>
                <input type="file" name="foto" id="foto" accept="image/*"
                class="block w-full text-sm text-gray-700 border border-gray-300 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('foto')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                class="border px-3 py-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }}">
                @error('name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-6">
                <label for="phone" class="block text-sm font-semibold text-gray-700">Nomor HP</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                placeholder="08xxxxxxxxxx"
                class="border px-3 py-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('phone') ? 'border-red-500' : '' }}">
                @error('phone')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Tambah Member
            </button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form[action="{{ route('members.store') }}"]').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^08[0-9]{8,10}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Nomor HP harus diawali 08 dan memiliki 10-12 digit.');
            }
        });
    </script>
@endsection
