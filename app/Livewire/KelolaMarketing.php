<?php

namespace App\Livewire;

use App\Models\Marketing;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class KelolaMarketing extends Component
{
    use WithPagination;

    // Properti untuk form modal
    public $marketingId, $nama;
    public $aktif = true; // Defaultnya aktif saat membuat baru

    // Properti untuk UI
    public $isOpen = false;
    public $search = '';

    protected $rules = [
        'nama' => 'required|string|min:3|unique:marketing,nama',
        'aktif' => 'required|boolean',
    ];
    
    protected $messages = [
        'nama.required' => 'Nama marketing wajib diisi.',
        'nama.unique' => 'Nama ini sudah digunakan.',
    ];

    public function render()
    {
        $marketing = Marketing::where('nama', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);
            
        return view('livewire.kelola-marketing', [
            'marketing' => $marketing,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }
    
    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }
    
    private function resetInputFields() {
        $this->reset(['marketingId', 'nama']);
        $this->aktif = true; // Selalu set default ke aktif
        $this->resetValidation();
    }

    public function store()
    {
        // Sesuaikan aturan validasi saat edit
        $rules = $this->rules;
        if ($this->marketingId) {
            $rules['nama'] = 'required|string|min:3|unique:marketing,nama,' . $this->marketingId;
        }

        $validatedData = $this->validate($rules);

        Marketing::updateOrCreate(['id' => $this->marketingId], $validatedData);

        $message = $this->marketingId ? 'Data marketing berhasil diperbarui.' : 'Marketing baru berhasil ditambahkan.';
        $this->dispatch('show-notification', message: $message, type: 'success');
        
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $marketing = Marketing::findOrFail($id);
        $this->marketingId = $id;
        $this->nama = $marketing->nama;
        $this->aktif = $marketing->aktif;
        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->marketingId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    #[On('deleteConfirmed')]
    public function delete()
    {
        if ($this->marketingId) {
            Marketing::find($this->marketingId)->delete();
            $this->dispatch('show-notification', message: 'Data marketing berhasil dihapus.', type: 'success');
        }
    }
}