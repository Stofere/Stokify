<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProdukPerKategoriExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $produks;
    protected $kategori;
    protected $isGeneralExport;

    public function __construct($produks, $kategori)
    {
        $this->produks = $produks;
        $this->kategori = $kategori;
        // Tentukan apakah ini ekspor umum (semua kategori) atau spesifik
        $this->isGeneralExport = is_null($kategori);
    }

    public function collection()
    {
        if (!$this->isGeneralExport) {
            return $this->produks;
        }

        // Jika ini ekspor umum, kita perlu memanipulasi collection
        // untuk menyisipkan header kategori.
        $processedCollection = collect();
        $this->produks->groupBy('id_kategori')->each(function ($items, $kategoriId) use (&$processedCollection) {
            // Tambahkan baris header untuk kategori
            $processedCollection->push(['is_header' => true, 'nama_kategori' => $items->first()->kategori->nama]);
            // Tambahkan semua produk di bawah header tersebut
            $items->each(function($item) use (&$processedCollection) {
                $processedCollection->push($item);
            });
        });
        
        return $processedCollection;
    }

    public function headings(): array
    {
        // Tambahkan kolom 'No' di awal
        return [
            'No',
            'Kode Barang',
            'Nama Produk',
            'Stok',
            'Satuan',
            'Lokasi',
            'Harga Jual',
        ];
    }

    private $counter = 1; // Counter untuk nomor urut

    public function map($row): array
    {
        // Jika baris ini adalah header kategori (dari collection yang kita proses)
        if (is_array($row) && isset($row['is_header'])) {
            $this->counter = 1; // Reset counter
            // Kembalikan array yang akan menjadi satu baris gabungan
            return [
                $row['nama_kategori']
            ];
        }

        // Jika ini adalah baris produk biasa
        $produk = $row;
        return [
            $this->counter++,
            $produk->kode_barang,
            $produk->nama_produk,
            $produk->lacak_stok ? $produk->stok : 'Tidak Dilacak',
            $produk->satuan,
            $produk->lokasi,
            $produk->harga_jual_standar,
        ];
    }
    
    public function title(): string
    {
        return $this->isGeneralExport ? 'Semua Produk' : 'Kategori - ' . $this->kategori->nama;
    }
}