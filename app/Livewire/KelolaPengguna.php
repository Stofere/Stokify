<?php
namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaPengguna extends Component
{
    use WithPagination;

    public $penggunaId, $nama, $username, $peran, $password;
    public $isOpen = false;

    public function render()
    {
        return view('livewire.kelola-pengguna', [
            'pengguna' => User::latest()->paginate(10)
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function store()
    {
        $this->validate([
            'nama' => 'required|string',
            'username' => 'required|string|unique:pengguna,username,' . $this->penggunaId,
            'peran' => 'required|in:admin,pegawai',
            'password' => $this->penggunaId ? 'nullable|min:8' : 'required|min:8',
        ]);

        User::updateOrCreate(['id' => $this->penggunaId], [
            'nama' => $this->nama,
            'username' => $this->username,
            'peran' => $this->peran,
            'password' => $this->password ? Hash::make($this->password) : User::find($this->penggunaId)->password,
        ]);

        session()->flash('message', 'Data pengguna berhasil disimpan.');
        $this->closeModal();
        $this->resetInputFields();
    }

    // ... method openModal, closeModal, edit, delete lainnya (mirip KelolaKategori)
    public function openModal()
    {
        $this->isOpen = true;
    }
    public function closeModal()
    {
        $this->isOpen = false;
    }
    public function resetInputFields()
    {
        $this->penggunaId = null;
        $this->nama = '';
        $this->username = '';
        $this->peran = '';
        $this->password = '';
    }
    public function edit($id)
    {
        $pengguna = User::findOrFail($id);
        $this->penggunaId = $id;
        $this->nama = $pengguna->nama;
        $this->username = $pengguna->username;
        $this->peran = $pengguna->peran;
        $this->password = ''; // Kosongkan password saat edit
        $this->openModal();
    }
    public function delete($id)
    {
        User::findOrFail($id)->delete();
        session()->flash('message', 'Pengguna berhasil dihapus.');
    }
    public function paginationView()
    {
        return 'vendor.livewire.pagination';
    }
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'nama' => 'required|string',
            'username' => 'required|string|unique:pengguna,username,' . $this->penggunaId,
            'peran' => 'required|in:admin,pegawai',
            'password' => $this->penggunaId ? 'nullable|min:8' : 'required|min:8',
        ]);
    }
    public function confirmDelete($id)
    {
        $this->delete($id);
    }

}