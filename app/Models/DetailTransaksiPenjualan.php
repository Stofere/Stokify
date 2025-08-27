<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksiPenjualan extends Model
{
    protected $table = 'detail_transaksi_penjualan';
    
    // Cara paling mudah dan direkomendasikan adalah menggunakan $guarded
    // protected $guarded = ['id'];

    protected $fillable = [
        'id_transaksi_penjualan',
        'id_produk',
        'jumlah',
        'satuan_saat_transaksi',
        'harga_satuan_deal',
        'subtotal'
    ];
    protected $casts = [
        'jumlah' => 'decimal:3',
    ];

    public function transaksi() { return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan'); }
    public function produk() { return $this->belongsTo(Produk::class, 'id_produk'); }
}