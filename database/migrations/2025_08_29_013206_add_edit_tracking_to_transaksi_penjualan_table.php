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
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            // ID pengguna yang terakhir mengedit transaksi ini
            $table->foreignId('edited_by_id_pengguna')
                  ->nullable() // Boleh kosong jika belum pernah diedit
                  ->constrained('pengguna') // Terhubung ke tabel 'pengguna'
                  ->nullOnDelete() // Jika pengguna dihapus, field ini jadi NULL
                  ->after('catatan'); // Letakkan setelah kolom 'catatan'
            
            // Timestamp kapan terakhir kali diedit
            $table->timestamp('edited_at')
                  ->nullable() // Boleh kosong jika belum pernah diedit
                  ->after('edited_by_id_pengguna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum menghapus kolom
            $table->dropForeign(['edited_by_id_pengguna']);
            $table->dropColumn(['edited_by_id_pengguna', 'edited_at']);
        });
    }
};