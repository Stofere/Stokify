<?php

namespace App\Livewire;

use App\Models\Pelanggan;
use App\Models\Marketing;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Carbon\Carbon;

class EditTransaksiPenjualan extends Component
{
    use WithPagination;

    public TransaksiPenjualan $transaksi;
    
    // Properti Form
    public $tanggal_transaksi, $id_pelanggan, $id_marketing, $catatan;
    public $status_penjualan, $status_pembayaran, $status_pengiriman;
    public $keranjang = [];
    public $totalHarga = 0;
    
    // Data Master 
    public $semuaMarketing = [];
    public $semuaPelangganOptions = []; // untuk Tom Select 
    
    // UI POS
    public $semuaKategori = [];
    public $kategoriAktif = null;
    public $searchProduk = '';

    protected function rules()
    {
        return [
            'tanggal_transaksi' => 'required|date_format:Y-m-d\TH:i',
            'id_pelanggan' => 'nullable|exists:pelanggan,id',
            'id_marketing' => 'required|exists:marketing,id',
            'catatan' => 'nullable|string',
            'status_penjualan' => 'required|in:draft,pesanan',
            'status_pembayaran' => 'required|in:lunas,belum_lunas',
            'status_pengiriman' => 'required|in:terkirim,belum_terkirim',
            'keranjang' => 'required|array|min:1',
        ];
    }
    
    public function mount(TransaksiPenjualan $transaksi)
    {
        $this->transaksi = $transaksi->load('detail.produk', 'editor');

        // isi properti dari data yang ada
        $this->tanggal_transaksi = Carbon::parse($this->transaksi->tanggal_transaksi)->format('Y-m-d\TH:i');
        $this->id_pelanggan = $this->transaksi->id_pelanggan;
        $this->id_marketing = $this->transaksi->id_marketing; 
        $this->catatan = $this->transaksi->catatan;
        $this->status_penjualan = $this->transaksi->status_penjualan;
        $this->status_pembayaran = $this->transaksi->status_pembayaran;
        $this->status_pengiriman = $this->transaksi->status_pengiriman;

        // Isi keranjang dari detail transaksi
        foreach ($this->transaksi->detail as $item) {
            $stokProdukSaatIni = $item->produk->stok ?? 0;
            
            $this->keranjang[] = [
                'id_produk' => $item->id_produk,
                'nama_produk' => $item->produk->nama_produk ?? 'Produk Dihapus',
                'satuan' => $item->satuan_saat_transaksi,
                'jumlah' => (float) $item->jumlah,
                'harga_satuan_deal' => (int) $item->harga_satuan_deal,
                'subtotal' => (int) $item->subtotal,
                'lacak_stok' => $item->produk->lacak_stok ?? false,
                
                // [FIX] Ini adalah stok MAKSIMAL yang bisa diinput pengguna
                // Stok saat ini DITAMBAH jumlah yang ada di nota ini
                'stok_maksimal_edit' => $stokProdukSaatIni + (float)$item->jumlah,
            ];
        }

        $this->hitungTotalHarga();

        $this->semuaMarketing = Marketing::where('aktif', true)->orderBy('nama')->get();
        $this->semuaKategori = Kategori::orderBy('nama')->get();
    }

    private function getPelangganOptions()
    {
        return Pelanggan::orderBy('nama')
            ->get()
            ->map(fn($pelanggan) => ['value' => $pelanggan->id, 'text' => $pelanggan->nama])
            ->values()
            ->all();
    }
    

