<?php

namespace App\Livewire;

use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProdukPerKategoriExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanProduk extends Component
{
    use WithPagination;

    public $filterKategori = ''; // Menyimpan ID kategori yang difilter

    /**
     * Method helper untuk query agar tidak duplikasi kode.
     * Ini adalah pusat dari semua logika pengambilan data.
     */
    private function getFilteredProduksQuery()
    {
        return Produk::with('kategori')
            ->when($this->filterKategori, function ($query) {
                $query->where('id_kategori', $this->filterKategori);
            })
            ->orderBy('id_kategori')
            ->orderBy('nama_produk');
    }

    public function render()
    {
        // Gunakan query helper dan tambahkan paginasi untuk tampilan di layar
        $produks = $this->getFilteredProduksQuery()->paginate(25);
        
        $semuaKategori = Kategori::orderBy('nama')->get();

        return view('livewire.laporan-produk', [
            'produks' => $produks,
            'semuaKategori' => $semuaKategori,
        ]);
    }

    public function exportExcel()
    {
        // Ambil SEMUA data yang cocok dengan filter, tanpa paginasi
        $produks = $this->getFilteredProduksQuery()->get();
        $kategoriDipilih = $this->filterKategori ? Kategori::find($this->filterKategori) : null;
        $namaKategori = $kategoriDipilih ? $kategoriDipilih->nama : 'Semua-Kategori';
        
        $namaFile = 'produk-' . strtolower(str_replace(' ', '-', $namaKategori)) . '.xlsx';

        return Excel::download(new ProdukPerKategoriExport($produks, $kategoriDipilih), $namaFile);
    }

    public function cetakPdf()
    {
        // Ambil SEMUA data yang cocok dengan filter, tanpa paginasi
        $produks = $this->getFilteredProduksQuery()->get();
        $kategoriDipilih = $this->filterKategori ? Kategori::find($this->filterKategori) : null;

        $data = [
            'produks' => $produks,
            'kategoriDipilih' => $kategoriDipilih,
            'tanggal' => now()->isoFormat('D MMMM YYYY'),
        ];

        $pdf = Pdf::loadView('pdf.produk-katalog-filtered', $data)->setPaper('a4', 'portrait');
        $namaKategori = $kategoriDipilih ? $kategoriDipilih->nama : 'Semua-Kategori';
        $namaFile = 'katalog-' . strtolower(str_replace(' ', '-', $namaKategori)) . '.pdf';
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $namaFile);
    }

    // Reset paginasi saat filter berubah
    public function updatedFilterKategori()
    {
        $this->resetPage();
    }
}