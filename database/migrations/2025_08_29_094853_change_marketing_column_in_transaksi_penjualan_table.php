<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            // Hapus kolom enum yang lama
            $table->dropColumn('marketing');
            
            // Tambahkan kolom foreign key yang baru
            // nullOnDelete: Jika data marketing dihapus, transaksi tetap ada.
            $table->foreignId('id_marketing')->nullable()->after('id_pelanggan')->constrained('marketing')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropForeign(['id_marketing']);
            $table->dropColumn('id_marketing');
            $table->enum('marketing', ['Simeon', 'Nopal'])->after('id_pelanggan');
        });
    }
};