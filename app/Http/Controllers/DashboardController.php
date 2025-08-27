<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiPenjualan;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Penjualan Hari Ini
        $penjualanHariIni = TransaksiPenjualan::whereDate('created_at', today())->sum('total_harga');

        // Jumlah Nota Hari Ini
        $notaHariIni = TransaksiPenjualan::whereDate('created_at', today())->count();

        // Produk Terlaris Minggu Ini (Improved Query)
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $produkTerlaris = DetailTransaksiPenjualan::select(
                'id_produk', 
                DB::raw('SUM(jumlah) as total_terjual') // We still sum the quantity
            )
            ->whereHas('transaksi', function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('tanggal_transaksi', [$startOfWeek, $endOfWeek]);
            })
            ->groupBy('id_produk') // Group by the product ID
            ->orderByDesc('total_terjual')
            ->with('produk') // Eager load product info
            ->take(5)
            ->get();
        
        return view('dashboard', [
            'penjualanHariIni' => $penjualanHariIni,
            'notaHariIni' => $notaHariIni,
            'produkTerlaris' => $produkTerlaris,
        ]);
    }
}