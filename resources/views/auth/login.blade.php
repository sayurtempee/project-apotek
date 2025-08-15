@extends('components.app')

@section('bodyClass', 'flex justify-center items-center min-h-screen bg-gray-100')

@section('content')
    <div class="flex flex-col md:flex-row w-full min-h-screen">

        <!-- Left Side (Login Form) -->
        <div class="w-full md:w-1/2 p-12 flex flex-col justify-center bg-white">
            <h2 class="text-3xl font-bold mb-6 text-center text-green-700"><a href="{{ route('home') }}"
                    class="text-green-700 hover:underline">Login</a></h2>

            @if (session('error'))
                <div class="bg-red-100 text-red-700 p-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="max-w-md mx-auto w-full">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 font-semibold">Email</label>
                    <input type="email" name="email"
                        class="border w-full p-3 rounded focus:outline-none focus:ring-2 focus:ring-green-700" required
                        autofocus>
                </div>
                <div class="mb-6">
                    <label class="block mb-2 font-semibold">Password</label>
                    <input type="password" name="password"
                        class="border w-full p-3 rounded focus:outline-none focus:ring-2 focus:ring-green-700" required>
                </div>
                <button type="submit"
                    class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded w-full font-semibold text-lg transition">
                    Login
                </button>
            </form>
        </div>

        <!-- Right Side (Welcome Message) -->
        <div class="bg-green-700 text-white flex flex-col justify-center items-center w-full md:w-1/2 p-12">
            <h2 class="text-4xl font-bold mb-6 text-center">Selamat Datang!</h2>
            <p class="text-xl mb-4 text-center">Silakan login untuk mengakses sistem Apotek.</p>
            <p class="text-base text-center">Pastikan data Anda aman dan rahasia.</p>
        </div>
    </div>
@endsection
