@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4 text-primary fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi</h2>

        @if ($transactions->isEmpty())
            <div class="alert alert-info text-center shadow-sm">
                <i class="bi bi-info-circle me-2"></i>Belum ada transaksi.
            </div>
        @else
            @foreach ($transactions as $transaction)
                @php
                    $subtotal = $transaction->items->sum('line_total');
                    $discountAmount = $subtotal - $transaction->total;
                    $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                    $change = max(0, $transaction->paid_amount - $transaction->total);
                @endphp

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-receipt me-2"></i>
                            <strong>Transaksi #{{ $transaction->id }}</strong>
                        </span>
                        <span class="d-flex align-items-center">
                            <i class="bi bi-calendar-event me-1"></i>
                            {{ $transaction->created_at->format('d-m-Y H:i') }}

                            <!-- Tombol Detail (Modal) -->
                            <button type="button" class="btn btn-sm btn-light ms-3" data-bs-toggle="modal"
                                data-bs-target="#transactionDetailModal{{ $transaction->id }}">
                                <i class="bi bi-eye me-1"></i>Detail
                            </button>

                            <!-- Tombol Download PDF -->
                            <a href="{{ route('order.invoice.download', $transaction->id) }}" target="_blank"
                                class="btn btn-sm btn-light ms-2">
                                <i class="bi bi-download me-1"></i>Download PDF
                            </a>

                            <!-- Tombol Kirim WhatsApp -->
                            @if ($transaction->member)
                                <form action="{{ route('cart.sendWhatsApp', ['cart' => $transaction->id]) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    <input type="hidden" name="ids"
                                        value="{{ implode(',', $transaction->items->pluck('id')->toArray()) }}">
                                    <input type="hidden" name="no_telp" value="{{ $transaction->member->phone }}">
                                    <button type="submit" class="btn btn-sm btn-success ms-2">
                                        <i class="fab fa-whatsapp"></i> Kirim ke WhatsApp
                                    </button>
                                </form>
                            @endif
                        </span>
                    </div>

                    <div class="card-body">
                        <p><strong>Total:</strong> Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                        <p><strong>Member:</strong>
                            @if ($transaction->member)
                                {{ $transaction->member?->name ?? '-' }}
                            @else
                                <span class="badge bg-secondary">Tanpa Member</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Modal Detail Transaksi -->
                <div class="modal fade" id="transactionDetailModal{{ $transaction->id }}" tabindex="-1"
                    aria-labelledby="transactionDetailModalLabel{{ $transaction->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="transactionDetailModalLabel{{ $transaction->id }}">
                                    Detail Transaksi #{{ $transaction->id }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama Obat</th>
                                                <th>Barcode</th>
                                                <th>Kategori</th>
                                                <th>Harga</th>
                                                <th>Jumlah</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transaction->items as $item)
                                                <tr>
                                                    <td
                                                        class="{{ $item->obat?->trashed() ? 'text-muted text-decoration-line-through' : '' }}">
                                                        {{ $item->product_name }}
                                                    </td>
                                                    <td>{{ $item->obat?->kode ?? '-' }}</td>
                                                    <td>{{ $item->obat?->category?->nama ?? '-' }}</td>
                                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <p><strong>Subtotal:</strong> Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                                <p><strong>Diskon Poin:</strong>
                                    @if ($discountAmount > 0)
                                        Rp {{ number_format($discountAmount, 0, ',', '.') }}
                                        ({{ number_format($discountPercentage, 2) }}%)
                                    @else
                                        -
                                    @endif
                                </p>
                                <p class="fs-5"><strong>Total:</strong>
                                    <span class="badge bg-success fs-6">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </span>
                                </p>
                                <p><strong>Jumlah Bayar:</strong> Rp
                                    {{ number_format($transaction->paid_amount, 0, ',', '.') }}</p>
                                <p><strong>Kembalian:</strong> Rp {{ number_format($change, 0, ',', '.') }}</p>

                                <div class="mt-3">
                                    <strong>Member:</strong>
                                    @if ($transaction->member)
                                        <ul class="list-group list-group-flush ms-2">
                                            <li class="list-group-item px-0 py-1">Nama:
                                                <span class="fw-semibold">{{ $transaction->member?->name ?? '-' }}</span>
                                            </li>
                                            <li class="list-group-item px-0 py-1">Telepon:
                                                <span class="fw-semibold">{{ $transaction->member?->phone ?? '-' }}</span>
                                            </li>
                                            <li class="list-group-item px-0 py-1">Poin:
                                                <span class="fw-semibold">{{ $transaction->member?->points ?? 0 }}</span>
                                            </li>
                                        </ul>
                                    @else
                                        <span class="badge bg-secondary ms-2">Tanpa Member</span>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="{{ route('order.invoice.download', $transaction->id) }}" target="_blank"
                                    class="btn btn-primary">
                                    <i class="bi bi-download me-1"></i>Download PDF
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- CSS Blur -->
    <style>
        .modal-backdrop-blur {
            backdrop-filter: blur(20px);
            /* cukup untuk terlihat blur */
            background-color: rgba(0, 0, 0, 0.3);
            /* transparansi gelap supaya efek blur jelas */
        }
    </style>

    <!-- JS Blur -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    // tunggu sebentar agar backdrop sudah ada
                    setTimeout(() => {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => {
                            backdrop.classList.add('modal-backdrop-blur');
                        });
                    }, 10);
                });
                modal.addEventListener('hidden.bs.modal', function() {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        backdrop.classList.remove('modal-backdrop-blur');
                    });
                });
            });
        });
    </script>
@endsection
