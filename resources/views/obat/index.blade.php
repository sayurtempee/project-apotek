@extends('components.app')
@include('layouts.sidebar')

@php
    use Carbon\Carbon;
@endphp
@section('content')
    <div x-data="{ open: false, kode: '' }" @keydown.escape.window="open = false">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button id="btn-scan" type="button"
                    class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                    Scan Barcode
                </button>
                <div class="relative flex-1">
                    <input id="barcode-input" type="text" placeholder="Arahkan scanner lalu tunggu otomatis"
                        class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        autocomplete="off" aria-label="Input barcode scanner" autofocus />
                    <div id="scan-feedback" class="absolute right-2 top-1/2 -translate-y-1/2 text-sm text-green-400 hidden">
                        Terscan!
                    </div>
                </div>
            </div>

            @if (Auth::user()->role === 'admin')
                <div class="flex gap-2">
                    <a href="{{ route('obat.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Tambah Obat</a>
                </div>
            @endif
        </div>

        <div id="alert-container" class="mb-4"></div>

        <!-- Notifikasi sederhana -->
        <div id="alert-container" class="mb-4"></div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full bg-white rounded shadow overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Foto Obat</th>
                    <th class="p-3 text-left">Barcode</th>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Deskripsi</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-right">Harga</th>
                    <th class="p-3 text-right">Stok</th>
                    <th class="p-3 text-right">Tanggal Kadaluarsa</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($obats as $index => $obat)
                    <tr class="border-t">
                        <td class="p-3">{{ $index + 1 }}</td>
                        <td class="p-3">
                            @if ($obat->foto)
                                <a href="{{ asset('storage/' . $obat->foto) }}" target="_blank" class="inline-block">
                                    <img src="{{ asset('storage/' . $obat->foto) }}" alt="Foto {{ $obat->nama }}"
                                        class="w-16 h-16 object-cover rounded" />
                                </a>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <button type="button" class="text-blue-600 hover:underline"
                                @click="kode='{{ $obat->kode }}'; open = true;">
                                {{ $obat->kode }}
                            </button>
                        </td>
                        <td class="p-3">{{ $obat->nama }}</td>
                        <td class="p-3">{{ $obat->deskripsi }}</td>
                        <td class="p-3">{{ $obat->category?->nama ?? '-' }}</td>
                        <td class="p-3 text-right">Rp. {{ number_format($obat->harga, 0, ',', '.') }}</td>
                        <td class="p-3 text-right">
                            @php $stok = $obat->stok; @endphp

                            @if ($stok === 0)
                                <div class="flex justify-center items-center h-8">
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 text-center">
                                        Habis
                                    </span>
                                </div>
                            @elseif ($stok <= 5)
                                <div class="flex justify-center items-center h-8">
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 text-center">
                                        Sisa: {{ $stok }}
                                    </span>
                                </div>
                            @else
                                <div class="flex justify-center items-center h-8">
                                    <span class="text-sm font-medium">{{ $stok }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            {{ $obat->kadaluarsa ? $obat->kadaluarsa->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex flex-col items-center gap-2">

                                {{-- Edit --}}
                                @if (Auth::user()->role === 'admin')
                                    <a href="{{ route('obat.edit', $obat) }}" class="text-blue-600 hover:underline">
                                        Edit
                                    </a>
                                @endif

                                {{-- Detail: buka modal --}}
                                <button type="button" class="text-green-600 hover:underline"
                                    onclick="openDetailModal({{ $obat->id }})">
                                    Detail
                                </button>

                                {{-- Tambah ke keranjang --}}
                                @if (Auth::user()->role === 'kasir')
                                    <form action="{{ route('cart.scan') }}" method="POST" class="inline add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="barcode" value="{{ $obat->kode }}">
                                        <button type="submit" @if ($obat->stok == 0 || $obat->is_expired) disabled @endif
                                            class="text-sm {{ $obat->stok == 0 || $obat->is_expired ? 'text-gray-400 cursor-not-allowed' : 'text-indigo-600 hover:underline' }}"
                                            title="{{ $obat->stok == 0 ? 'Stok habis' : ($obat->is_expired ? 'Obat kadaluarsa' : 'Tambah ke keranjang') }}">
                                            + Keranjang
                                        </button>
                                    </form>
                                @endif

                                {{-- Hapus --}}
                                @if (Auth::user()->role === 'admin')
                                    @if ($obat->stok > 0)
                                        <button class="w-28 text-gray-400 cursor-not-allowed text-sm"
                                            title="Tidak bisa dihapus: stok masih ada ({{ $obat->stok }})" disabled>
                                            Hapus
                                        </button>
                                    @else
                                        <form action="{{ route('obat.destroy', $obat) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="w-28 text-red-600 hover:underline text-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{--  Modal Detail per obat --}}
                    <div id="modal-detail-{{ $obat->id }}"
                        class="fixed inset-0 hidden items-center justify-center z-50"
                        style="background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);">
                        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative">
                            <button onclick="closeDetailModal({{ $obat->id }})"
                                class="absolute top-3 right-3 text-gray-500 hover:text-gray-800"
                                aria-label="Tutup">&times;</button>

                            <h2 class="text-xl font-semibold mb-4">Detail Obat: {{ $obat->nama }}</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    @if ($obat->foto)
                                        <img src="{{ asset('storage/' . $obat->foto) }}" alt="Foto {{ $obat->nama }}"
                                            class="w-full rounded">
                                    @else
                                        <div class="bg-gray-100 w-full h-48 flex items-center justify-center rounded">
                                            <span class="text-sm text-gray-500">Tidak ada foto</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Kode:</strong> {{ $obat->kode }}</p>
                                    <p><strong>Deskripsi:</strong> {{ $obat->deskripsi ?? '-' }}</p>
                                    <p><strong>Harga:</strong> {{ number_format($obat->harga, 0, ',', '.') }}</p>
                                    <p><strong>Stok:</strong> {{ $obat->stok }}</p>
                                    <p><strong>Kategori:</strong> {{ $obat->category?->nama ?? '-' }}</p>
                                    <p><strong>Dibuat:</strong> {{ $obat->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button onclick="closeDetailModal({{ $obat->id }})"
                                    class="px-4 py-2 bg-gray-200 rounded">Tutup</button>
                                @if ($obat->stok > 0)
                                    <form id="add-to-cart-modal-{{ $obat->id }}">
                                        @csrf
                                        <input type="hidden" name="barcode" value="{{ $obat->kode }}">
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">+
                                            Keranjang</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($obats->isEmpty())
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500">Belum ada data obat.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="mt-4">
            {{ $obats->withQueryString()->links() }}
        </div>

        {{-- Modal Barcode --}}
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            x-transition.opacity>
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 relative">
                <button @click="open = false"
                    class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">
                    &times;
                </button>
                <h2 class="text-xl font-semibold mb-4">Barcode: <span x-text="kode"></span></h2>
                <div class="flex justify-center mb-4">
                    <svg id="modal-barcode"></svg>
                </div>
                <div class="text-center">
                    <button
                        @click="
                            navigator.clipboard.writeText(kode);
                            copyMsg = 'Disalin!';
                            setTimeout(()=> copyMsg = '', 1000);
                        "
                        class="bg-blue-600 text-white px-4 py-2 rounded mr-2">
                        Salin Kode
                    </button>
                    <span x-data="{ copyMsg: '' }">
                        <span x-text="copyMsg" class="text-sm text-green-600"></span>
                    </span>
                </div>
            </div>
        </div>

        <script>
            {{--  JS Modal Barcode  --}}
            document.addEventListener('alpine:init', () => {
                Alpine.store('barcode', {
                    init() {
                        this.$watch = (prop, callback) => {
                            // dummy, just for compatibility if needed
                        };
                    }
                });
            });

            const barcodeUpdater = () => {
                const svg = document.getElementById('modal-barcode');
                if (!svg) return;
                const btns = document.querySelectorAll('button[@click]');
            };

            let lastKode = '';
            setInterval(() => {
                const kodeSpan = document.querySelector('[x-text="kode"]');
                if (!kodeSpan) return;
                const current = kodeSpan.textContent.trim();
                if (current && current !== lastKode) {
                    lastKode = current;
                    JsBarcode('#modal-barcode', current, {
                        format: 'CODE128',
                        displayValue: true,
                        fontSize: 14,
                        height: 60,
                        margin: 10
                    });
                }
            }, 200);

            {{--  JS Modal Detail  --}}
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="modal-detail-"]').forEach(modal => {
                    if (!modal.classList.contains('flex')) return;
                    const inner = modal.querySelector('> div'); // container langsung
                    if (inner && !inner.contains(e.target) && !e.target.closest('button')) {
                        closeDetailModal(modal.id.split('-').pop());
                    }
                });
            });

            function openDetailModal(id) {
                const modal = document.getElementById(`modal-detail-${id}`);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            function closeDetailModal(id) {
                const modal = document.getElementById(`modal-detail-${id}`);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            // klik di luar untuk tutup
            document.addEventListener('click', function(e) {
                document.querySelectorAll('[id^="modal-detail-"]').forEach(modal => {
                    if (!modal.classList.contains('flex')) return;
                    const inner = modal.querySelector('> div');
                    if (inner && !inner.contains(e.target) && !e.target.closest('button')) {
                        const id = modal.id.split('-').pop();
                        closeDetailModal(id);
                    }
                });
            });

            {{--  Scanner  --}}
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('barcode-input');
                const btnScan = document.getElementById('btn-scan');
                const feedback = document.getElementById('scan-feedback');
                const alertContainer = document.getElementById('alert-container');

                let buffer = '';
                let timer = null;
                const BUFFER_TIMEOUT = 150;

                // Fokus ketika tombol scan diklik
                btnScan.addEventListener('click', () => {
                    input.focus();
                    input.select();
                });

                // Tangani semua keydown untuk akumulasi
                input.addEventListener('keydown', (e) => {
                    // Jika scanner mengirim Enter: kirim buffer (tanpa include Enter)
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const code = buffer.trim() || input.value.trim();
                        clearBuffer();
                        if (code) addToCart(code);
                        return;
                    }

                    // Karakter normal: tambahkan ke buffer
                    if (e.key.length === 1) { // hanya karakter, bukan Shift/Ctrl
                        buffer += e.key;
                        restartBufferTimer();
                    }
                });

                // Fallback kalau browser tidak memicu keydown seperti di atas: juga perhatikan input event
                input.addEventListener('input', () => {
                    // jika user ketik manual, sinkronkan buffer agar tidak double
                    if (!buffer) {
                        buffer = input.value;
                        restartBufferTimer();
                    }
                });

                function restartBufferTimer() {
                    if (timer) clearTimeout(timer);
                    timer = setTimeout(() => {
                        const code = buffer.trim();
                        clearBuffer();
                        if (code) addToCart(code);
                    }, BUFFER_TIMEOUT);
                }

                function clearBuffer() {
                    buffer = '';
                    if (timer) {
                        clearTimeout(timer);
                        timer = null;
                    }
                    input.value = '';
                }

                async function addToCart(barcode) {
                    showFeedback();
                    input.disabled = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        const res = await fetch("{{ url('/cart/scan') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                ...(token ? {
                                    'X-CSRF-TOKEN': token
                                } : {}),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                barcode: barcode
                            }),
                        });

                        const data = await res.json();

                        if (res.ok) {
                            // Ambil jumlah total item dari cart
                            const totalItems = data.cart.items.reduce((sum, item) => sum + item.quantity, 0);

                            // Update badge di icon cart
                            const badge = document.getElementById('cart-badge');
                            if (badge) {
                                badge.textContent = totalItems;
                                badge.classList.remove('hidden');
                            }

                            showAlert('Ditambahkan: ' + (data.product_name || barcode), 'success');
                        } else {
                            showAlert(data.message || 'Gagal menambahkan', 'error');
                        }
                    } catch (err) {
                        console.error(err);
                        showAlert('Kesalahan jaringan saat menambah ke keranjang', 'error');
                    } finally {
                        input.disabled = false;
                        input.focus();
                    }
                }

                function showFeedback() {
                    feedback.classList.remove('hidden');
                    setTimeout(() => feedback.classList.add('hidden'), 800);
                }

                function showAlert(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-100 border-green-500 text-green-800',
                        error: 'bg-red-100 border-red-500 text-red-800',
                        info: 'bg-blue-100 border-blue-500 text-blue-800',
                    };
                    const alert = document.createElement('div');
                    alert.className =
                        `border-l-4 p-3 mb-2 rounded ${colors[type] || colors.info} flex justify-between items-center`;
                    alert.innerHTML = `
            <div class="text-sm">${message}</div>
            <button type="button" class="ml-4 font-bold">&times;</button>
        `;
                    alert.querySelector('button').addEventListener('click', () => alert.remove());
                    alertContainer.appendChild(alert);
                    setTimeout(() => alert.remove(), 5000);
                }

                input.focus();
            });

            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        const res = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok) {
                            // Update badge jumlah keranjang
                            const totalItems = data.cart.items.reduce((sum, item) => sum + item.quantity,
                                0);
                            const badge = document.getElementById('cart-badge');
                            if (badge) {
                                badge.textContent = totalItems;
                                badge.classList.remove('hidden');
                            }

                            // Tampilkan alert sukses
                            alert(`Ditambahkan: ${data.product_name}`);
                        } else {
                            alert(data.message || 'Gagal menambahkan ke keranjang');
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan koneksi');
                    }
                });
            });

            document.querySelectorAll('[id^="add-to-cart-modal-"]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("{{ route('cart.scan') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": formData.get('_token'),
                                "Accept": "application/json"
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            // Menampilkan pesan di UI
                            alert(
                                `${data.message}\nProduk: ${data.product_name}\nKadaluarsa: ${data.expires_at}`);
                        })
                        .catch(err => {
                            console.error(err);
                            alert("Gagal menambahkan ke keranjang.");
                        });
                });
            });
        </script>
    </div>
@endsection
