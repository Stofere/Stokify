<?php

use App\Livewire\KelolaKategori;
use App\Livewire\KelolaPelanggan;
use App\Livewire\KelolaPengguna;
use App\Livewire\KelolaProduk;

use App\Livewire\BuatTransaksiPenjualan;
use App\Livewire\RiwayatPenjualan;
use App\Livewire\LaporanProduk;
use App\Livewire\EditTransaksiPenjualan;
use App\Livewire\KelolaMarketing;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Auth\Login;

use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Jika belum login, biarkan Laravel yang merender komponen login.
    // Kita tidak perlu menunjuk ke view-nya secara manual.
    // Kita bisa langsung redirect ke route 'login' yang sudah dibuat Breeze.
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/kategori', KelolaKategori::class)
    ->middleware(['auth'])
    ->name('kategori');

Route::get('/pelanggan', KelolaPelanggan::class)
    ->middleware('auth')
    ->name('pelanggan');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/pengguna', KelolaPengguna::class)->name('pengguna');
    Route::get('/laporan/produk', LaporanProduk::class)->name('laporan.produk');
    Route::get('/penjualan/edit/{transaksi}', EditTransaksiPenjualan::class)->name('penjualan.edit');
    Route::get('/marketing', KelolaMarketing::class)->name('marketing');
    // Nanti route lain khusus admin bisa ditaruh di sini
});

Route::get('/produk', KelolaProduk::class)
    ->middleware(['auth'])
    ->name('produk');

Route::get('/penjualan/baru', BuatTransaksiPenjualan::class)
    ->middleware('auth')
    ->name('penjualan.buat');

Route::get('/penjualan/riwayat', RiwayatPenjualan::class)
    ->middleware('auth')
    ->name('penjualan.riwayat');


require __DIR__.'/auth.php';
