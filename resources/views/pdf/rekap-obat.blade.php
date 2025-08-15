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
            background-color: #eaeaea;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Obat</th>
                <th>Jumlah Terjual</th>
                <th>Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($items as $item)
                <tr>
                    <td align="center">{{ $no++ }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td align="center">{{ $item->total_qty }}</td>
                    <td align="right">{{ number_format($item->total_sales, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" align="right">Grand Total</td>
                <td align="right">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
