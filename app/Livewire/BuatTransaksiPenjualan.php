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
use Livewire\Attributes\Computed;


class BuatTransaksiPenjualan extends Component
{
    use WithPagination;
    
    // Properti untuk info transaksi
    public $tanggal_transaksi;
    public $id_pelanggan;
    public $id_marketing; 
    
    
    public $catatan;

    // Properti baru untuk UI POS
    public $semuaKategori = [];
    public $kategoriAktif = null; // ID dari kategori yang dipilih
    public $searchProduk = '';

    // Properti untuk keranjang
    public $keranjang = [];
    public $totalHarga = 0;
    
    // Data master
    public $semuaPelanggan = [];
    public $semuaPelangganOptions = []; // Properti baru untuk menampung format options
    public $semuaMarketing = []; 
    
    // Properti untuk modal & pelanggan baru
    public $isModalPelangganOpen = false;
    public $pelangganBaru = [
        'nama' => '',
        'telepon' => '',
        'alamat' => '',
    ];

    // Properti untuk menampilkan nama pelanggan baru di UI setelah ditambahkan
    public $namaPelangganBaruSementara = '';

    // Properti untuk menampung pilihan status dari form
    public $status_penjualan = 'pesanan';
    public $status_pembayaran = 'belum_lunas';
    public $status_pengiriman = 'belum_terkirim';

    // Aturan validasi
    protected function rules() 
    {
        return [
            'tanggal_transaksi' => 'required|date_format:Y-m-d\TH:i',
            'id_pelanggan' => 'nullable|exists:pelanggan,id',
            'id_marketing' => 'required|exists:marketing,id',
            'keranjang' => 'required|array|min:1',
            'pelangganBaru.nama' => 'required_if:id_pelanggan,null|string|min:3|unique:pelanggan,nama',
        ];
    }

    public function mount()
    {
        // Set nilai awal dengan format yang cocok untuk input datetime-local
        $this->tanggal_transaksi = now()->format('Y-m-d\TH:i');

        // Panggil generate kode dengan tanggal saat ini`
        $this->tanggal_transaksi = now()->format('Y-m-d\TH:i');
        $this->semuaKategori = Kategori::orderBy('nama')->get(); 

        $this->semuaMarketing = Marketing::where('aktif', true)->orderBy('nama')->get();
    }

    // [2] BUAT COMPUTED PROPERTY UNTUK KODE TRANSAKSI
    // Properti ini akan dihitung ulang secara otomatis setiap kali
    // properti lain yang digunakannya ($this->tanggal_transaksi) berubah.
    #[Computed]
    public function kodeTransaksiPreview()
    {
        // Pastikan tanggal_transaksi tidak kosong
        if (empty($this->tanggal_transaksi)) {
            return 'INV-XXXX-XXXX';
        }

        try {
            $tanggal = Carbon::parse($this->tanggal_transaksi);
            $datePart = $tanggal->format('Ymd');
            $prefix = 'INV-';
            
            // Hitung jumlah transaksi PADA TANGGAL YANG DIPILIH
            $countPadaTanggal = TransaksiPenjualan::whereDate('tanggal_transaksi', $tanggal->toDateString())->count();
            $sequence = $countPadaTanggal + 1;
            
            $paddedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

            return $prefix . $datePart . '-' . $paddedSequence;
        } catch (\Exception $e) {
            // Tangani jika format tanggal tidak valid untuk sementara
            return 'Format tanggal salah...';
        }
    }

    public function filterByKategori($kategoriId)
    {
        $this->kategoriAktif = $kategoriId;
        $this->resetPage(); // Reset paginasi saat filter berubah
    }


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


    // Method untuk modal pelanggan baru
    public function openModalPelanggan()
    {
        $this->reset('pelangganBaru');
        $this->isModalPelangganOpen = true;
    }

    public function closeModalPelanggan()
    {
        $this->isModalPelangganOpen = false;
    }

    // Listener untuk menangkap event dari modal pelanggan
    #[On('pelangganBaruDitambahkan')]
    public function pelangganBaruDitambahkan($pelangganId)
    {
        // Method ini tidak lagi perlu dispatch event, cukup set ID-nya
        $this->id_pelanggan = $pelangganId;
    }


