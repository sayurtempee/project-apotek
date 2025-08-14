@extends('components.app')

@section('bodyClass', 'flex justify-center items-center min-h-screen bg-gray-100')

@section('content')
    <div class="bg-white rounded shadow-md w-[900px] flex overflow-hidden mx-auto">
        <!-- Left Side -->
        <div class="bg-green-700 text-white flex flex-col justify-center items-center w-1/2 p-12">
            <h2 class="text-4xl font-bold mb-6">Selamat Datang!</h2>
            <p class="text-xl mb-4 text-center">Silakan login untuk mengakses sistem Apotek.</p>
            <p class="text-base text-center">Pastikan data Anda aman dan rahasia.</p>
        </div>
        <!-- Right Side -->
        <div class="w-1/2 p-12 flex flex-col justify-center">
            <h2 class="text-3xl font-bold mb-6 text-center">
                <a href="{{ route('home') }}" class="text-green-700 hover:underline">Login</a>
            </h2>
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
            <form action="{{ route('login') }}" method="POST">
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
                    class="bg-green-700 hover:bg-green-700 text-white px-6 py-3 rounded w-full font-semibold text-lg">
                    Login
                </button>
            </form>
        </div>
    </div>
@endsection
