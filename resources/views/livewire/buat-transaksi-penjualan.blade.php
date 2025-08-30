<div class="h-screen flex flex-col">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kasir - Input Transaksi Penjualan
        </h2>
    </x-slot>

     <div class="flex-grow flex overflow-hidden">
        {{-- Kolom Kiri: Keranjang & Info Pelanggan --}}
        <div class="w-1/3 bg-white p-4 flex flex-col border-r">
            {{-- Info Pelanggan & Marketing --}}
            <div class="space-y-4 mb-4">
                <div>
                    <label for="id_pelanggan" class="block text-sm font-medium text-gray-700">Pelanggan</label>
                    
                    {{-- Komponen Alpine.js yang mengelola state UI --}}
                    <div 
                        x-data="{ namaBaru: @entangle('namaPelangganBaruSementara') }"
                        class="flex items-start space-x-2 mt-1"
                    >
                        <button wire:click.prevent="openModalPelanggan" class="px-3 py-2 bg-gray-200 text-lg font-bold rounded-md hover:bg-gray-300">+</button>
                        
                        <div class="w-full">
                            {{-- Tampilkan nama baru JIKA variabel Alpine 'namaBaru' ada isinya --}}
                            <template x-if="namaBaru">
                                <div class="flex items-center justify-between px-3 py-2 bg-blue-100 text-blue-800 rounded-md">
                                    <span x-text="namaBaru + ' (Baru)'"></span>
                                    <button @click="namaBaru = ''" class="text-blue-600 hover:text-blue-900 font-bold">×</button>
                                </div>
                            </template>
                            
                            {{-- Tampilkan TomSelect JIKA variabel Alpine 'namaBaru' kosong --}}
                            <div x-show="!namaBaru" wire:ignore>
                                <select id="tom-select-pelanggan"></select>
                            </div>
                        </div>
                    </div>
                    
                    @error('id_pelanggan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    @error('pelangganBaru.nama') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="marketing" class="block text-sm font-medium text-gray-700">Marketing</label>
                    <select wire:model="id_marketing" id="marketing" class="w-full mt-1 shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <option value="">Pilih Marketing</option>
                        @foreach($semuaMarketing as $marketing_item)
                            <option value="{{ $marketing_item->id }}">{{ $marketing_item->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_marketing') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Daftar Keranjang --}}
            <h3 class="text-lg font-medium border-t pt-2">Pesanan Baru</h3>
            <div class="flex-grow overflow-y-auto mt-2">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-500">
                            <th class="py-1">Item</th>
                            <th class="py-1 text-center">Qty</th>
                            <th class="py-1 text-center">Satuan</th>
                            <th class="py-1 text-right">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keranjang as $index => $item)
                        <tr wire:key="cart-{{ $index }}" class="border-b">
                            <td class="py-2">
                                <p class="font-medium">{{ $item['nama_produk'] }}</p>
                                <p class="text-sm text-gray-600">@ Rp {{ number_format($item['harga_satuan_deal'], 0, ',', '.') }}</p>
                            </td>
                            <td class="py-2 text-center">
                                <input type="number" step="{{ in_array(strtolower($item['satuan']), ['kg', 'meter']) ? '0.01' : '1' }}" wire:model.live.debounce.300ms="keranjang.{{ $index }}.jumlah" class="w-16 text-center form-input rounded-md">
                            </td>
                            <td class="py-2 text-center">
                                <span class="text-sm text-gray-500">{{ $item['satuan'] }}</span>
                            </td>
                            <td class="py-2 text-right font-semibold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            <td class="py-2 text-center">
                                <button wire:click="hapusItemDariKeranjang({{ $index }})" class="text-red-500 hover:text-red-700">×</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-10 text-gray-400">Keranjang kosong</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Total & Tombol Bayar --}}
            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Total</span>
                    <span class="text-2xl font-bold">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                </div>
                <button wire:click="konfirmasiSimpanTransaksi" class="w-full py-3 bg-green-600 text-white font-bold rounded-md hover:bg-green-700 disabled:opacity-50" @if(empty($keranjang) || empty($id_marketing)) disabled @endif>
                    Simpan Transaksi
                </button>
            </div>
        </div>

        {{-- Kolom Kanan: Daftar Produk --}}
        <div class="w-2/3 bg-gray-100 p-4 flex flex-col">
            {{-- Search Bar --}}
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="searchProduk" placeholder="Cari produk..." class="w-full form-input rounded-md">
            </div>

            {{-- Filter Kategori --}}
            <div class="mb-4 flex flex-wrap gap-2">
                <button wire:click="filterByKategori(null)" class="{{ is_null($kategoriAktif) ? 'bg-blue-600 text-white' : 'bg-white' }} px-4 py-2 text-sm rounded-md shadow-sm">
                    Semua Kategori
                </button>
                @foreach($semuaKategori as $kategori)
                <button wire:click="filterByKategori({{ $kategori->id }})" class="{{ $kategoriAktif == $kategori->id ? 'bg-blue-600 text-white' : 'bg-white' }} px-4 py-2 text-sm rounded-md shadow-sm">
                    {{ $kategori->nama }}
                </button>
                @endforeach
            </div>

            {{-- Grid Produk --}}
            <div class="flex-grow overflow-y-auto">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($produks as $produk)
                    <div wire:key="prod-{{ $produk->id }}" wire:click="tambahProdukKeKeranjang({{ $produk->id }})" class="bg-white rounded-lg shadow-md p-2 cursor-pointer hover:border-blue-500 border-2 border-transparent transition">
                        <div class="relative">
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200">
                                <img src="{{ $produk->foto ? asset('storage/' . $produk->foto) : asset('images/tidak-ada-gambar-tersedia.png') }}" 
                                    alt="{{ $produk->nama_produk }}" 
                                    class="h-full w-full object-cover object-center group-hover:opacity-75">
                            </div>
                            

                            {{-- Indikator Stok --}}
                            <span class="absolute top-1 right-1 bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">
                                {{ $produk->lacak_stok ? format_jumlah($produk->stok, $produk->satuan) : '∞' }}
                            </span>
                        </div>

                        <div class="mt-2 text-center">
                            <p class="text-sm font-medium truncate">{{ $produk->nama_produk }}</p>
                            <p class="text-base font-bold text-blue-600">Rp {{ number_format($produk->harga_jual_standar, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="col-span-4 text-center text-gray-500 mt-10">Tidak ada produk ditemukan.</p>
                    @endforelse
                </div>
                
                {{-- Paginasi (jika perlu) --}}
                <div class="mt-4">
                    {{ $produks->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($isModalPelangganOpen)
    <div class="fixed z-20 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModalPelanggan"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="simpanPelangganSementara">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Pelanggan Baru (Sementara)</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                                <input type="text" wire:model.defer="pelangganBaru.nama" id="nama_pelanggan" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('pelangganBaru.nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="telepon_pelanggan" class="block text-sm font-medium text-gray-700">Telepon</label>
                                <input type="text" wire:model.defer="pelangganBaru.telepon" id="telepon_pelanggan" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('pelangganBaru.telepon') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="alamat_pelanggan" class="block text-sm font-medium text-gray-700">Alamat</label>
                                <textarea wire:model.defer="pelangganBaru.alamat" id="alamat_pelanggan" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('pelangganBaru.alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Tambahkan</button>
                        <button wire:click.prevent="closeModalPelanggan" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Simpan instance TomSelect di luar agar bisa diakses
            let tomSelectPelanggan = null;

            // Fungsi untuk menginisialisasi atau me-refresh TomSelect
            function initTomSelect(options, selectedValue) {
                if (tomSelectPelanggan) {
                    tomSelectPelanggan.destroy(); // Hancurkan instance lama
                }
                tomSelectPelanggan = new TomSelect('#tom-select-pelanggan', {
                    options: options,
                    items: [selectedValue],
                    placeholder: 'Ketik untuk mencari pelanggan...',
                    allowEmptyOption: true,
                    plugins: ['clear_button'],
                    
                    onChange: (value) => {
                        @this.set('id_pelanggan', value);
                    }
                });
            }

            // Inisialisasi pertama kali saat halaman dimuat
            initTomSelect(@js($semuaPelangganOptions), @js($id_pelanggan));

            // Listener untuk me-refresh data setelah pelanggan baru ditambahkan dari modal
            Livewire.on('pelanggan-list-updated', (event) => {
                initTomSelect(event.options, @this.get('id_pelanggan'));
            });
            
            // Listener untuk menampilkan notifikasi TOAST
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
                    icon: event.type || 'success',
                    title: event.message
                });
            });

            // Listener untuk KONFIRMASI SIMPAN TRANSAKSI
            Livewire.on('show-save-confirmation', (event) => {
                Swal.fire({
                    title: 'Simpan transaksi ini?',
                    text: "Pastikan semua data sudah benar sebelum menyimpan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim event balasan ke backend untuk benar-benar menyimpan
                        Livewire.dispatch('saveConfirmed');
                    }
                });
            });

        });
    </script>
    @endpush

</div>