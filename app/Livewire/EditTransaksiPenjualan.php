<?php

namespace App\Livewire;

use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class EditTransaksiPenjualan extends Component
{
    public TransaksiPenjualan $transaksi;
    
    public $tanggal_transaksi;
    public $id_pelanggan;
    public $marketing;
    public $catatan;
    public $keranjang = [];
    public $totalHarga = 0;
    
    public $semuaPelanggan = [];
    public $searchProduk = '';
    public $hasilPencarian = [];

    public function mount(TransaksiPenjualan $transaksi)
    {
        $this->transaksi = $transaksi->load('detail.produk', 'editor');
        $this->tanggal_transaksi = Carbon::parse($this->transaksi->tanggal_transaksi)->format('Y-m-d');
        $this->id_pelanggan = $this->transaksi->id_pelanggan;
        $this->marketing = $this->transaksi->marketing;
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
                'jumlah_lama' => (float) $item->jumlah,
            ];
        }

        $this->hitungTotalHarga();
        $this->semuaPelanggan = Pelanggan::orderBy('nama')->get();
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
        // Anda bisa menambahkan validasi di sini jika perlu
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

            // 3. Update data transaksi utama
            $this->transaksi->update([
                'id_pelanggan' => $this->id_pelanggan,
                'marketing' => $this->marketing,
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

    public function render()
    {
        if (strlen($this->searchProduk) > 2) {
            $this->hasilPencarian = Produk::where('nama_produk', 'like', "%{$this->searchProduk}%")->take(5)->get();
        } else {
            $this->hasilPencarian = [];
        }
        return view('livewire.edit-transaksi-penjualan');
    }
}