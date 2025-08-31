<?php

namespace App\Livewire;

use App\Models\TransaksiPenjualan;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;

class RiwayatPenjualan extends Component
{
    use WithPagination;

    // Properti filter & pencarian
    public $search = '';
    public $startDate;
    public $endDate;
    public $filterPeriode = 'bulan_ini';
    
    // Properti untuk menampilkan detail transaksi di modal
    public $tabAktif = 'proses'; // Default tab adalah "Butuh Proses"
    public $selectedTransaksi;
    public $isDetailModalOpen = false;

    // Properti untuk batalkan transaksi
    public $transaksiIdUntukDibatalkan;

    // Method ini akan berjalan setiap kali salah satu propertinya di-update dari frontend
    public function updated($property)
    {
        // Jika filter periode atau tab berubah, reset paginasi ke halaman 1
        if (in_array($property, ['filterPeriode', 'tabAktif', 'search'])) {
            $this->resetPage();
        }
    }

    // Method ini dipanggil oleh dropdown filter periode
    public function setFilterPeriode($periode)
    {
        $this->filterPeriode = $periode;
        
        switch ($periode) {
            case 'hari_ini':
                $this->startDate = now()->startOfDay()->format('Y-m-d');
                $this->endDate = now()->endOfDay()->format('Y-m-d');
                break;
            case 'kemarin':
                $this->startDate = now()->subDay()->startOfDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->endOfDay()->format('Y-m-d');
                break;
            case '7_hari':
                $this->startDate = now()->subDays(6)->startOfDay()->format('Y-m-d');
                $this->endDate = now()->endOfDay()->format('Y-m-d');
                break;
            case 'bulan_ini':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                // Reset tanggal agar pengguna bisa memilih sendiri
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
    }

    // Dipanggil oleh tombol tab
    public function gantiTab($namaTab)
    {
        $this->tabAktif = $namaTab;
    }

    public function render()
    {
        // Panggil setFilterPeriode saat render pertama kali jika belum diset
        if (is_null($this->startDate) && $this->filterPeriode !== 'custom') {
            $this->setFilterPeriode('bulan_ini');
        }

        $query = TransaksiPenjualan::with(['pelanggan', 'pengguna', 'marketing'])
            ->when($this->startDate, fn($q) => $q->whereDate('tanggal_transaksi', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('tanggal_transaksi', '<=', $this->endDate))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('kode_transaksi', 'like', '%' . $this->search . '%')
                        ->orWhereHas('pelanggan', function ($pelangganQuery) {
                            $pelangganQuery->where('nama', 'like', '%' . $this->search . '%');
                        });
                });
            });
        
        // [LOGIKA TAB]
        switch ($this->tabAktif) {
            case 'proses':
                $query->where('status_penjualan', '!=', 'dibatalkan')
                      ->where(function ($q) {
                          $q->where('status_pembayaran', 'belum_lunas')
                            ->orWhere('status_pengiriman', 'belum_terkirim');
                      });
                break;
            case 'selesai':
                $query->where('status_penjualan', '!=', 'dibatalkan')
                      ->where('status_pembayaran', 'lunas')
                      ->where('status_pengiriman', 'terkirim');
                break;
            case 'dibatalkan':
                $query->where('status_penjualan', 'dibatalkan');
                break;
            // 'semua' case (opsional)
            // case 'semua':
            // default:
            //     // Tidak perlu filter status tambahan
            //     break;
        }

        $transaksis = $query->latest('tanggal_transaksi')->paginate(15);
        
        return view('livewire.riwayat-penjualan', [
            'transaksis' => $transaksis,
        ]);
    }

    public function konfirmasiBatal($transaksiId)
    {
        $this->transaksiIdUntukDibatalkan = $transaksiId;
        $this->dispatch('show-cancel-confirmation');
    }

    #[On('cancelConfirmed')]
    public function batalkanTransaksi()
    {
        $transaksi = TransaksiPenjualan::find($this->transaksiIdUntukDibatalkan);
        if ($transaksi) {
            $transaksi->batalkan();
            $this->dispatch('show-notification', message: 'Transaksi berhasil dibatalkan.', type: 'success');
        }
        $this->transaksiIdUntukDibatalkan = null;
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

    public function cetakInvoice($transaksiId)
    {
        $transaksi = TransaksiPenjualan::with('pelanggan', 'detail.produk')->find($transaksiId);
        if (!$transaksi) return;

        $data = ['transaksi' => $transaksi];

        $pdf = Pdf::loadView('pdf.invoice-penjualan', $data)->setPaper('a4', 'portrait');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'invoice-' . $transaksi->kode_transaksi . '.pdf');
    }
}