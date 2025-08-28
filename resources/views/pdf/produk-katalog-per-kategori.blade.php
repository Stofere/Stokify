<!DOCTYPE html>
<html>
<head>
    <title>Katalog Produk - {{ $kategori->nama }}</title>
    {{-- Salin-tempel style CSS dari file pdf/produk.blade.php yang lama --}}
</head>
<body>
    <div class="header">
        <h1>Katalog Produk</h1>
        <h2>Kategori: {{ $kategori->nama }}</h2>
        <p>Dicetak pada: {{ $tanggal }}</p>
    </div>

    <table>
        <thead>
            {{-- Header tabel (No, Kode, Nama, Stok, Lokasi, Harga) --}}
        </thead>
        <tbody>
            @foreach ($produks as $index => $produk)
                <tr>
                    {{-- Isi data produk --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>