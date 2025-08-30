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
    public $tanggal_transaksi;
    public $id_pelanggan;
    public $id_marketing; 
    public $catatan;
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
            'tanggal_transaksi' => 'required|date',
            'id_pelanggan' => 'nullable|exists:pelanggan,id',
            'id_marketing' => 'required|exists:marketing,id',
            'catatan' => 'nullable|string',
            'keranjang' => 'required|array|min:1',
        ];
    }
    
    public function mount(TransaksiPenjualan $transaksi)
    {
        $this->transaksi = $transaksi->load('detail.produk', 'editor');

        // isi properti dari data yang ada
        $this->tanggal_transaksi = Carbon::parse($this->transaksi->tanggal_transaksi)->format('Y-m-d');
        $this->id_pelanggan = $this->transaksi->id_pelanggan;
        $this->id_marketing = $this->transaksi->id_marketing; 
        $this->catatan = $this->transaksi->catatan;

        foreach ($this->transaksi->detail as $item) {
            $this->keranjang[] = [
                'id_produk' => $item->id_produk,
                'nama_produk' => $item->produk->nama_produk ?? 'Produk Dihapus',
                'satuan' => $item->satuan_saat_transaksi,
                'jumlah' => (float) $item->jumlah,
                'harga_satuan_deal' => (int) $item->harga_satuan_deal,
                'subtotal' => (int) $item->subtotal,
                'lacak_stok' => $item->produk->lacak_stok ?? false,
                'stok_tersedia' => $item->produk->stok ?? 0,
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
    

    // [SALIN SEMUA METHOD HELPER DARI BuatTransaksiPenjualan.php]
    public function tambahProdukKeKeranjang($produkId)
    {
        $produk = Produk::find($produkId);
        if (!$produk) return;

        // Cek jika produk sudah ada di keranjang
        $keranjangIndex = null;
        foreach ($this->keranjang as $index => $item) {
            if ($item['id_produk'] == $produkId) {
                $keranjangIndex = $index;
                break;
            }
        }

        // [FIX] LOGIKA VALIDASI STOK
        // Tentukan jumlah yang sudah ada di keranjang
        $jumlahDiKeranjang = ($keranjangIndex !== null) ? $this->keranjang[$keranjangIndex]['jumlah'] : 0;
        
        // Cek stok HANYA JIKA produknya dilacak
        if ($produk->lacak_stok && ($jumlahDiKeranjang + 1) > $produk->stok) {
            // Kirim notifikasi error ke frontend
            $this->dispatch('show-notification', message: 'Stok tidak mencukupi!', type: 'error');
            return; // Hentikan proses
        }

        if ($keranjangIndex !== null) {
            // Jika sudah ada, tambah jumlahnya
            $this->keranjang[$keranjangIndex]['jumlah']++;
            $this->hitungSubtotal($keranjangIndex);
        } else {
            // Jika belum ada, tambahkan baru
            $this->keranjang[] = [
                'id_produk' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'satuan' => $produk->satuan,
                'lacak_stok' => $produk->lacak_stok, // Simpan status pelacakan
                'stok_tersedia' => $produk->stok, // Simpan stok awal
                'jumlah' => 1,
                'harga_satuan_deal' => $produk->harga_jual_standar,
                'subtotal' => $produk->harga_jual_standar,
            ];
            $this->hitungTotalHarga();
        }
    }
    
    // Dipanggil setiap kali jumlah atau harga di keranjang berubah
    public function updatedKeranjang($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        // [FIX] Tambahkan validasi saat quantity diubah manual di keranjang
        if ($field === 'jumlah') {
            $item = $this->keranjang[$index];
            if ($item['lacak_stok'] && $value > $item['stok_tersedia']) {
                $this->keranjang[$index]['jumlah'] = $item['stok_tersedia']; // Reset ke nilai max
                $this->dispatch('show-notification', message: 'Stok tidak mencukupi! Maksimal ' . $item['stok_tersedia'], type: 'error');
            }
        }
        
        $this->hitungSubtotal($index);
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
        DB::transaction(function () {
            // Logika koreksi stok yang kompleks
            $detailLama = $this->transaksi->detail->keyBy('id_produk');
            $idProdukDiKeranjang = collect($this->keranjang)->pluck('id_produk');

            // 1. Kembalikan stok untuk item yang dihapus dari keranjang
            $detailYangDihapus = $this->transaksi->detail()->whereNotIn('id_produk', $idProdukDiKeranjang)->get();
            foreach ($detailYangDihapus as $itemDihapus) {
                $produk = $itemDihapus->produk;
                if ($produk && $produk->lacak_stok) {
                    $produk->increment('stok', $itemDihapus->jumlah);
                }
                $itemDihapus->delete();
            }

            // 2. Update item yang ada di keranjang dan koreksi stoknya
            foreach ($this->keranjang as $item) {
                $produk = Produk::find($item['id_produk']);
                if ($produk && $produk->lacak_stok) {
                    $jumlahLama = $detailLama->get($item['id_produk'])->jumlah ?? 0;
                    $selisih = $jumlahLama - $item['jumlah'];
                    if ($selisih != 0) {
                        $produk->increment('stok', $selisih);
                    }
                }
                
                DetailTransaksiPenjualan::updateOrCreate(
                    ['id_transaksi_penjualan' => $this->transaksi->id, 'id_produk' => $item['id_produk']],
                    ['jumlah' => $item['jumlah'], 'satuan_saat_transaksi' => $item['satuan'], 'harga_satuan_deal' => $item['harga_satuan_deal'], 'subtotal' => $item['subtotal']]
                );
            }

            $this->transaksi->update([
                'id_pelanggan' => $this->id_pelanggan,
                'id_marketing' => $this->id_marketing, 
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'total_harga' => $this->totalHarga,
                'catatan' => $this->catatan,
                'edited_by_id_pengguna' => auth()->id(),
                'edited_at' => now(),
            ]);
        });

        $this->dispatch('show-notification', message: 'Transaksi berhasil diperbarui.', type: 'success');
        return redirect()->route('penjualan.riwayat');
    }

    public function filterByKategori($kategoriId)
    {
        $this->kategoriAktif = $kategoriId;
        $this->resetPage(); // Reset paginasi ke halaman 1 saat filter diubah
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
        $this->semuaPelangganOptions = $this->getPelangganOptions();

        return view('livewire.edit-transaksi-penjualan', [
            'produks' => $produks
        ]);
    }

}