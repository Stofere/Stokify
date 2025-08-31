<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaksi->kode_transaksi }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .invoice-details {
            margin-bottom: 25px;
            width: 100%;
        }
        .invoice-details td {
            padding: 2px 0;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table .text-center { text-align: center; }
        .items-table .text-right { text-align: right; }
        .totals {
            width: 100%;
            margin-top: 20px;
        }
        .totals td {
            padding: 5px;
        }
        .totals .label {
            text-align: right;
            font-weight: bold;
        }
        .totals .value {
            text-align: right;
            width: 150px;
        }
        .footer-notes {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
        .signature-area {
            margin-top: 80px;
            width: 100%;
        }
        .signature-area td {
            width: 50%;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 60px auto 5px auto;
        }

        /* CSS UNTUK STATUS BADGE */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            margin-left: 5px;
        }
        .status-lunas { background-color: #28a745; } /* Hijau */
        .status-belum-lunas { background-color: #ffc107; color: #333; } /* Kuning */
        .status-terkirim { background-color: #17a2b8; } /* Teal */
        .status-belum-terkirim { background-color: #6c757d; } /* Abu-abu */
        .status-dibatalkan { background-color: #dc3545; } /* Merah */
        .status-draft { background-color: #343a40; } /* Hitam/Abu tua */
        .status-pesanan { background-color: #007bff; } /* Biru */
    </style>
</head>
<body>
    <div class="container">
        
        <table class="invoice-details">
            <tr>
                <td style="width: 50%;">
                    <h2 style="margin: 0;">SOBAT SPEAKER</h2>
                    <p style="margin: 0; font-size: 11px;">
                        Jl. Alamat Toko Anda No. 123<br>
                        Surabaya, Jawa Timur<br>
                        Telp: 0812-3456-7890
                    </p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <h1 style="margin: 0; font-size: 28px;">INVOICE</h1>
                    <p style="margin: 5px 0 10px 0;">
                        <strong>No. Invoice:</strong> {{ $transaksi->kode_transaksi }}<br>
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->isoFormat('D MMMM YYYY') }}
                    </p>

                    <div>
                        @php
                            $statusPenjualanClass = 'status-' . $transaksi->status_penjualan;
                            $statusPembayaranClass = 'status-' . str_replace('_', '-', $transaksi->status_pembayaran);
                            // [FIX] Ganti 'status-lunas' menjadi 'status-terkirim'
                            $statusPengirimanClass = 'status-' . str_replace('_', '-', $transaksi->status_pengiriman);
                        @endphp
                        
                        @if($transaksi->status_penjualan !== 'pesanan')
                            <span class="status-badge {{ $statusPenjualanClass }}">
                                {{ strtoupper($transaksi->status_penjualan) }}
                            </span>
                        @endif
                        
                        <span class="status-badge {{ $statusPembayaranClass }}">
                            {{ strtoupper(str_replace('_', ' ', $transaksi->status_pembayaran)) }}
                        </span>

                        <span class="status-badge {{ $statusPengirimanClass }}">
                            {{ strtoupper(str_replace('_', ' ', $transaksi->status_pengiriman)) }}
                        </span>
                    </div>

                </td>
            </tr>
        </table>
        
        <table class="invoice-details">
            <tr>
                <td style="width: 50%;">
                    <strong>Kepada Yth:</strong><br>
                    {{ $transaksi->pelanggan->nama ?? 'Pelanggan Umum' }}<br>
                    {{ $transaksi->pelanggan->alamat ?? '' }}<br>
                    {{ $transaksi->pelanggan->telepon ?? '' }}
                </td>
            </tr>
        </table>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 45%;">Deskripsi Barang</th>
                    <th style="width: 15%;" class="text-center">Jumlah</th>
                    <th style="width: 15%;" class="text-right">Harga Satuan</th>
                    <th style="width: 20%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi->detail as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</td>
                    <td class="text-center">
                        {{ format_jumlah($item->jumlah, $item->satuan_saat_transaksi) }} {{ $item->satuan_saat_transaksi }}
                    </td>
                    <td class="text-right">Rp {{ number_format($item->harga_satuan_deal, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <table class="totals">
            <tr>
                <td class="label">Total</td>
                <td class="value" style="font-size: 1.2em;">
                    <strong>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </table>

        @if($transaksi->catatan)
        <div style="margin-top: 20px;">
            <strong>Catatan:</strong>
            <p style="border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; margin-top: 5px;">
                {!! nl2br(e($transaksi->catatan)) !!}
            </p>
        </div>
        @endif
        
        <table class="signature-area">
            <tr>
                <td>
                    <p>Hormat Kami,</p>
                    <div class="signature-line"></div>
                    <p>( {{ $transaksi->marketing->nama ?? 'Sobat Speaker' }} )</p>
                </td>
                <td>
                    <p>Diterima Oleh,</p>
                    <div class="signature-line"></div>
                    <p>( {{ $transaksi->pelanggan->nama ?? '....................' }} )</p>
                </td>
            </tr>
        </table>

        <div class="footer-notes">
            <p>Terima kasih atas kepercayaan Anda.</p>
        </div>
    </div>
</body>
</html>