<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Semua Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #2b7a78;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background: #def2f1;
            font-weight: 600;
        }

        .total-row {
            font-weight: bold;
            background: #e6fcf5;
        }
    </style>
</head>

<body>
    <h2>Daftar Semua Transaksi</h2>

    @foreach ($orders as $order)
        <table>
            <thead>
                <tr>
                    <th colspan="4">Transaksi #{{ $order->id }} - {{ $order->created_at->format('d/m/Y H:i') }}
                    </th>
                </tr>
                <tr>
                    <th>Member</th>
                    <th>Nama Obat</th>
                    <th>Jumlah</th>
                    <th>Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $order->member->name ?? '-' }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                @php
                    $subtotal = $order->items->sum('line_total');
                    $discountAmount = $subtotal - $order->total;
                    $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                @endphp

                <tr class="total-row">
                    <td colspan="2">Total Barang: {{ $order->items->sum('quantity') }}</td>
                    <td colspan="2">Total Bayar: Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>

                @if ($discountAmount > 0)
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right;">
                            Diskon Poin ({{ number_format($discountPercentage, 2) }}%)
                        </td>
                        <td>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                    </tr>
                @endif

                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Jumlah Bayar</td>
                    <td>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Kembalian</td>
                    <td>Rp {{ number_format($order->change, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <br>
    @endforeach
</body>

</html>
