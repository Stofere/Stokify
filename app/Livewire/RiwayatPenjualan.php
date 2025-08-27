<?php

namespace App\Livewire;

use App\Models\TransaksiPenjualan;
use Livewire\Component;
use Livewire\WithPagination;

class RiwayatPenjualan extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate;
    public $endDate;
    
    // Properti untuk menampilkan detail transaksi di modal
    public $selectedTransaksi;
    public $isDetailModalOpen = false;

    public function mount()
    {
        // Set default filter tanggal untuk bulan ini
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $query = TransaksiPenjualan::with(['pelanggan', 'pengguna'])
            // Filter berdasarkan rentang tanggal
            ->when($this->startDate, function ($q) {
                $q->whereDate('tanggal_transaksi', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('tanggal_transaksi', '<=', $this->endDate);
            })
            // Filter berdasarkan pencarian
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('kode_transaksi', 'like', '%' . $this->search . '%')
                        ->orWhereHas('pelanggan', function ($pelangganQuery) {
                            $pelangganQuery->where('nama', 'like', '%' . $this->search . '%');
                        });
                });
            });

        $transaksis = $query->latest('tanggal_transaksi')->paginate(15);
        
        return view('livewire.riwayat-penjualan', [
            'transaksis' => $transaksis,
        ]);
    }
    
    // Method untuk membuka modal detail
    public function showDetail($transaksiId)
    {
        // Eager load detail beserta produknya
        $this->selectedTransaksi = TransaksiPenjualan::with('detail.produk')->find($transaksiId);
        $this->isDetailModalOpen = true;
    }

    public function closeModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedTransaksi = null;
    }
}