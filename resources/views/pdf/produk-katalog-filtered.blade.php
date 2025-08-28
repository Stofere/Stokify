<!DOCTYPE html>
<html>
<head>
    <title>Katalog Produk</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header h2 { margin: 5px 0; font-weight: normal; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .category-header { background-color: #e9e9e9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Katalog Produk</h1>
        @if($kategoriDipilih)
            <h2>Kategori: {{ $kategoriDipilih->nama }}</h2>
        @else
            <h2>Semua Kategori</h2>
        @endif
        <p>Dicetak pada: {{ $tanggal }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Kode Barang</th>
                <th>Nama Produk</th>
                <th class="text-center" style="width: 10%;">Stok</th>
                <th style="width: 15%;">Lokasi</th>
                <th class="text-right" style="width: 15%;">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentKategori = null;
                $counter = 1;
            @endphp
            @forelse ($produks as $produk)
                {{-- Jika tidak ada filter, tampilkan header grup kategori --}}
                @if(!$kategoriDipilih && $produk->kategori->nama !== $currentKategori)
                    <tr class="category-header">
                        <td colspan="6">{{ $produk->kategori->nama }}</td>
                    </tr>
                    @php
                        $currentKategori = $produk->kategori->nama;
                        $counter = 1; // Reset nomor urut
                    @endphp
                @endif
                <tr>
                    <td class="text-center">{{ $counter++ }}</td>
                    <td>{{ $produk->kode_barang ?? '-' }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td class="text-center">{{ $produk->lacak_stok ? $produk->stok : '∞' }}</td>
                    <td>{{ $produk->lokasi ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($produk->harga_jual_standar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>