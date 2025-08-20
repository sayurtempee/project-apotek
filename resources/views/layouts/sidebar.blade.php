<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apotek.Mii</title>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js" defer></script>

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        aside a {
            text-decoration: none !important;
        }

        aside a:hover {
            text-decoration: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <!-- HEADER -->
    <header class="fixed top-0 left-0 right-0 bg-green-800 shadow-md z-10 h-16 flex items-center">
        <div class="container mx-auto flex justify-between items-center px-6">
            <!-- Logo -->
            <div class="flex items-center space-x-2 cursor-pointer" onclick="window.location.href='/dashboard'">
                <span class="text-4xl font-extrabold italic text-white">Apotek</span>
                <span class="text-4xl font-extrabold italic text-yellow-300">.Mii</span>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center space-x-4">
                <!-- Cart Icon -->
                @if (Auth::user()->role === 'kasir')
                    <a href="{{ route('cart.index') }}" title="Lihat keranjang">
                        <div
                            class="w-10 h-10 bg-white border-2 border-[#2E7D32] rounded-full flex items-center justify-center relative shadow hover:scale-105 transition">
                            <i class="fas fa-shopping-cart text-[#2E7D32] text-lg"></i>
                            @if (!empty($cartCount) && $cartCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5 font-bold shadow">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endif

                <!-- Profile -->
                <div x-data="{ open: false }" class="relative">
                    <!-- Avatar -->
                    <button @click.stop="open = !open" type="button"
                        class="w-10 h-10 rounded-full overflow-hidden cursor-pointer border-2 border-white shadow-lg flex items-center justify-center bg-[#388E3C] text-white font-bold text-lg hover:ring-2 hover:ring-yellow-300 transition">
                        @if (Auth::user()->foto)
                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="photo-profile"
                                class="w-full h-full object-cover">
                        @else
                            <div
                                class="w-10 h-10 flex items-center justify-center text-white text-lg font-bold rounded-full">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false" x-transition
                        class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                        <a href="{{ route('profile.index') }}"
                            class="block px-5 py-3 text-gray-700 hover:bg-[#2E7D32]/10 font-semibold transition">
                            <i class="fas fa-user mr-2 text-[#2E7D32]"></i> Profile
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-left block px-5 py-3 text-gray-700 hover:bg-[#2E7D32]/10 font-semibold transition">
                                <i class="fas fa-sign-out-alt mr-2 text-red-600"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside x-data="{ sidebarOpen: true }"
        class="lg:block fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-green-900 p-6 overflow-y-auto z-20 shadow-md rounded-br-xl border-r border-green-700/50">

        <div class="space-y-8">
            <!-- Management -->
            <div>
                <h2 class="text-white font-bold mb-4 text-lg tracking-wide flex items-center gap-2">
                    <i class="fas fa-cogs text-yellow-200"></i> Management
                </h2>
                <div class="space-y-2">
                    <!-- Obat -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                            <span class="flex items-center gap-2"><i class="fas fa-pills"></i> Obat</span>
                            <i class="fas fa-chevron-down transition-transform duration-300"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                            <a href="{{ route('obat.index') }}"
                                class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Daftar
                                Obat</a>
                            @if (Auth::user()->role === 'admin')
                                <a href="{{ route('obat.create') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Tambah
                                    Obat</a>
                            @endif
                        </div>
                    </div>

                    <!-- Kategori -->
                    @if (Auth::user()->role === 'admin')
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                                <span class="flex items-center gap-2"><i class="fas fa-tags"></i> Kategori Obat</span>
                                <i class="fas fa-chevron-down transition-transform duration-300"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                                <a href="{{ route('category.index') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Daftar
                                    Kategori Obat</a>
                                <a href="{{ route('category.create') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Tambah
                                    Kategori Obat</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin/Kasir -->
            <div>
                @if (Auth::user()->role === 'admin')
                    <h2 class="text-white font-bold mb-4 text-lg tracking-wide flex items-center gap-2">
                        <i class="fas fa-user-shield text-yellow-200"></i> Admin
                    </h2>
                @endif
                @if (Auth::user()->role === 'kasir')
                    <h2 class="text-white font-bold mb-4 text-lg tracking-wide flex items-center gap-2">
                        <i class="fas fa-cash-register text-yellow-200"></i> Kasir
                    </h2>
                @endif

                <div class="space-y-2">
                    <!-- Users (Admin) -->
                    @if (Auth::user()->role === 'admin')
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                                <span class="flex items-center gap-2"><i class="fas fa-users"></i> Users</span>
                                <i class="fas fa-chevron-down transition-transform duration-300"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                                <a href="{{ route('admin.index') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Daftar
                                    Admin</a>
                                <a href="{{ route('kasir.index') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Daftar
                                    Kasir</a>
                                <a href="{{ route('kasir.create') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Tambah
                                    Kasir</a>
                            </div>
                        </div>
                    @endif

                    <!-- Member -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                            <span class="flex items-center gap-2"><i class="fas fa-id-card"></i> Member</span>
                            <i class="fas fa-chevron-down transition-transform duration-300"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                            @if (Auth::user()->role === 'admin')
                                <a href="{{ route('members.index') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Daftar
                                    Member</a>
                            @endif
                            @if (Auth::user()->role === 'kasir')
                                <a href="{{ route('members.create') }}"
                                    class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Tambah
                                    Member</a>
                            @endif
                        </div>
                    </div>

                    <!-- Transaksi -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                            <span class="flex items-center gap-2"><i class="fas fa-receipt"></i> Transaksi</span>
                            <i class="fas fa-chevron-down transition-transform duration-300"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                            <a href="{{ route('transaction.history') }}"
                                class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Riwayat
                                Transaksi</a>
                        </div>
                    </div>

                    <!-- Laporan -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full bg-white text-[#2E7D32] font-semibold py-2 px-4 rounded-lg flex justify-between items-center shadow hover:bg-gray-100 transition">
                            <span class="flex items-center gap-2"><i class="fas fa-file-download"></i> Laporan</span>
                            <i class="fas fa-chevron-down transition-transform duration-300"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 pl-4 space-y-2 origin-top">
                            <a href="{{ route('orders.download.pdf') }}"
                                class="block text-white hover:bg-yellow-500 hover:text-gray-800 rounded-md px-2 py-1 transition">Download
                                Semua Transaksi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</body>

</html>
