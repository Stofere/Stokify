<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')->constrained('kategori');
            $table->string('nama_produk')->unique();
            $table->string('kode_barang')->unique();
            $table->string('satuan');
            $table->bigInteger('harga_jual_standar')->default(0)->nullable();
            $table->integer('stok')->default(0);
            $table->boolean('lacak_stok')->default(false);
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
