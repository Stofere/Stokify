<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection; // Pastikan untuk meng-import Collection

class ProdukExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $produks;

    public function __construct(Collection $produks)
    {
        $this->produks = $produks;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        return $this->produks;
    }

    /**
     * Mendefinisikan header untuk kolom.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama Produk',
            'Kode Barang',
            'Kategori',
            'Harga Jual',
            'Stok',
            'Lacak Stok',
            'Satuan',
            'Lokasi',
            'Deskripsi',
        ];
    }

    /**
     * Memetakan data dari setiap produk ke kolom yang sesuai.
     * @var Produk $produk
     */
    public function map($produk): array
    {
        return [
            $produk->id,
            $produk->nama_produk,
            $produk->kode_barang,
            $produk->kategori->nama ?? 'N/A',
            $produk->harga_jual_standar,
            $produk->lacak_stok ? $produk->stok : 'Tidak Dilacak',
            $produk->lacak_stok ? 'Ya' : 'Tidak',
            $produk->satuan,
            $produk->lokasi,
            $produk->deskripsi,
        ];
    }
}