<?php

namespace App\Livewire;

use App\Models\Pelanggan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class KelolaPelanggan extends Component
{
    use WithPagination;

    public $pelangganId, $nama, $telepon, $alamat;
    public $isOpen = false;
    public $search = '';

    protected $rules = [
        'nama' => 'required|string|min:3',
        'telepon' => 'nullable|string|max:20',
        'alamat' => 'nullable|string',
    ];
    
    protected $messages = [
        'nama.required' => 'Nama pelanggan wajib diisi.',
    ];

    public function render()
    {
        $pelanggan = Pelanggan::where('nama', 'like', '%'.$this->search.'%')
            ->orWhere('telepon', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);
            
        return view('livewire.kelola-pelanggan', [
            'pelanggan' => $pelanggan,
        ]);
    }

    public function create() {
        $this->resetInputFields();
        $this->openModal();
    }
    
    public function openModal() {
        $this->isOpen = true;
    }
    
    public function closeModal() {
        $this->isOpen = false;
    }
    
    private function resetInputFields() {
        $this->reset(['pelangganId', 'nama', 'telepon', 'alamat']);
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        Pelanggan::updateOrCreate(['id' => $this->pelangganId], [
            'nama' => $this->nama,
            'telepon' => $this->telepon,
            'alamat' => $this->alamat,
        ]);

        $message = $this->pelangganId ? 'Data pelanggan berhasil diperbarui.' : 'Pelanggan baru berhasil ditambahkan.';
        $this->dispatch('show-notification', message: $message, type: 'success');
        
        
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $this->pelangganId = $id;
        $this->nama = $pelanggan->nama;
        $this->telepon = $pelanggan->telepon;
        $this->alamat = $pelanggan->alamat;
        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->pelangganId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    #[On('deleteConfirmed')]
    public function delete()
    {
        if ($this->pelangganId) {
            Pelanggan::find($this->pelangganId)->delete();
            $this->dispatch('show-notification', message: 'Data pelanggan berhasil dihapus.', type: 'success');
        }
    }
}