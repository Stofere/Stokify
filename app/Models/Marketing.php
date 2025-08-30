<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     */
    protected $table = 'marketing';

    /**
     * Atribut yang boleh diisi secara massal.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'aktif' => 'boolean',
    ];
}