<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kategori;
use Livewire\WithPagination;

class KelolaKategori extends Component
{
    // trait withPagination untuk fitur paginasi liveware
    use WithPagination;

    // properti untuk menampung data form dan status model
    public $kategoriId;
    public $nama;
    public $isOpen = false;

    // aturan validasi untuk form
    protected $rules = [
        'nama' => 'required|string|min:3|unique:kategori,nama',
    ];


    /**
     * render komponen
     * mengambil data dari database dengan paginasi 
     */
    public function render()
    {
        return view('livewire.kelola-kategori', [
            'kategori' => Kategori::latest()->paginate(10),
        ]);
    }

    /**
     * membuka modal untuk membuat data baru
     */
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    /**
     * membuka modal 
     */
    public function openModal()
    {
        $this->isOpen = true;
    }

    /**
     * menutup modal
     */
    public function closeModal()
    {
        $this->isOpen = false;
    }


    /**
     * reset input field form
     */
    public function resetInputFields()
    {
        $this->kategoriId = null;
        $this->nama = '';
    }

    /**
     * menyimpan data baru atau data yang di update
     */
    public function store()
    {
        // jika sedang edit, sesuaikan aturan validasi agar tidak error 'unique'
        $rules = $this->rules;
        if ($this->kategoriId) {
            $rules['nama'] = 'required|string|min:3|unique:kategori,nama,' . $this->kategoriId;
        }

        $this->validate($rules);

        Kategori::updateOrCreate(
            ['id' => $this->kategoriId],
            ['nama' => $this->nama]
        );

        session()->flash('message', 
            $this->kategoriId ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
        
        $this->closeModal();
        $this->resetInputFields();
    }

    /**
     * mengambil data kategori untuk diedit dan membuka modal
     */
    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        $this->kategoriId = $kategori->id;
        $this->nama = $kategori->nama;

        $this->openModal();
    }

    /**
     * menghapus data kategori
     */
    public function delete($id)
    {
        Kategori::find($id)->delete();
        session()->flash('message', 'Kategori berhasil dihapus.');
    }

    /**
     * menampilkan konfirmasi sebelum menghapus (opsional, untuk UX yang lebih baik)
     * saya perlu menambahkan listener di view jika mau pakai ini
     * untuk sekarang kita akan hapus langsung, nanti bisa kita tambahkan library sweetalert
     */
    public function confirmDelete($id)
    {
        // untuk MVP ini, kita langsung hapus saja agar simpel
        $this->delete($id);
    }
}
