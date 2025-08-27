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
        $table->foreignId('id_transaksi_penjualan')->constrained('transaksi_penjualan')->cascadeOnDelete();
        $table->foreignId('id_produk')->constrained('produk')->restrictOnDelete();
        
        // 1. UBAH TIPE DATA INI
        // Menggunakan DECIMAL untuk mengakomodasi kg, meter, dll.
        // (10, 2) artinya total 10 digit, dengan 2 digit di belakang koma. Sesuaikan jika perlu presisi lebih.
        // Misal, (10, 3) untuk 1.255 kg.
        $table->decimal('jumlah', 10, 2);

        // 2. TAMBAHKAN KOLOM INI
        // Menyimpan satuan yang digunakan PADA SAAT transaksi.
        // Ini mengunci data historis, bahkan jika satuan di master produk berubah di kemudian hari.
        $table->string('satuan_saat_transaksi');
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
