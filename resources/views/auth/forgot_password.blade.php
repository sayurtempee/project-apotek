{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('components.app')

@section('bodyClass', 'flex justify-center items-center min-h-screen bg-gray-100')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
            <h2 class="text-2xl font-bold text-center text-green-700 mb-6">Lupa Password</h2>

            {{-- Pesan sukses / error --}}
            @if (session('status'))
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form kirim email reset password --}}
            <form method="POST" action="{{ route('forgot.password') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input type="email" id="email" name="email" required autofocus
                        class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                        placeholder="masukkan email anda">
                </div>

                <button type="submit"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg shadow hover:bg-green-700 transition">
                    Kirim Link Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-green-700 hover:underline">
                    Kembali ke login
                </a>
            </div>
        </div>
    </div>
@endsection
