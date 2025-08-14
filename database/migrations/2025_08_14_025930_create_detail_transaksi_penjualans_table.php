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
        Schema::create('detail_transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi_penjualan')->constrained('transaksi_penjualan');
            $table->foreignId('id_produk')->constrained('produk');
            $table->integer('jumlah');
            $table->bigInteger('harga_satuan_deal');
            $table->bigInteger('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_penjualans');
    }
};
