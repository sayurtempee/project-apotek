<head>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
</head>
<header class="fixed top-0 left-0 right-0 bg-[#2E7D32] shadow-md z-50 h-16 flex items-center justify-between p-4">
    <div class="container mx-auto flex justify-between items-center px-4">
        <!-- Logo -->
        <div class="text-4xl font-bold italic">
            <span class="text-white cursor-pointer" onclick="window.location.href='/'">
                Apotek
            </span>
            <span class="text-yellow-300 cursor-pointer" onclick="window.location.href='/'">
                .Mii
            </span>
        </div>

        <!-- Tombol LOGIN -->
        <a href="{{ route('login') }}"
            class="group bg-gradient-to-r from-green-700 via-green-800 to-green-900 text-white px-6 py-2 rounded-full text-base font-semibold shadow-lg transition duration-300 ease-in-out transform relative z-50 overflow-hidden focus:outline-none focus:ring-2 focus:ring-yellow-400"
        >
            <span class="absolute inset-0 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-in-out opacity-80"></span>
            <span class="relative flex items-center">
            <svg class="inline-block w-5 h-5 mr-2 -mt-1 transition-transform duration-300 group-hover:translate-x-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4-4m-4 4l4 4m13-4a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="transition-colors duration-300 group-hover:text-green-900">LOGIN</span>
            </span>
        </a>
    </div>
</header>
