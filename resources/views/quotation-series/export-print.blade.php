<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $series->displayName() }} — Quotation</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; }
        h1 { font-size: 16px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()">Print</button>
    <h1>Quotation List For: {{ $series->displayName() }}</h1>
    <p>Supplier: {{ $series->supplier?->name }} · {{ ucfirst($series->purchase_type) }} · {{ $series->currency }}</p>
    <table>
        <thead>
            <tr>
                <th>N/S</th>
                <th>Part Number</th>
                <th>Product Name</th>
                <th>Make</th>
                <th>Vehicle</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Unit Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['ns'] }}</td>
                    <td>{{ $row['part_number'] }}</td>
                    <td>{{ $row['product_name'] }}</td>
                    <td>{{ $row['make'] }}</td>
                    <td>{{ $row['vehicle'] }}</td>
                    <td>{{ $row['unit'] }}</td>
                    <td>{{ number_format($row['quantity'], 0) }}</td>
                    <td>{{ $row['unit_price'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
