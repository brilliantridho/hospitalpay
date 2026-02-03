<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $transaction->transaction_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px 0;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items th {
            background-color: #f2f2f2;
        }
        .total {
            float: right;
            width: 300px;
        }
        .total table {
            width: 100%;
        }
        .total td {
            padding: 5px 0;
        }
        .total .grand-total {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RUMAH SAKIT</h1>
        <p>BUKTI PEMBAYARAN</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="150"><strong>No. Transaksi</strong></td>
                <td>: {{ $transaction->transaction_code }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: {{ $transaction->paid_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Nama Pasien</strong></td>
                <td>: {{ $transaction->patient_name }}</td>
            </tr>
            <tr>
                <td><strong>Asuransi</strong></td>
                <td>: {{ $transaction->insurance->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Kasir</strong></td>
                <td>: {{ $transaction->user->name }}</td>
            </tr>
        </table>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>Layanan Medis</th>
                    <th width="100">Harga</th>
                    <th width="50">Qty</th>
                    <th width="100">Diskon</th>
                    <th width="120">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                <tr>
                    <td>{{ $detail->medicalService->name }}</td>
                    <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>Rp {{ number_format($detail->discount_per_item * $detail->quantity, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total">
        <table>
            <tr>
                <td>Subtotal</td>
                <td align="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Diskon</td>
                <td align="right">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL BAYAR</td>
                <td align="right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda</p>
        <p>--- Dokumen ini dicetak otomatis ---</p>
    </div>
</body>
</html>
