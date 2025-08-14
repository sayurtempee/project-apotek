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
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-receipt me-2"></i>
                            <strong>Transaksi #{{ $transaction->id }}</strong>
                        </span>
                        <span>
                            <i class="bi bi-calendar-event me-1"></i>
                            {{ $transaction->created_at->format('d-m-Y H:i') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-3 fs-5">
                            <strong>Total:</strong>
                            <span class="badge bg-success fs-6">Rp
                                {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </p>

                        <div class="table-responsive">
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
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->obat->kode ?? '-' }}</td>
                                            <td>{{ $item->obat->category->nama ?? '-' }}</td>
                                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $subtotal = $transaction->items->sum('line_total');
                            $discountAmount = $subtotal - $transaction->total;
                            $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                        @endphp

                        <div class="mt-3">
                            <p>
                                <strong>Diskon Poin:</strong>
                                @if ($discountAmount > 0)
                                    Rp {{ number_format($discountAmount, 0, ',', '.') }}
                                    ({{ number_format($discountPercentage, 2) }}%)
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        <div class="mt-4">
                            <strong>Member:</strong>
                            @if ($transaction->member)
                                <ul class="list-group list-group-flush ms-2">
                                    <li class="list-group-item px-0 py-1">Nama: <span
                                            class="fw-semibold">{{ $transaction->member->name }}</span></li>
                                    <li class="list-group-item px-0 py-1">Telepon: <span
                                            class="fw-semibold">{{ $transaction->member->phone }}</span></li>
                                    <li class="list-group-item px-0 py-1">Poin: <span
                                            class="fw-semibold">{{ $transaction->member->points }}</span></li>
                                </ul>
                            @else
                                <span class="badge bg-secondary ms-2">Tanpa Member</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
