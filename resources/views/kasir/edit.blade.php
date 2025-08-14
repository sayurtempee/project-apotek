@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Edit Kasir</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kasir.update', $kasir->id) }}" method="POST" enctype="multipart/form-data"
            class="space-y-4 max-w-md">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="foto" class="block text-sm font-medium text-gray-700">Foto Profil</label>
                <input type="file" name="foto" id="foto" class="mt-1 block w-full">
                @if (!empty($kasir->foto))
                    <img src="{{ asset('storage/' . $kasir->foto) }}" alt="Foto Profil"
                        class="w-20 h-20 mt-2 rounded-full object-cover">
                @endif
            </div>
            <div>
                <label class="block font-medium">Nama</label>
                <input type="text" name="name" value="{{ $kasir->name }}" class="w-full border rounded px-3 py-2"
                    required>
            </div>
            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" value="{{ $kasir->email }}" class="w-full border rounded px-3 py-2"
                    required>
            </div>
            <div>
                <label class="block font-medium">Password (kosongkan jika tidak diganti)</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
                <a href="{{ route('kasir.index') }}" class="bg-gray-300 px-4 py-2 rounded">Batal</a>
            </div>
        </form>
    </div>
@endsection
