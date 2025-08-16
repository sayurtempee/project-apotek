<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Invoice Pembelian #{{ $order->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #e0f7fa 0%, #f8fafc 100%);
            color: #222;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.12);
            padding: 36px 44px 44px 44px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header img {
            width: 60px;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            color: #2b7a78;
            letter-spacing: 1px;
            font-weight: 700;
            font-size: 28px;
        }

        .header p {
            margin: 2px 0;
            color: #555;
            font-size: 15px;
        }

        .divider {
            border: none;
            border-top: 2px dashed #def2f1;
            margin: 24px 0 18px 0;
        }

        h3 {
            color: #17252a;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 22px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 12px;
            background: #e0f7fa;
            color: #009688;
            font-size: 13px;
            font-weight: 600;
            margin-left: 10px;
            vertical-align: middle;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 18px;
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }

        th,
        td {
            border: none;
            padding: 13px 12px;
            text-align: left;
        }

        th {
            background: #def2f1;
            color: #222;
            font-weight: 700;
            font-size: 15px;
        }

        tbody tr:nth-child(even) {
            background: #f4f8fb;
        }

        tbody tr:hover {
            background: #e0f7fa;
            transition: background 0.2s;
        }

        .total-row td {
            font-weight: bold;
            color: #2b7a78;
            border-top: 2px solid #3aafa9;
            background: #e6fcf5;
        }

        .member-info {
            margin-top: 28px;
            background: #e6fcf5;
            border-left: 5px solid #3aafa9;
            padding: 18px 24px;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(58, 175, 169, 0.07);
        }

        .member-info h4 {
            margin-top: 0;
            color: #2b7a78;
        }

        .action-buttons {
            display: flex;
            gap: 14px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .action-buttons a,
        .action-buttons button {
            background: linear-gradient(90deg, #3aafa9 60%, #2b7a78 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 11px 22px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(58, 175, 169, 0.08);
            transition: background 0.2s, transform 0.2s;
        }

        .action-buttons a:hover,
        .action-buttons button:hover {
            background: linear-gradient(90deg, #2b7a78 60%, #3aafa9 100%);
            transform: translateY(-2px) scale(1.03);
        }

        @media (max-width: 600px) {
            .container {
                padding: 16px 6px;
            }

            table,
            th,
            td {
                font-size: 13px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="action-buttons">
            <a href="{{ route('obat.index') }}">&#8592; Kembali</a>
            <a href="{{ route('order.invoice.download', $order->id) }}" target="_blank">Download PDF</a>
            @if ($order->member)
                <form action="{{ route('cart.sendWhatsApp', ['cart' => $order->id]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="ids"
                        value="{{ implode(',', $order->items->pluck('id')->toArray()) }}">
                    <input type="hidden" name="no_telp" value="{{ $order->member->phone }}">
                    <button type="submit">
                        <i class="fab fa-whatsapp"></i> Kirim ke WhatsApp
                    </button>
                </form>
            @endif
        </div>

        <div class="header">
            <img src="https://img.icons8.com/fluency/96/000000/pill.png" alt="Logo Apotek" />
            <h2>Apotek Mii</h2>
            <p>Cakung Timur, Jakarta Timur, Gang Bayam No.17, DKI Jakarta</p>
            <p>Telp: (021) 78374839</p>
        </div>

        <hr class="divider" />

        <div class="info-row">
            <div>
                <h3>
                    Invoice #{{ $order->id }}
                    <span class="badge">{{ $order->status == 'paid' ? 'Lunas' : 'Belum Lunas' }}</span>
                </h3>
            </div>
            <div>
                <span>Tanggal: {{ $order->created_at->format('d-m-Y H:i') }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nama Obat</th>
                    <th>Harga (Rp)</th>
                    <th>Jumlah</th>
                    <th>Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                @php
                    $subtotal = $order->items->sum('line_total');
                    $discountAmount = $subtotal - $order->total;
                    $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                @endphp

                <tr class="total-row">
                    <td colspan="2"></td>
                    <td>Total Barang: {{ $order->items->sum('quantity') }}</td>
                    <td>Total: Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>

                @if ($discountAmount > 0)
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right; font-weight:600;">
                            Diskon Poin ({{ number_format($discountPercentage, 2) }}%)
                        </td>
                        <td>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                    </tr>
                @endif

                <tr class="total-row">
                    <td colspan="3" style="text-align:right; font-weight:600;">
                        Jumlah Bayar
                    </td>
                    <td>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="3" style="text-align:right; font-weight:600;">
                        Kembalian
                    </td>
                    <td>Rp {{ number_format($order->change, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if ($order->member)
            <div class="member-info">
                <h4>Data Member</h4>
                <p>Nama: <strong>{{ $order->member->name }}</strong></p>
                <p>Nomor Telepon: {{ $order->member->phone }}</p>
                <p>Poin Saat Ini: {{ $order->member->points }}</p>
            </div>
        @endif

    </div>
</body>

</html>
