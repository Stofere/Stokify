<?php

use App\Livewire\KelolaKategori;
use App\Livewire\KelolaPelanggan;
use App\Livewire\KelolaPengguna;
use App\Livewire\KelolaProduk;

use App\Livewire\BuatTransaksiPenjualan;
use App\Livewire\RiwayatPenjualan;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;

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

Route::view('/', 'welcome');

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
