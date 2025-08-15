@extends('components.app')

@section('bodyClass', 'min-h-screen bg-gray-100 flex items-center justify-center')

@section('content')
    <div class="flex flex-col md:flex-row w-full min-h-screen">

        <!-- Left Side -->
        <div class="bg-green-700 text-white flex flex-col justify-center items-center p-10 md:w-1/2 w-full">
            <h2 class="text-4xl font-bold mb-6 text-center">
                Selamat Datang di Apotek Mii 💊
            </h2>
            <p class="text-lg mb-4 text-center leading-relaxed">
                Temukan obat terbaik dan layanan farmasi terpercaya untuk kesehatan Anda dan keluarga.
            </p>
            <p class="text-base text-center opacity-80">
                Kesehatan Anda adalah prioritas kami.
            </p>
        </div>

        <!-- Right Side -->
        <div class="flex flex-col justify-center items-center p-10 md:w-1/2 w-full bg-white">
            <h3 class="text-2xl font-semibold text-green-700 mb-8 text-center">
                Akses Sistem Apotek
            </h3>
            <div class="flex flex-col gap-4 w-full max-w-xs">
                <a href="{{ route('login') }}"
                    class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded font-semibold text-lg text-center shadow transition duration-200">
                    Login
                </a>
                <a href="{{ route('cart.index') }}"
                    class="bg-white border border-green-700 text-green-700 hover:bg-green-50 px-6 py-3 rounded font-semibold text-lg text-center shadow transition duration-200">
                    Mulai Belanja
                </a>
            </div>
        </div>

    </div>
@endsection
