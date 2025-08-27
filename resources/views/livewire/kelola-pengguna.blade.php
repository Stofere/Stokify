<div>
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pengguna') }}
        </h2>
    </x-slot>

    {{-- Konten --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Aksi & Pencarian --}}
                    <div class="flex items-center justify-between mb-6">
                        <button wire:click="create"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            + Tambah Pengguna
                        </button>
                        <div class="w-full sm:w-1/3">
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Cari nama atau username..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pengguna as $user)
                                    <tr wire:key="{{ $user->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->username }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $user->peran == 'admin' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $user->peran }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button wire:click="edit({{ $user->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                Edit
                                            </button>
                                            <button wire:click="confirmDelete({{ $user->id }})"
                                                    class="text-red-600 hover:text-red-900 font-medium ml-4">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            Tidak ada data pengguna.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginasi --}}
                    <div class="mt-4">
                        {{ $pengguna->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create/Edit dengan Fade & Warna --}}
    @if($isOpen)
        @php $isEdit = (bool) $penggunaId; @endphp
        <div class="fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true" wire:keydown.escape="closeModal">
            <div class="flex items-end sm:items-center justify-center min-h-screen text-center sm:block">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity duration-200"></div>

                {{-- Spacer untuk centering --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Konten Modal --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all duration-200 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    {{-- Header berwarna dinamis --}}
                    <div class="px-4 py-3 border-b
                                {{ $isEdit ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200' }}">
                        <h3 class="text-lg font-medium
                                   {{ $isEdit ? 'text-amber-700' : 'text-blue-700' }}">
                            {{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
                        </h3>
                        @if($isEdit)
                            <p class="text-xs text-amber-600 mt-1">Anda sedang mengubah data pengguna terpilih.</p>
                        @else
                            <p class="text-xs text-blue-600 mt-1">Lengkapi form untuk menambahkan pengguna.</p>
                        @endif
                    </div>

                    {{-- Form --}}
                    <form wire:submit.prevent="store">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="space-y-4">
                                <div>
                                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                                    <input type="text" id="nama" wire:model.defer="nama"
                                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" id="username" wire:model.defer="username"
                                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="peran" class="block text-sm font-medium text-gray-700">Peran</label>
                                    <select id="peran" wire:model.defer="peran"
                                            class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="" disabled selected>Pilih Peran</option>
                                        <option value="admin">Admin</option>
                                        <option value="pegawai">Pegawai</option>
                                    </select>
                                    @error('peran') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" id="password" wire:model.defer="password"
                                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    @if ($penggunaId)
                                        <small class="text-gray-500">Kosongkan jika tidak ingin mengubah password.</small>
                                    @endif
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-white text-sm font-medium
                                           {{ $isEdit ? 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }}"
                                    wire:loading.attr="disabled"
                                    wire:target="store">
                                <span wire:loading.remove wire:target="store">Simpan</span>
                                <span wire:loading wire:target="store">Menyimpan...</span>
                            </button>
                            <button type="button" wire:click="closeModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- SweetAlert (event cocok dengan komponen) --}}
    @push('scripts')
    <script>
    document.addEventListener('livewire:initialized', () => {
        // Konfirmasi Hapus
        window.addEventListener('show-delete-confirmation', () => {
            Swal.fire({
                title: 'Hapus pengguna ini?',
                text: 'Tindakan ini tidak bisa dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete');
                }
            });
        });

        // Toast sukses/error
        window.addEventListener('show-toast', (e) => {
            const detail = e.detail || {};
            const payload = detail.message !== undefined ? detail : (Array.isArray(detail) ? (detail[0] || {}) : {});
            const message = payload.message || 'Berhasil';
            const type = payload.type || 'success';
            if (window.Toast) {
                window.Toast.fire({ icon: type, title: message });
            } else {
                Swal.fire({ icon: type, title: type === 'error' ? 'Gagal' : 'Sukses', text: message, timer: 2000, showConfirmButton: false });
            }
        });
    });
    </script>
    @endpush
</div>