    public function tambahProdukKeKeranjang($produkId)
    {
        $produk = Produk::find($produkId);
        if (!$produk) {
            return;
        }

        // Cari tahu apakah produk ini sudah ada di keranjang
        $keranjangIndex = null;
        foreach ($this->keranjang as $index => $item) {
            if ($item['id_produk'] == $produkId) {
                $keranjangIndex = $index;
                break;
            }
        }

        // --- Skenario 1: Produk sudah ada di keranjang (menambah kuantitas) ---
        if ($keranjangIndex !== null) {
            $item = $this->keranjang[$keranjangIndex];
            $jumlahBerikutnya = $item['jumlah'] + 1;
            
            // Validasi menggunakan stok_maksimal_edit yang sudah kita siapkan di mount()
            if ($item['lacak_stok'] && $jumlahBerikutnya > $item['stok_maksimal_edit']) {
                $this->dispatch('show-notification', message: 'Stok tidak mencukupi!', type: 'error');
                return;
            }

            $this->keranjang[$keranjangIndex]['jumlah']++;
            $this->hitungSubtotal($keranjangIndex);
        } 
        // --- Skenario 2: Produk baru ditambahkan ke nota yang sedang diedit ---
        else {
            // Validasi menggunakan stok aktual di database, karena ini item baru
            if ($produk->lacak_stok && $produk->stok <= 0) {
                $this->dispatch('show-notification', message: 'Stok produk habis!', type: 'error');
                return;
            }

            $jumlahAwal = 1;
            // Penanganan cerdas untuk satuan desimal jika stok kurang dari 1
            $isDecimalUnit = in_array(strtolower($produk->satuan), ['kg', 'meter']);
            if ($isDecimalUnit && $produk->lacak_stok && $produk->stok < 1) {
                $jumlahAwal = (float) $produk->stok;
            }

            // Tambahkan item baru ke dalam array keranjang
            $this->keranjang[] = [
                'id_produk' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'satuan' => $produk->satuan,
                'lacak_stok' => $produk->lacak_stok,
                // 'stok_maksimal_edit' untuk item baru adalah stok aktual di DB
                // karena tidak ada 'jumlah lama' yang perlu dikembalikan.
                'stok_maksimal_edit' => (float) $produk->stok, 
                'jumlah' => $jumlahAwal,
                'harga_satuan_deal' => $produk->harga_jual_standar,
                'subtotal' => $produk->harga_jual_standar * $jumlahAwal,
            ];
            $this->hitungTotalHarga();
        }
    }
    
    // Dipanggil setiap kali jumlah atau harga di keranjang berubah
    public function updatedKeranjang($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1] ?? null;

