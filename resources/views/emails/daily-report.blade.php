<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #666;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Transaksi Harian</h1>
            <p>Rumah Sakit</p>
        </div>
        
        <div class="content">
            <p>Berikut adalah laporan transaksi pembayaran untuk tanggal <strong>{{ $date }}</strong>.</p>
            
            <div class="info-box">
                <h3>Ringkasan:</h3>
                <p>Total Transaksi: <strong>{{ $transactionCount }}</strong> transaksi</p>
            </div>

            <p>Detail selengkapnya dapat dilihat pada file Excel yang terlampir.</p>
            
            <p>Laporan ini digenerate secara otomatis oleh sistem pada {{ now()->format('d/m/Y H:i') }}.</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Rumah Sakit. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