    // Dipanggil saat form modal di-submit
    public function simpanPelangganSementara()
    {
        $this->validate([
            'pelangganBaru.nama' => 'required|string|min:3|unique:pelanggan,nama',
            'pelangganBaru.telepon' => 'nullable|string',
            'pelangganBaru.alamat' => 'nullable|string',
        ]);
        
        // Simpan nama ke properti sementara untuk ditampilkan di UI
        $this->namaPelangganBaruSementara = $this->pelangganBaru['nama'];
        
        // Kosongkan pilihan dropdown pelanggan yang sudah ada
        $this->id_pelanggan = null;

        $this->closeModalPelanggan();
    }

    /**
     * Langkah 1: Dipanggil oleh tombol "Simpan Transaksi".
     * Tugasnya hanya memvalidasi dan memicu dialog konfirmasi di frontend.
     */
    public function konfirmasiSimpanTransaksi()
    {
        // Jalankan validasi awal
        if (empty($this->id_pelanggan) && empty($this->namaPelangganBaruSementara)) {
            $this->addError('id_pelanggan', 'Silahkan pilih pelanggan atau tambahkan pelanggan baru.');
            return;
        }
        $this->validate();

        // Jika validasi berhasil, kirim event ke Javascript untuk menampilkan dialog konfirmasi
        $this->dispatch('show-save-confirmation');
    }

    /**
     * Langkah 2: Dipanggil oleh event balasan dari JavaScript.
     * Tugasnya adalah menjalankan logika penyimpanan ke database.
     */
    #[On('saveConfirmed')]
    public function simpanTransaksi()
    {
        DB::transaction(function () {
            $pelangganIdUntukTransaksi = $this->id_pelanggan;

            // JIKA ada pelanggan baru yang "mengambang"
            if (!empty($this->namaPelangganBaruSementara)) {
                // BARU kita simpan pelanggan ke Database
                $pelanggan = Pelanggan::create($this->pelangganBaru);
                $pelangganIdUntukTransaksi = $pelanggan->id; 
            }
            
            // 1. Buat record transaksi utama
            $transaksi = TransaksiPenjualan::create([
                'id_pengguna' => auth()->id(),
                'id_pelanggan' => $pelangganIdUntukTransaksi, 
                'id_marketing' => $this->id_marketing,
                'kode_transaksi' => $this->kodeTransaksiPreview,
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'total_harga' => $this->totalHarga,
                'catatan' => $this->catatan,
                'status_penjualan' => $this->status_penjualan,
                'status_pembayaran' => $this->status_pembayaran,
                'status_pengiriman' => $this->status_pengiriman,

            ]);
            // 2. Loop melalui keranjang dan simpan setiap item
            foreach ($this->keranjang as $item) {
                DetailTransaksiPenjualan::create([
                    'id_transaksi_penjualan' => $transaksi->id,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan_deal' => $item['harga_satuan_deal'],
                    'satuan_saat_transaksi' => $item['satuan'],
                    'subtotal' => $item['subtotal'],
                ]);

                // === LOGIKA PENGURANGAN STOK ===
                $produk = Produk::find($item['id_produk']);

                // 3. Hanya kurangi stok jika statusnya BUKAN 'draft'
                if ($this->status_penjualan !== 'draft') {
                    $produk = Produk::find($item['id_produk']);
                    if ($produk && $produk->lacak_stok) {
                        $produk->decrement('stok', $item['jumlah']);
                    }
                }
            }
        });
        $this->dispatch('show-notification', message: 'Transaksi berhasil disimpan.', type: 'success');
        
        // Redirect ke halaman daftar transaksi
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

        // [SOLUSI] SIAPKAN DATA OPTIONS DI SINI SETIAP KALI RENDER
        // Ini memastikan TomSelect selalu mendapatkan data terbaru
        $this->semuaPelangganOptions = Pelanggan::orderBy('nama')
            ->get()
            ->map(fn($pelanggan) => ['value' => $pelanggan->id, 'text' => $pelanggan->nama])
            ->values()
            ->all();

        // Kirim semua data yang dibutuhkan oleh view
        return view('livewire.buat-transaksi-penjualan', [
            'produks' => $produks
        ]);
    }
}