@extends('components.app')

@section('bodyClass', 'flex justify-center items-center min-h-screen bg-gray-100')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-green-50 to-green-100 px-4">
        <div class="w-full max-w-md bg-white shadow-xl rounded-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-4 text-center">
                <h2 class="text-2xl font-bold text-white">Lupa Password</h2>
                <p class="text-green-100 text-sm mt-1">Masukkan email Anda untuk reset password</p>
            </div>

            {{-- Body --}}
            <div class="p-6 sm:p-8">
                {{-- Pesan sukses --}}
                @if (session('status'))
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        ✅ {{ session('status') }}
                    </div>
                @endif

                {{-- Pesan error --}}
                @if ($errors->any())
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>⚠️ {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form kirim email reset password --}}
                <form method="POST" action="{{ route('forgot.password') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                        <input type="email" id="email" name="email" required autofocus
                            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                            placeholder="contoh: user@email.com">
                    </div>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-green-600 text-white py-2.5 px-4 rounded-lg shadow hover:bg-green-700 focus:ring-2 focus:ring-green-400 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12H8m8 0l-4 4m4-4l-4-4" />
                        </svg>
                        Kirim Link Reset Password
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}"
                        class="inline-block text-sm text-green-600 hover:text-green-700 hover:underline transition">
                        ← Kembali ke login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
