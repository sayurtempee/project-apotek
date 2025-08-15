<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        td {
            padding: 4px;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($items as $item)
                <tr>
                    <td align="center">{{ $no++ }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td align="right">{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td align="center">{{ $item->quantity }}</td>
                    <td align="right">{{ number_format($item->line_total, 0, ',', '.') }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4" align="right">Total Keseluruhan</td>
                <td align="right">{{ number_format($total, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
