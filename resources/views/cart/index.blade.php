@extends('components.app')

@section('content')
    <div class="container py-4">
        <a href="{{ route('obat.index') }}" class="btn btn-link text-decoration-none mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        <div class="row g-4">
            <!-- Daftar Barang -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div
                        class="card-header bg-gradient bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja
                        </h5>
                        @if ($cart->expires_at && $cart->items->count() > 0)
                            <span class="badge bg-warning text-dark">
                                Hapus item bisa setelah: <span id="countdown"></span>
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @forelse ($cart->items as $item)
                            <div
                                class="d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="checkbox" class="form-check-input check-item" data-id="{{ $item->id }}"
                                        {{ $item->is_checked ? 'checked' : '' }}>
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{ $item->obat->nama }}</h6>
                                        <small class="text-muted">Rp
                                            {{ number_format($item->obat->harga, 0, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="mt-3 mt-md-0 d-flex align-items-center gap-2">
                                    <form action="{{ route('cart.item.update', $item->id) }}" method="POST"
                                        class="d-flex align-items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="quantity" value="{{ old('quantity', $item->quantity) }}"
                                            min="1" max="{{ $item->obat->stok }}"
                                            class="form-control form-control-sm text-center" style="width: 70px">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                    <span class="fw-bold text-primary">Rp
                                        {{ number_format($item->line_total, 0, ',', '.') }}</span>
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger delete-btn"
                                            {{ !$cart->isExpired() ? 'disabled' : '' }}>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p class="mb-0">Keranjang kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pembayaran -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('checkout') }}" method="POST" id="checkout-form" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold">No. HP Member</label>
                                <div class="input-group">
                                    <input type="text" name="phone" id="phone" class="form-control"
                                        placeholder="Masukkan nomor HP" value="{{ old('phone', $phone ?? '') }}">
                                    <button type="button" class="btn btn-primary" id="btn-search-member"
                                        title="Cari Member">
                                        <i class="bi bi-search-heart"></i>
                                    </button>
                                </div>
                                <div id="member-result" class="mt-3"></div>
                            </div>

                            <input type="hidden" name="pakai_diskon" id="input-pakai_diskon" value="0">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Total</label>
                                <input type="text" class="form-control bg-light fw-bold" id="total"
                                    value="{{ $grandTotal }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="paid_amount" class="form-label fw-bold">Jumlah Bayar</label>
                                <input type="number" name="paid_amount" class="form-control" id="paid_amount" required
                                    min="0" step="1">
                                <div class="invalid-feedback" id="paid-error">Jumlah bayar kurang dari total.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kembalian</label>
                                <input type="text" class="form-control bg-light fw-bold" id="change" readonly>
                            </div>

                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hitung kembalian otomatis
        document.getElementById('paid_amount').addEventListener('input', function() {
            const total = parseFloat(document.getElementById('total').value) || 0;
            const paid = parseFloat(this.value) || 0;
            const change = paid - total;
            document.getElementById('change').value = change > 0 ? change : 0;

            // Reset validasi saat input berubah
            this.classList.remove('is-invalid');
            document.getElementById('paid-error').style.display = 'none';
        });

        // Pencarian member dengan AJAX saat tombol cari ditekan
        document.getElementById('btn-search-member').addEventListener('click', function() {
            const phoneInput = document.getElementById('phone');
            const phone = phoneInput.value.trim();
            const resultDiv = document.getElementById('member-result');

            if (!phone) {
                alert('Masukkan nomor HP!');
                return;
            }

            fetch(`{{ route('member.search') }}?phone=${phone}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'found') {
                        if (!data.is_active) {
                            resultDiv.innerHTML = `
                            <div class="alert alert-warning">
                                Member tidak aktif.<br>
                                Untuk mengaktifkan, silahkan lakukan transaksi minimal Rp 50.000.
                            </div>
                        `;
                            document.getElementById('input-pakai_diskon').value = '0';
                            return;
                        }

                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Member: ${data.nama}</strong><br>
                            Poin: ${data.poin}
                        </div>
                        <button type="button" class="btn btn-success w-100" id="btn-use-points">
                            Gunakan Member
                        </button>
                    `;

                        // Pasang event listener tombol Gunakan Poin Diskon
                        document.getElementById('btn-use-points').addEventListener('click', function() {
                            const totalInput = document.getElementById('total');
                            const poin = parseFloat(data.poin) || 0;
                            const currentTotal = parseFloat(totalInput.value) || 0;
                            let newTotal = currentTotal - (poin * 100);
                            totalInput.value = newTotal > 0 ? newTotal.toFixed(0) : 0;

                            // Set flag diskon
                            document.getElementById('input-pakai_diskon').value = '1';

                            alert('Poin diskon berhasil diterapkan!');
                        });

                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Member tidak ditemukan</div>`;
                        // Reset flag diskon
                        document.getElementById('input-pakai_diskon').value = '0';
                    }
                })
                .catch(() => {
                    alert('Terjadi kesalahan saat mencari member.');
                });
        });

        // Validasi jumlah bayar sebelum submit form checkout
        document.getElementById('checkout-form').addEventListener('submit', function(event) {
            const total = parseFloat(document.getElementById('total').value) || 0;
            const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;

            if (paidAmount < total) {
                event.preventDefault(); // cegah form submit
                const paidInput = document.getElementById('paid_amount');
                paidInput.classList.add('is-invalid');
                document.getElementById('paid-error').style.display = 'block';
                paidInput.focus();
            }
        });

        // Countdown hapus item
        @if ($cart->expires_at)
            const expiryTime = new Date("{{ $cart->expires_at->toIso8601String() }}").getTime();
            const countdownEl = document.getElementById('countdown');
            const deleteBtns = document.querySelectorAll('.delete-btn');

            // Saat awal load, disable tombol hapus
            deleteBtns.forEach(btn => btn.disabled = true);

            const timer = setInterval(() => {
                const now = new Date().getTime();
                const distance = expiryTime - now;

                if (distance <= 0) {
                    clearInterval(timer);
                    countdownEl.textContent = "Waktu habis, item otomatis terhapus.";

                    fetch("{{ route('cart.clear-expired', $cart->id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload()); // reload halaman agar item hilang
                } else {
                    const minutes = Math.floor(distance / 1000 / 60);
                    const seconds = Math.floor((distance / 1000) % 60);
                    countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        @endif

        document.querySelectorAll('.check-item').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.id;
                const isChecked = this.checked ? 1 : 0;

                fetch(`{{ url('/cart/item') }}/${itemId}/toggle-check`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            is_checked: isChecked
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Gagal menyimpan status ceklis.');
                        }
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan koneksi.');
                    });
            });
        });
    </script>
@endsection
