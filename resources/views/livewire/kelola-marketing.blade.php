<div>
    {{-- 1. HEADER HALAMAN --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Marketing') }}
        </h2>
    </x-slot>

    {{-- 2. KONTEN UTAMA HALAMAN --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex items-center justify-between mb-6">
                        <button wire:click="create" class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600">
                            + Tambah Marketing
                        </button>
                        <div class="w-1/3">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama marketing..." class="w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($marketing as $index => $m)
                                <tr wire:key="{{ $m->id }}">
                                    <td class="px-6 py-4">{{ $marketing->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $m->nama }}</td>
                                    <td class="px-6 py-4">
                                        @if($m->aktif)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <button wire:click="edit({{ $m->id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button wire:click="confirmDelete({{ $m->id }})" class="text-red-600 hover:text-red-900 ml-4">Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4">Tidak ada data marketing.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $marketing->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 3. MODAL UNTUK TAMBAH/EDIT --}}
    @if($isOpen)
    <div class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="store">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $marketingId ? 'Edit Marketing' : 'Tambah Marketing Baru' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                                <input type="text" wire:model.defer="nama" id="nama" class="mt-1 block w-full form-input rounded-md shadow-sm">
                                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="aktif" class="flex items-center">
                                    <input type="checkbox" wire:model.defer="aktif" id="aktif" class="rounded form-checkbox">
                                    <span class="ms-2 text-sm text-gray-600">Aktif (bisa dipilih di form penjualan)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-3 sm:space-x-reverse">
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                            Simpan
                        </button>
                        <button type="button" wire:click="closeModal"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 text-sm font-medium hover:bg-gray-100">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    {{-- 4. SCRIPT SWEETALERT (wajib) --}}
    @push('scripts')
        <script>
            // Salin-tempel script SweetAlert2 yang konsisten dari halaman lain (misal, kelola-produk)
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('show-delete-confirmation', (event) => {
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data ini akan dihapus!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('deleteConfirmed');
                        }
                    });
                });

                Livewire.on('show-notification', (event) => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: event.type || 'success',
                        title: event.message
                    });
                });
            });
        </script>
    @endpush
</div>