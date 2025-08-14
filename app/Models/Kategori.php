<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Opsional, untuk type hinting yang lebih baik

class Kategori extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'kategori';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'nama',
    ];

    /**
     * Mendefinisikan relasi "memiliki banyak" ke model Produk.
     * Satu Kategori bisa memiliki banyak Produk.
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'id_kategori');
    }
}