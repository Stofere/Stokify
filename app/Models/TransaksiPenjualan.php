<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenjualan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_penjualan';
    protected $guarded = ['id'];

    /**
     * Relasi ke model Pengguna (siapa yang mencatat).
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }
    
    /**
     * Relasi ke model Pelanggan.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * [FIX] Mendefinisikan relasi "memiliki banyak" ke DetailTransaksiPenjualan.
     * Inilah relasi 'detail' yang dicari.
     */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'id_transaksi_penjualan');
    }

    Public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by_id_pengguna');
    }

    public function marketing()
    {
        // Nama relasi 'marketing' akan memanggil model 'Marketing'
        return $this->belongsTo(Marketing::class, 'id_marketing');
    }
}