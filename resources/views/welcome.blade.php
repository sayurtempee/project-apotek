@extends('components.app')

@section('bodyClass', 'min-h-screen bg-gray-100 flex items-center justify-center')

@section('content')
    <div class="flex flex-col md:flex-row w-full min-h-screen">

        <!-- Left Side -->
        <div
            class="bg-gradient-to-br from-green-700 to-emerald-600 text-white flex flex-col justify-center items-center p-10 md:w-1/2 w-full relative">
            <h2 class="text-4xl font-extrabold mb-6 text-center drop-shadow">
                Selamat Datang di Apotek Mii 💊
            </h2>
            <p class="text-lg mb-4 text-center leading-relaxed max-w-lg">
                Temukan obat terbaik dan layanan farmasi terpercaya untuk kesehatan Anda dan keluarga.
            </p>
            <p class="text-base text-center opacity-90">
                Kesehatan Anda adalah prioritas kami.
            </p>
        </div>

        <!-- Right Side -->
        <div class="flex flex-col justify-center items-center p-10 md:w-1/2 w-full bg-white">
            <h3 class="text-2xl font-bold text-green-700 mb-8 text-center">
                Akses Sistem Apotek
            </h3>
            <div class="flex flex-col gap-4 w-full max-w-xs">
                <a href="{{ route('login') }}"
                    class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg font-semibold text-lg text-center shadow transition transform hover:scale-105">
                    Login
                </a>
                <a href="{{ route('cart.index') }}"
                    class="bg-white border-2 border-green-700 text-green-700 hover:bg-green-50 px-6 py-3 rounded-lg font-semibold text-lg text-center shadow transition transform hover:scale-105">
                    Mulai Belanja
                </a>
            </div>
        </div>

    </div>
@endsection
