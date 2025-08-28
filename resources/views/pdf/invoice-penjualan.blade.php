<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $transaksi->kode_transaksi }}</title>
    {{-- Tambahkan style CSS yang mirip dengan contoh invoice Anda --}}
</head>
<body>
    {{-- Header dengan info toko Anda & tulisan "INVOICE" --}}
    <h1>INVOICE</h1>
    <p>No. Invoice: {{ $transaksi->kode_transaksi }}</p>
    <p>Tanggal: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->isoFormat('D MMMM YYYY') }}</p>
    <hr>
    <p><strong>Kepada:</strong></p>
    <p>{{ $transaksi->pelanggan->nama ?? 'Pelanggan Umum' }}</p>
    <p>{{ $transaksi->pelanggan->alamat ?? '' }}</p>
    <br>
    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Kategori</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detail as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk ?? 'N/A' }}</td>
                    <td>{{ $item->produk->kategori->nama ?? 'N/A' }}</td>
                    <td>{{ format_jumlah($item->jumlah, $item->satuan_saat_transaksi) }} {{ $item->satuan_saat_transaksi }}</td>
                    <td>Rp {{ number_format($item->harga_satuan_deal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                <td><strong>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    {{-- ... info pembayaran, tanda tangan, dll ... --}}
</body>
</html>