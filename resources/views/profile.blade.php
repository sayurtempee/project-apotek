@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="p-6 max-w-xl mx-auto bg-white shadow-md rounded-lg">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">Profil Saya</h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4 shadow-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 flex flex-col items-center gap-4">
            @if (Auth::user()->foto)
                <img src="{{ asset('storage/' . Auth::user()->foto) }}"
                    class="w-28 h-28 rounded-full object-cover border-2 border-green-600 shadow-md">
            @else
                <div
                    class="w-28 h-28 bg-gray-300 rounded-full flex items-center justify-center text-2xl font-bold text-gray-700 shadow-inner">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endif
        </div>

        @if (Auth::user()->role === 'kasir')
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Nama</label>
                    <input type="text" name="name" value="{{ Auth::user()->name }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                        required>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ Auth::user()->email }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                        required>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Password Baru (Opsional)</label>
                    <input type="password" name="password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Foto Profil (Opsional)</label>
                    <input type="file" name="foto" class="w-full">
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow-md transition-all">
                    Simpan Perubahan
                </button>
            </form>
        @else
            <div class="space-y-2 bg-gray-50 p-4 rounded-lg shadow-inner">
                <p><strong>Nama:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
            </div>
        @endif
    </div>
@endsection
