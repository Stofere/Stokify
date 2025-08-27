<div>
    {{-- 1. HEADER HALAMAN --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pelanggan') }}
        </h2>
    </x-slot>

    {{-- 2. KONTEN UTAMA HALAMAN --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Tombol Aksi dan Pencarian --}}
                    <div class="flex items-center justify-between mb-6">
                        <button wire:click="create" class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600">
                            + Tambah Pelanggan
                        </button>
                        <div class="w-1/3">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="Cari nama atau telepon..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    {{-- Tabel Pelanggan --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pelanggan as $index => $p)
                                <tr wire:key="{{ $p->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pelanggan->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $p->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $p->telepon ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->alamat ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="edit({{ $p->id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button wire:click="confirmDelete({{ $p->id }})" class="text-red-600 hover:text-red-900 ml-4">Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada data pelanggan ditemukan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginasi --}}
                    <div class="mt-4">
                        {{ $pelanggan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 3. MODAL UNTUK TAMBAH/EDIT --}}
    @if($isOpen)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="store">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $pelangganId ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                                <input type="text" wire:model.defer="nama" id="nama" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="telepon" class="block text-sm font-medium text-gray-700">Telepon (Opsional)</label>
                                <input type="text" wire:model.defer="telepon" id="telepon" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('telepon') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat (Opsional)</label>
                                <textarea wire:model.defer="alamat" id="alamat" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    {{-- 4. SCRIPT JAVASCRIPT SWEETALERT (wajib) --}}
   @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                
                // Listener untuk menampilkan modal konfirmasi HAPUS
                Livewire.on('show-delete-confirmation', (event) => {
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Anda tidak akan bisa mengembalikan data ini!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Kirim event balasan ke backend
                            // Jika Anda mengirim ID, gunakan: Livewire.dispatch('deleteConfirmed', { id: event.id });
                            Livewire.dispatch('deleteConfirmed'); 
                        }
                    });
                });

                // Listener untuk semua notifikasi (Toast)
                // GANTI 'show-toast' MENJADI 'show-notification'
                Livewire.on('show-notification', (event) => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        // Gunakan 'event.type' untuk ikon dinamis (success, error, warning)
                        icon: event.type || 'success',
                        title: event.message
                    });
                });

            });
        </script>
    @endpush
</div>