<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'produk';

    /**
     * Atribut yang dijaga dari mass assignment.
     * Mengosongkan array ini berarti semua atribut boleh diisi.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi "milik" ke model Kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}