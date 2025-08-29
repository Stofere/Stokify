<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // Mengubah tipe kolom 'stok' menjadi DECIMAL
            // (10, 2) artinya total 10 digit, dengan 2 di belakang koma.
            $table->decimal('stok', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // Logika untuk mengembalikan jika di-rollback
            $table->integer('stok')->default(0)->change();
        });
    }
};