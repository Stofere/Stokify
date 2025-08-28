<?php

namespace App\Livewire;

use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;


use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProdukExport; 
use Barryvdh\DomPDF\Facade\Pdf;


class KelolaProduk extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $produkId, $id_kategori, $nama_produk, $harga_jual_standar, $satuan;
    public $kode_barang, $deskripsi, $foto, $foto_lama;
    public $lokasi;
    public $stok = 0; 
    public $lacak_stok = false;

    public $isOpen = false;
    public $semuaKategori = [];
    public $search = '';

    protected function rules()
    {
        return [
            'id_kategori' => 'required|exists:kategori,id',
            'nama_produk' => 'required|string|min:3|unique:produk,nama_produk,' . $this->produkId,
            'kode_barang' => 'required|string|unique:produk,kode_barang,' . $this->produkId,
            'harga_jual_standar' => 'required|integer',
            'satuan' => 'required|string',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|max:1024',
            'lokasi' => 'nullable|string|max:255',
            'stok' => 'required_if:lacak_stok,true|integer|min:0',
            'lacak_stok' => 'required|boolean',
        ];
    }

    // Pesan validasi kustom bs ditambahkan di sini
    protected $messages = [
        'nama_produk.required' => 'Nama produk wajib diisi.',
        'nama_produk.unique' => 'Nama produk ini sudah ada di database. Silakan gunakan nama lain.',
        'kode_barang.unique' => 'Kode barang ini sudah digunakan.',
    ];

    public function mount()
    {
        $this->semuaKategori = Kategori::orderBy('nama')->get();
    }

    public function render()
    {
        // Gunakan method helper di sini
        $produks = $this->getFilteredProduks()->paginate(10);

        return view('livewire.kelola-produk', ['produks' => $produks]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function store()
    {
        // Validasi tetap dijalankan seperti biasa
        $this->validate();

        // 1. Siapkan array data untuk disimpan, tanpa menyertakan 'foto' pada awalnya.
        // Kita ambil semua properti publik yang namanya cocok dengan kolom di database.
        // Ini cara yang aman dan dinamis.
        $dataToSave = [
            'id_kategori' => $this->id_kategori,
            'nama_produk' => $this->nama_produk,
            'kode_barang' => $this->kode_barang,
            'harga_jual_standar' => $this->harga_jual_standar,
            'satuan' => $this->satuan,
            'lokasi' => $this->lokasi,
            // Logika Cerdas untuk Stok:
            // Jika lacak_stok aktif, simpan nilai $this->stok.
            // Jika tidak aktif, simpan nilai 0 (atau stok sebelumnya jika ada).
            'stok' => $this->lacak_stok ? $this->stok : 0, 
            'lacak_stok' => $this->lacak_stok,
            'deskripsi' => $this->deskripsi,
        ];

        // 2. Cek apakah ada file foto BARU yang di-upload.
        if ($this->foto) {
            // Hapus foto lama jika ada (saat mode edit).
            if ($this->produkId && $this->foto_lama) {
                Storage::disk('public')->delete($this->foto_lama);
            }
            
            // Simpan foto baru dan tambahkan path-nya ke dalam array data yang akan disimpan.
            $dataToSave['foto'] = $this->foto->store('produk-foto', 'public');
        }

        // 3. Jalankan updateOrCreate dengan data yang sudah bersih.
        // Jika tidak ada foto baru, key 'foto' tidak akan ada di dalam $dataToSave,
        // sehingga kolom 'foto' di database tidak akan di-update dan tetap aman.
        Produk::updateOrCreate(['id' => $this->produkId], $dataToSave);

        $message = $this->produkId ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.';
        $this->dispatch('show-notification', message: $message, type: 'success');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $this->produkId = $id;
        $this->id_kategori = $produk->id_kategori;
        $this->nama_produk = $produk->nama_produk;
        $this->kode_barang = $produk->kode_barang;
        $this->harga_jual_standar = $produk->harga_jual_standar;
        $this->satuan = $produk->satuan;
        $this->lokasi = $produk->lokasi;
        $this->lacak_stok = (bool)$produk->lacak_stok;
        $this->stok = $produk->stok;
        $this->deskripsi = $produk->deskripsi;
        $this->foto_lama = $produk->foto; // Simpan path foto lama untuk ditampilkan
        $this->foto = null; // Reset input file

        $this->resetValidation(); // Hapus pesan error lama jika ada
        $this->openModal();     // Buka modal
    }

    // Method ini HANYA mengirim event ke frontend
    public function confirmDelete($id)
    {
        // Kirim event ke JS, bawa ID produk
        $this->dispatch('show-delete-confirmation', id: $id);
    }

    // Method ini HANYA mendengarkan event dari frontend
    #[On('deleteConfirmed')]
    public function delete($id)
    {
        $produk = Produk::find($id);
        if ($produk) {
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }
            $produk->delete();
            // Kirim event notifikasi sukses
            $this->dispatch('show-notification', message: 'Produk berhasil dihapus.', type: 'success');
        }
    }

    // Helpers
    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->produkId = null;
        $this->id_kategori = '';
        $this->nama_produk = '';
        $this->kode_barang = '';
        $this->harga_jual_standar = '';
        $this->satuan = '';
        $this->lokasi = '';
        $this->stok = 0;
        $this->lacak_stok = false;
        $this->deskripsi = '';
        $this->foto = null;
        $this->foto_lama = null;
    }

    

    // [4] Method helper untuk query agar tidak duplikasi kode
    private function getFilteredProduks()
    {
        return Produk::with('kategori')
            ->when($this->search, fn($q) =>
                $q->where('nama_produk', 'like', "%{$this->search}%")
                  ->orWhere('kode_barang', 'like', "%{$this->search}%"))
            ->latest();
    }
}