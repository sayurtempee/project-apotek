@extends('components.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container py-5">
        <!-- Back Button -->
        <a href="{{ route('obat.index') }}" class="btn btn-outline-secondary rounded-pill px-4 mb-4 shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        <div class="row g-4">
            <!-- Daftar Barang -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg h-100">
                    <div
                        class="card-header bg-gradient bg-primary text-white py-3 d-flex justify-content-between align-items-center rounded-top">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-cart-check me-2"></i> Keranjang Belanja
                        </h5>
                        @if ($cart->expires_at && $cart->items->count() > 0)
                            <span class="badge bg-warning text-dark p-2 rounded-pill shadow-sm">
                                <i class="bi bi-clock-history me-1"></i>
                                Hapus item bisa setelah: <span id="countdown"></span>
                            </span>
                        @endif
                    </div>

                    <div class="card-body p-0">
                        @forelse ($cart->items as $item)
                            <div
                                class="d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 border-bottom cart-row bg-light-hover">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="checkbox" class="form-check-input check-item" data-id="{{ $item->id }}"
                                        {{ $item->is_checked ? 'checked' : '' }}>
                                    <div>
                                        <h6 class="mb-1 fw-semibold text-dark">{{ $item->obat->nama }}</h6>
                                        <small class="text-muted">Rp
                                            {{ number_format($item->obat->harga, 0, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="mt-3 mt-md-0 d-flex align-items-center gap-2">
                                    <form action="{{ route('cart.item.update', $item->id) }}" method="POST"
                                        class="d-flex align-items-center gap-2 quantity-form">
                                        @csrf @method('PUT')
                                        <input type="number" name="quantity" value="{{ old('quantity', $item->quantity) }}"
                                            min="1" max="{{ $item->obat->stok }}"
                                            class="form-control form-control-sm text-center rounded-pill shadow-sm"
                                            style="width: 80px">
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                    <span class="fw-bold text-primary line-total">
                                        Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                    </span>
                                    {{--  <form action="{{ route('cart.remove', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus item ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill delete-btn"
                                            {{ !$cart->isExpired() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>  --}}
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-box-seam display-6 mb-2"></i>
                                <p class="mb-0 fw-semibold">Keranjang kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pembayaran -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg h-100">
                    <div class="card-header bg-gradient bg-success text-white py-3 rounded-top">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-credit-card me-2"></i> Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('checkout') }}" method="POST" id="checkout-form" novalidate>
                            @csrf
                            <!-- Member -->
                            <div class="mb-4">
                                <label for="phone" class="form-label fw-bold">No. HP Member</label>
                                <div class="input-group">
                                    <input type="text" name="phone" id="phone"
                                        class="form-control rounded-start-pill" placeholder="Masukkan nomor HP"
                                        value="{{ old('phone', $phone ?? '') }}">
                                    <button type="button" class="btn btn-primary rounded-end-pill" id="btn-search-member"
                                        title="Cari Member">
                                        <i class="bi bi-search-heart"></i>
                                    </button>
                                </div>
                                <div id="member-result" class="mt-3"></div>
                            </div>

                            <input type="hidden" name="pakai_diskon" id="input-pakai_diskon" value="0">

                            <!-- Total -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Total</label>
                                <input type="text" class="form-control bg-light fw-bold rounded-pill" id="total"
                                    value="{{ $grandTotal }}" readonly>
                            </div>

                            <!-- Jumlah Bayar -->
                            <div class="mb-3">
                                <label for="paid_amount" class="form-label fw-bold">Jumlah Bayar</label>
                                <input type="number" name="paid_amount" class="form-control rounded-pill" id="paid_amount"
                                    required min="0" step="1">
                                <div class="invalid-feedback" id="paid-error">Jumlah bayar kurang dari total.</div>
                            </div>

                            <!-- Kembalian -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Kembalian</label>
                                <input type="text" class="form-control bg-light fw-bold rounded-pill" id="change"
                                    readonly>
                            </div>

                            <!-- Checkout Button -->
                            <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill shadow-sm py-2">
                                <i class="bi bi-check-circle me-1"></i> Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const totalInput = document.getElementById('total');
        const paidInput = document.getElementById('paid_amount');
        const changeInput = document.getElementById('change');
        let memberPoints = 0;
        let usePoints = false;

        // Hitung total & kembalian
        function updateTotal() {
            let subtotal = 0;
            document.querySelectorAll('.check-item').forEach(cb => {
                if (cb.checked) {
                    const row = cb.closest('.cart-row');
                    const lineTotalEl = row.querySelector('.line-total');
                    const lineTotal = parseFloat(lineTotalEl.textContent.replace(/\D/g, '')) || 0;
                    subtotal += lineTotal;
                }
            });
            let total = subtotal;
            if (usePoints && memberPoints > 0) {
                total -= memberPoints * 100;
                if (total < 0) total = 0;
            }
            totalInput.value = total.toFixed(0);
            const paid = parseFloat(paidInput.value) || 0;
            changeInput.value = (paid - total > 0 ? (paid - total).toFixed(0) : 0);
        }
        updateTotal();
        paidInput.addEventListener('input', updateTotal);

        // Toggle check item
        document.querySelectorAll('.check-item').forEach(cb => {
            cb.addEventListener('change', function() {
                const id = this.dataset.id;
                fetch(`/cart/item/${id}/toggle-check`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            is_checked: this.checked ? 1 : 0
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) alert(data.message || 'Gagal menyimpan status ceklis.');
                        updateTotal();
                    })
                    .catch(() => alert('Kesalahan koneksi.'));
            });
        });

        // Cari member & pakai poin
        document.getElementById('btn-search-member').addEventListener('click', () => {
            const phone = document.getElementById('phone').value.trim();
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
                            resultDiv.innerHTML =
                                `<div class="alert alert-warning">Member tidak aktif. Transaksi minimal Rp 50.000 untuk aktivasi.</div>`;
                            document.getElementById('input-pakai_diskon').value = '0';
                            usePoints = false;
                            memberPoints = 0;
                            updateTotal();
                            return;
                        }
                        resultDiv.innerHTML =
                            `<div class="alert alert-success"><strong>Member: ${data.nama}</strong><br>Poin: ${data.poin}</div>
            <button type="button" class="btn btn-success w-100" id="btn-use-points" data-points="${data.poin}">Gunakan Poin</button>`;
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Member tidak ditemukan</div>`;
                        document.getElementById('input-pakai_diskon').value = '0';
                        usePoints = false;
                        memberPoints = 0;
                        updateTotal();
                    }
                }).catch(() => alert('Kesalahan saat mencari member'));
        });

        // Gunakan poin
        document.addEventListener('click', e => {
            if (e.target && e.target.id === 'btn-use-points') {
                usePoints = true;
                memberPoints = parseFloat(e.target.dataset.points) || 0;
                document.getElementById('input-pakai_diskon').value = '1';
                updateTotal();
                alert('Poin diskon diterapkan!');
            }
        });

        // Validasi bayar sebelum submit
        document.getElementById('checkout-form').addEventListener('submit', e => {
            const total = parseFloat(totalInput.value) || 0;
            const paid = parseFloat(paidInput.value) || 0;
            if (paid < total) {
                e.preventDefault();
                paidInput.classList.add('is-invalid');
                document.getElementById('paid-error').style.display = 'block';
                paidInput.focus();
            }
        });

        // Update quantity via AJAX
        document.querySelectorAll('.quantity-form').forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();
                const data = new FormData(form);
                const url = form.action;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: data
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const row = form.closest('.cart-row');
                            row.querySelector('.line-total').textContent = 'Rp ' + Number(res
                                .line_total).toLocaleString('id', {
                                minimumFractionDigits: 0
                            });
                            totalInput.value = res.cart_total.toFixed(0);
                            const paid = parseFloat(paidInput.value) || 0;
                            changeInput.value = (paid - res.cart_total > 0 ? (paid - res.cart_total)
                                .toFixed(0) : 0);
                        } else {
                            alert(res.message || 'Gagal update quantity.');
                        }
                    }).catch(() => alert('Kesalahan koneksi.'));
            });
        });

        // Countdown hapus item
        @if ($cart->expires_at)
            const expiryTime = new Date("{{ $cart->expires_at->toIso8601String() }}").getTime();
            const countdownEl = document.getElementById('countdown');
            const deleteBtns = document.querySelectorAll('.delete-btn');
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
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(() => location.reload());
                } else {
                    const minutes = Math.floor(distance / 1000 / 60);
                    const seconds = Math.floor((distance / 1000) % 60);
                    countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2,'0')}`;
                }
            }, 1000);
        @endif
    </script>
@endsection