        if (isset($this->keranjang[$index])) {
            $item = $this->keranjang[$index];
            $isDecimalUnit = in_array(strtolower($item['satuan']), ['kg', 'meter']);

            if ($field === 'jumlah') {
                // [FIX #1] Jika satuan BUKAN desimal, paksa nilainya menjadi integer
                if (!$isDecimalUnit) {
                    // floor() akan membulatkan ke bawah (0.99 menjadi 0)
                    // (int) akan mengubahnya menjadi tipe data integer
                    $value = (int) floor($value);
                    $this->keranjang[$index]['jumlah'] = $value;
                }

                $jumlahBaru = (float) $value;

                // Jika jumlah 0 atau kurang, hapus item
                if ($jumlahBaru <= 0) {
                    $this->hapusItemDariKeranjang($index);
                    $this->dispatch('show-notification', message: 'Item dihapus dari keranjang.', type: 'info');
                    return;
                }

                // Validasi stok maksimal (sudah benar)
                if ($item['lacak_stok'] && $jumlahBaru > $item['stok_maksimal_edit']) {
                    $this->keranjang[$index]['jumlah'] = $item['stok_maksimal_edit'];
                    $this->dispatch('show-notification', message: 'Stok tidak mencukupi! Maksimal ' . $item['stok_maksimal_edit'], type: 'error');
                }
            }
            
            // Pastikan item masih ada sebelum menghitung subtotal
            if (isset($this->keranjang[$index])) {
                $this->hitungSubtotal($index);
            }
        }
    }
    
    private function hitungSubtotal($index)
    {
        $item = $this->keranjang[$index];
        $this->keranjang[$index]['subtotal'] = $item['jumlah'] * $item['harga_satuan_deal'];
        $this->hitungTotalHarga();
    }

    private function hitungTotalHarga()
    {
        $this->totalHarga = collect($this->keranjang)->sum('subtotal');
    }

    public function hapusItemDariKeranjang($index)
    {
        unset($this->keranjang[$index]);
        $this->keranjang = array_values($this->keranjang); // Re-index array
        $this->hitungTotalHarga();
    }

    public function filterByKategori($kategoriId)
    {
        $this->kategoriAktif = $kategoriId;
        $this->resetPage(); // Reset paginasi ke halaman 1 saat filter diubah
    }

    /**
     * Langkah 1: Memicu dialog konfirmasi sebelum update.
     */
    public function konfirmasiUpdateTransaksi()
    {
        // Jalankan validasi sebelum menampilkan konfirmasi
        $this->validate(); 
        $this->dispatch('show-update-confirmation');
    }

    /**
     * Langkah 2: Menjalankan logika update setelah dikonfirmasi.
     */
    #[On('updateConfirmed')]
    public function updateTransaksi()
    {
        $this->validate(); // Jalankan validasi sekali lagi untuk keamanan

        DB::transaction(function () {
            // [LOGIKA KOREKSI STOK TERPUSAT YANG DISEMPURNAKAN]
            
            // Langkah A: Kembalikan semua stok lama ke sistem
            if ($this->transaksi->status_penjualan !== 'draft') {
                foreach ($this->transaksi->detail as $itemLama) {
                    if ($itemLama->produk && $itemLama->produk->lacak_stok) {
                        // Gunakan increment untuk operasi database yang aman
                        $itemLama->produk->increment('stok', $itemLama->jumlah);
                    }
                }
            }
            
            // Hapus semua detail transaksi lama. Ini menyederhanakan logika.
            $this->transaksi->detail()->delete();

            // Siapkan data detail baru dari keranjang saat ini
            $detailBaru = [];
            foreach ($this->keranjang as $item) {
                $detailBaru[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'satuan_saat_transaksi' => $item['satuan'], // <-- [FIX] PASTIKAN INI ADA
                    'harga_satuan_deal' => $item['harga_satuan_deal'],
                    'subtotal' => $item['subtotal'],
                ];
            }

            // Simpan semua detail item baru dengan satu query
            $this->transaksi->detail()->createMany($detailBaru);

            // Langkah B: Kurangi stok berdasarkan keranjang BARU (jika status BUKAN draft)
            if ($this->status_penjualan !== 'draft') {
                foreach ($this->keranjang as $item) {
                    $produk = Produk::find($item['id_produk']);
                    if ($produk && $produk->lacak_stok) {
                        // Gunakan decrement untuk operasi database yang aman
                        $produk->decrement('stok', $item['jumlah']);
                    }
                }
            }

            // Update data transaksi utama
            $this->transaksi->update([
                'id_pelanggan' => $this->id_pelanggan,
                'id_marketing' => $this->id_marketing, 
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'total_harga' => $this->totalHarga,
                'catatan' => $this->catatan,
                'status_penjualan' => $this->status_penjualan,
                'status_pembayaran' => $this->status_pembayaran,
                'status_pengiriman' => $this->status_pengiriman,
                'edited_by_id_pengguna' => auth()->id(),
                'edited_at' => now(),
            ]);
        });

        $this->dispatch('show-notification', message: 'Transaksi berhasil diperbarui.', type: 'success');
        return redirect()->route('penjualan.riwayat');
    }

    public function konfirmasiBatalDariEdit()
    {
        // Tidak perlu properti baru, karena kita sudah punya $this->transaksi
        $this->dispatch('show-cancel-confirmation-from-edit');
    }

    #[On('cancelConfirmedFromEdit')]
    public function batalkanTransaksiDariEdit()
    {
        $this->transaksi->batalkan();
        $this->dispatch('show-notification', message: 'Transaksi berhasil dibatalkan.');
        // Redirect kembali ke halaman riwayat
        return redirect()->route('penjualan.riwayat');
    }


    public function render()
    {
        // Query produk yang akan ditampilkan di grid kanan
        $produks = Produk::query()
            ->when($this->searchProduk, function ($query) {
                $query->where('nama_produk', 'like', '%' . $this->searchProduk . '%')
                      ->orWhere('kode_barang', 'like', '%' . $this->searchProduk . '%');
            })
            ->when($this->kategoriAktif, function ($query) {
                $query->where('id_kategori', $this->kategoriAktif);
            })
            ->orderBy('nama_produk')
            ->paginate(12); // Tampilkan 12 produk per halaman (bisa disesuaikan)
            
        // Format options pelanggan setiap kali render
        $this->semuaPelangganOptions = Pelanggan::orderBy('nama')
            ->get()->map(fn($p) => ['value' => $p->id, 'text' => $p->nama])->values()->all();

        return view('livewire.edit-transaksi-penjualan', [ 'produks' => $produks ]);
    }
}