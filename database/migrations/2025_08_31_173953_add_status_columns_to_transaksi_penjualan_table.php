<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->string('status_penjualan')->default('draft')->after('total_harga');
            $table->string('status_pembayaran')->default('belum_lunas')->after('status_penjualan');
            $table->string('status_pengiriman')->default('belum_terkirim')->after('status_pembayaran');

            
            // Tambahkan index untuk performa query filter status
            $table->index('status_penjualan');
            $table->index('status_pembayaran');
            $table->index('status_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropIndex(['status_penjualan', 'status_pembayaran', 'status_pengiriman']);
            $table->dropColumn(['status_penjualan', 'status_pembayaran', 'status_pengiriman']);
        });
    }
};