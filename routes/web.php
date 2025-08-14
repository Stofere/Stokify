<?php

use App\Livewire\KelolaKategori;
use App\Livewire\KelolaPengguna;
use App\Livewire\KelolaProduk;
use Illuminate\Support\Facades\Route;

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

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/kategori', KelolaKategori::class)
    ->middleware(['auth'])
    ->name('kategori');

Route::get('/pengguna', KelolaPengguna::class)
    ->middleware(['auth'])
    ->name('pengguna');

Route::get('/produk', KelolaProduk::class)
    ->middleware(['auth'])
    ->name('produk');



require __DIR__.'/auth.php';
