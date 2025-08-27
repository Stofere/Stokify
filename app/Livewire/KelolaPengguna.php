<?php
namespace App\Livewire;

use App\Models\User as Pengguna;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class KelolaPengguna extends Component
{
    use WithPagination;

    // Properti untuk form
    public $penggunaId, $nama, $username, $password, $peran;
    // Properti lainnya
    public $isOpen = false;
    public $search = '';

    protected function rules()
    {
        return [
            'nama' => 'required|string|min:3',
            'username' => 'required|string|min:3|unique:pengguna,username,' . $this->penggunaId,
            'peran' => 'required|in:admin,pegawai',
            // Password hanya wajib saat membuat user baru
            'password' => $this->penggunaId ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    // Memberi pesan validasi kustom dalam Bahasa Indonesia
    protected $messages = [
        'nama.required' => 'Nama wajib diisi.',
        'username.required' => 'Username wajib diisi.',
        'username.unique' => 'Username ini sudah digunakan.',
        'peran.required' => 'Peran wajib dipilih.',
        'password.required' => 'Password wajib diisi saat membuat pengguna baru.',
        'password.min' => 'Password minimal 6 karakter.',
    ];

    public function render()
    {
        $pengguna = Pengguna::where('nama', 'like', '%'.$this->search.'%')
            ->orWhere('username', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);
            
        return view('livewire.kelola-pengguna', [
            'pengguna' => $pengguna
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function store()
    {
        $validatedData = $this->validate();

        // Hash password hanya jika diisi (untuk edit atau create)
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            // Hapus key password dari array jika kosong agar tidak menimpa password lama
            unset($validatedData['password']);
        }

        Pengguna::updateOrCreate(['id' => $this->penggunaId], $validatedData);

        $message = $this->penggunaId ? 'Pengguna berhasil diperbarui.' : 'Pengguna berhasil ditambahkan.';
        $this->dispatch('show-toast', ['message' => $message]);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $pengguna = Pengguna::findOrFail($id);
        $this->penggunaId = $id;
        $this->nama = $pengguna->nama;
        $this->username = $pengguna->username;
        $this->peran = $pengguna->peran;
        $this->password = ''; // Kosongkan field password saat edit

        $this->openModal();
    }
    
    public function confirmDelete($id)
    {
        // Jangan biarkan admin menghapus dirinya sendiri
        if (auth()->id() == $id) {
            $this->dispatch('show-toast', ['message' => 'Anda tidak bisa menghapus akun Anda sendiri.', 'type' => 'error']);
            return;
        }
        $this->penggunaId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    #[On('deleteConfirmed')]
    public function delete()
    {
        if ($this->penggunaId) {
            Pengguna::find($this->penggunaId)->delete();
            $this->dispatch('show-toast', ['message' => 'Pengguna berhasil dihapus.']);
        }
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }
    private function resetInputFields()
    {
        $this->penggunaId = null;
        $this->nama = '';
        $this->username = '';
        $this->password = '';
        $this->peran = '';
        $this->resetValidation();
    }
}