<div x-data="{ isZoomModalOpen: false, zoomedImageUrl: '' }">
    {{-- 1. HEADER HALAMAN --}}
    {{-- Ini akan ditempatkan di bagian header layout utama Anda --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Produk') }}
        </h2>
    </x-slot>

    {{-- 2. KONTEN UTAMA HALAMAN --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- A. Tombol Aksi dan Notifikasi --}}
                    <div class="flex items-center justify-between mb-6">
                        <button wire:click="create" class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600">
                            + Tambah Produk
                        </button>
                        <div class="w-1/3">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="Cari nama produk..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
                    {{-- Notifikasi Sukses --}}
                    @if (session()->has('message'))
                        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-100" role="alert">
                            <span class="font-medium">Sukses!</span> {{ session('message') }}
                        </div>
                    @endif

                    {{-- B. Tabel Data Produk --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelacakan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($produks as $index => $produk)
                                    <tr wire:key="{{ $produk->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produks->firstItem() + $index }}</td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($produk->foto)
                                                <img 
                                                    src="{{ asset('storage/' . $produk->foto) }}" 
                                                    alt="{{ $produk->nama_produk }}" 
                                                    class="h-12 w-12 object-cover rounded-md cursor-pointer hover:scale-110 transition-transform duration-200"
                                                    @click="isZoomModalOpen = true; zoomedImageUrl = '{{ asset('storage/' . $produk->foto) }}'">
                                            @else
                                                {{-- Placeholder jika tidak ada foto --}}
                                                <div class="h-12 w-12 bg-gray-200 flex items-center justify-center rounded-md">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->nama_produk }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->kode_barang ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->kategori->nama ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($produk->harga_jual_standar, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->stok }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($produk->lacak_stok)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->satuan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->lokasi ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="edit({{ $produk->id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button wire:click="confirmDelete({{ $produk->id }})" class="text-red-600 hover:text-red-900 ml-4">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            Tidak ada data produk ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- C. Paginasi --}}
                    <div class="mt-4">
                        {{ $produks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. MODAL UNTUK TAMBAH/EDIT --}}
    @if($isOpen)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Latar belakang modal --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            {{-- Konten Modal --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="store">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $produkId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            
                            {{-- Form Input --}}
                            <div>
                                <label for="id_kategori" class="block text-sm font-medium text-gray-700">Kategori</label>
                                <select wire:model.defer="id_kategori" id="id_kategori" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($semuaKategori as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                                    @endforeach
                                </select>
                                @error('id_kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="nama_produk" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                                <input type="text" wire:model.defer="nama_produk" id="nama_produk" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('nama_produk') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="kode_barang" class="block text-sm font-medium text-gray-700">Kode Barang (SKU) Opsional</label>
                                <input type="text" wire:model.defer="kode_barang" id="kode_barang" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('kode_barang') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="harga_jual_standar" class="block text-sm font-medium text-gray-700">Harga Jual Standar</label>
                                <input type="number" wire:model.defer="harga_jual_standar" id="harga_jual_standar" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('harga_jual_standar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="satuan" class="block text-sm font-medium text-gray-700">Satuan</label>
                                <select wire:model.defer="satuan" id="satuan" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="" disabled selected>Pilih Satuan</option>
                                    <option value="Unit">Unit</option>
                                    <option value="pcs">Pcs</option>
                                    <option value="lusin">Lusin</option>
                                    <option value="kg">Kg</option>
                                    <option value="meter">Meter</option>
                                </select>
                                @error('satuan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- 1. Bungkus input Stok dan Lacak Stok dengan div x-data --}}
                            <div x-data="{ lacakStok: @entangle('lacak_stok') }">

                                {{-- 2. Checkbox untuk Lacak Stok --}}
                                <div class="mt-4">
                                    <label for="lacak_stok" class="flex items-center">
                                        {{-- 'x-model' akan mengubah nilai 'lacakStok' di Alpine secara real-time --}}
                                        <input type="checkbox" x-model="lacakStok" id="lacak_stok" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-600">Lacak Stok Produk Ini</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">Jika diaktifkan, stok akan berkurang otomatis saat terjadi penjualan.</p>
                                </div>

                                {{-- 3. Input Jumlah Stok (Kondisional) --}}
                                {{-- 'x-show="lacakStok"' akan menampilkan div ini HANYA JIKA lacakStok == true --}}
                                <div x-show="lacakStok" x-transition class="mt-4">
                                    <label for="stok" class="block text-sm font-medium text-gray-700">Jumlah Stok Saat Ini</label>
                                    <input type="number" wire:model.defer="stok" id="stok" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('stok') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                            </div>

                            <div>
                                <label for="foto" class="block text-sm font-medium text-gray-700">Foto Produk</label>
                                <input type="file" wire:model="foto" id="foto" class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                                
                                {{-- Loading indicator saat upload --}}
                                <div wire:loading wire:target="foto" class="text-sm text-gray-500 mt-1">Mengunggah...</div>

                                {{-- Menampilkan preview foto atau foto lama --}}
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="mt-2 h-24 w-24 object-cover">
                                @elseif ($foto_lama)
                                    <img src="{{ asset('storage/' . $foto_lama) }}" class="mt-2 h-24 w-24 object-cover">
                                @endif

                                @error('foto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="lokasi" class="block text-sm font-medium text-gray-700">Lokasi Rak (Opsional)</label>
                                <input type="text" wire:model.defer="lokasi" id="lokasi" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Contoh: A-01, Rak 5 Atas">
                                @error('lokasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea wire:model.defer="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('deskripsi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif


    {{-- 3. MODAL UNTUK ZOOM GAMBAR --}}
    <div
        x-show="isZoomModalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="isZoomModalOpen = false"
        @keydown.escape.window="isZoomModalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        style="display: none;"
    >
        <div class="relative p-4">
            {{-- Tombol Close --}}
            <button @click="isZoomModalOpen = false" class="absolute -top-2 -right-2 text-white bg-gray-800 rounded-full p-1 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            {{-- Konten Gambar --}}
            <img 
                :src="zoomedImageUrl" 
                alt="Zoomed Product Image" 
                class="max-w-screen-sm max-h-screen-md object-contain rounded-lg shadow-xl"
                @click.stop {{-- Mencegah klik pada gambar menutup modal --}}
            >
        </div>
    </div>
    {{-- 4. SCRIPT JAVASCRIPT --}}
    @push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        
        // Definisikan Toast sekali untuk digunakan kembali
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // Listener untuk menampilkan modal konfirmasi HAPUS
        // Mendengarkan event dari komponen PHP
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
                    // Kirim event kembali ke komponen PHP dengan membawa ID
                    Livewire.dispatch('deleteConfirmed', { id: event.id });
                }
            });
        });

        // Listener untuk semua notifikasi (Toast)
        // Mendengarkan event dari komponen PHP
        Livewire.on('show-notification', (event) => {
            Toast.fire({
                icon: event.type || 'success', // Default ke 'success' jika tipe tidak disediakan
                title: event.message // Mengambil pesan dari event
            });
        });

    });
</script>
@endpush

</div>