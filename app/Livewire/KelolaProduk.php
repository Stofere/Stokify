<?php

namespace App\Livewire;

use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage; // Untuk menghapus file lama

class KelolaProduk extends Component
{
    use WithPagination;
    use WithFileUploads; 

    // Properti untuk form
    public $produkId, $id_kategori, $nama_produk, $harga_jual_standar, $satuan;
    public $kode_barang, $deskripsi, $foto, $foto_lama; 
    
    // Properti untuk modal dan lainnya
    public $isOpen = false;
    public $semuaKategori = [];
    public $search = '';

    protected function rules()
    {
        return [
            'id_kategori' => 'required|exists:kategori,id',
            'nama_produk' => 'required|string|min:3',
            'kode_barang' => 'nullable|string|unique:produk,kode_barang,' . $this->produkId,
            'harga_jual_standar' => 'required|integer',
            'satuan' => 'required|string',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|max:1024', // Maksimal 1MB
        ];
    }

    /**
     * Method ini dipanggil sekali saat komponen pertama kali di-mount.
     * Kita gunakan untuk memuat data kategori.
     */
    public function mount()
    {
        $this->semuaKategori = Kategori::orderBy('nama')->get();
    }

    public function render()
    {
        // 'with('kategori')' adalah Eager Loading untuk efisiensi query
        $produks = Produk::with('kategori')->latest()->paginate(10);

        return view('livewire.kelola-produk', [
            'produks' => $produks
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function store()
    {
        // 1. Validasi dijalankan pertama dan hasilnya langsung disimpan ke $validatedData
        $validatedData = $this->validate();

        // 2. Cek apakah ada file foto baru yang di-upload
        if ($this->foto) {
            // hapus foto lama jika ada dan sedang mengedit
            if ($this->produkId && $this->foto_lama) {
                Storage::disk('public')->delete($this->foto_lama);
            }
            // Simpan foto baru, lalu timpa key 'foto' di dalam array $validatedData
            // dengan path yang baru.
            $validatedData['foto'] = $this->foto->store('produk-foto', 'public');
        }

        // 3. Jalankan updateOrCreate dengan $validatedData yang sudah lengkap.
        // Variabel ini sekarang dijamin selalu ada.
        Produk::updateOrCreate(['id' => $this->produkId], $validatedData);

        session()->flash('message', 
        $this->produkId ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.');

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
        $this->deskripsi = $produk->deskripsi;
        $this->foto_lama = $produk->foto; // Simpan path foto lama untuk

        $this->openModal();
    }

    public function delete($id)
    {
        // Tambahkan validasi jika produk sudah pernah ada di transaksi
        // Untuk sekarang, kita hapus langsung
        Produk::find($id)->delete();
        session()->flash('message', 'Produk berhasil dihapus.');
    }

    public function confirmDelete($id)
    {
        $this->delete($id);
    }
    
    // Fungsi-fungsi pembantu
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
        $this->deskripsi = '';
        $this->foto = null; // reset properti upload file
        $this->foto_lama = null;
    }
}