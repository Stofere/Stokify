<div class="h-screen flex flex-col">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Transaksi: <span class="font-mono">{{ $transaksi->kode_transaksi }}</span>
        </h2>
    </x-slot>

    <div class="flex-grow flex overflow-hidden">

        {{-- KOLOM KIRI: INFO & KERANJANG --}}
        <div class="w-1/3 bg-white p-6 flex flex-col border-r overflow-y-auto">
            
            <div class="space-y-4 mb-6">
                {{-- Tanggal --}}
                <div>
                    <label for="tanggal_transaksi" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                    <input type="date" wire:model="tanggal_transaksi" id="tanggal_transaksi" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('tanggal_transaksi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                {{-- Pelanggan (disederhanakan) --}}
                <div>
                    <label for="id_pelanggan" class="block text-sm font-medium text-gray-700">Pelanggan</label>
                    <div wire:ignore class="mt-1">
                        <select id="tom-select-pelanggan-edit"></select>
                    </div>
                    @error('id_pelanggan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                {{-- Marketing --}}
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

            {{-- BAGIAN KERANJANG BELANJA (DENGAN KOREKSI STRUKTUR) --}}
            <div class="border-t pt-6 flex-grow flex flex-col">
                <h3 class="text-lg font-medium text-gray-900">Daftar Barang</h3>
                {{-- Kontainer scrollable untuk tabel --}}
                <div class="overflow-y-auto mt-4 -mx-6 px-6 flex-grow">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($keranjang as $index => $item)
                                <tr wire:key="keranjang-{{ $index }}">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item['nama_produk'] }}</td>
                                    <td class="px-4 py-2"><input type="number" step="0.01" wire:model.live.debounce.300ms="keranjang.{{ $index }}.jumlah" class="w-24 border-gray-300 rounded-md shadow-sm"></td>
                                    <td class="px-4 py-2"><input type="number" step="1" wire:model.live.debounce.300ms="keranjang.{{ $index }}.harga_satuan_deal" class="w-40 border-gray-300 rounded-md shadow-sm"></td>
                                    <td class="px-4 py-2 whitespace-nowrap">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2"><button wire:click="hapusItemDariKeranjang({{ $index }})" class="text-red-500 hover:text-red-700">✕</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-10 text-gray-500">Keranjang masih kosong.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BAGIAN RINGKASAN & SIMPAN (DIDORONG KE BAWAH) --}}
            <div class="border-t pt-6 mt-auto">
                <textarea wire:model.defer="catatan" placeholder="Catatan transaksi (opsional)..." rows="2" class="w-full border-gray-300 rounded-md shadow-sm mb-4"></textarea>
                @if($transaksi->edited_at)
                    <div class="text-xs text-gray-500 mb-2 text-right">
                        Terakhir diedit oleh {{ $transaksi->editor->nama ?? 'N/A' }} <br>
                        pada {{ \Carbon\Carbon::parse($transaksi->edited_at)->isoFormat('D MMM YYYY, HH:mm') }}
                    </div>
                @endif
                <div class="text-right text-2xl font-bold mb-4">
                    Total: <span class="text-blue-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-end items-center space-x-3">
                    <a href="{{ route('penjualan.riwayat') }}" wire:navigate class="px-6 py-3 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50">Batal</a>
                    <button wire:click="konfirmasiUpdateTransaksi" class="px-6 py-3 bg-yellow-500 text-white font-semibold rounded-md hover:bg-yellow-600 disabled:opacity-50" @if(empty($keranjang)) disabled @endif>Update Transaksi</button>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- KOLOM KANAN (2/3 LEBAR): PENCARIAN & DAFTAR PRODUK --}}
        {{-- ======================================================================= --}}
        <div class="w-2/3 p-6 flex flex-col bg-gray-50 overflow-y-auto">
            {{-- Search Bar yang Menempel di Atas --}}
            <div class="sticky top-0 bg-gray-50 pb-4 z-10 -mt-6 -mx-6 pt-6 px-6">
                <input type="text" wire:model.live.debounce.300ms="searchProduk" placeholder="Cari produk..." class="w-full form-input rounded-md">
                <div class="mt-4 flex flex-wrap gap-2">
                    <button wire:click="filterByKategori(null)" class="{{ is_null($kategoriAktif) ? 'bg-blue-600 text-white' : 'bg-white' }} px-4 py-2 text-sm rounded-md shadow-sm">Semua</button>
                    @foreach($semuaKategori as $kategori)
                        <button wire:click="filterByKategori({{ $kategori->id }})" class="{{ $kategoriAktif == $kategori->id ? 'bg-blue-600 text-white' : 'bg-white' }} px-4 py-2 text-sm rounded-md shadow-sm">{{ $kategori->nama }}</button>
                    @endforeach
                </div>
            </div>
            
            {{-- [FIX] Tampilkan Grid Produk --}}
            <div class="flex-grow overflow-y-auto mt-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($produks as $produk)
                        <div wire:key="prod-{{ $produk->id }}" wire:click="tambahProdukKeKeranjang({{ $produk->id }})" class="bg-white rounded-lg shadow-md p-2 cursor-pointer hover:border-blue-500 border-2 border-transparent transition">
                            <div class="relative">
                                <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200">
                                    <img src="{{ $produk->foto ? asset('storage/' . $produk->foto) : asset('images/tidak-ada-gambar-tersedia.png') }}" alt="{{ $produk->nama_produk }}" class="h-full w-full object-cover object-center">
                                </div>
                                <span class="absolute top-1 right-1 bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $produk->lacak_stok ? format_jumlah($produk->stok, $produk->satuan) : '∞' }}</span>
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
                <div class="mt-4">{{ $produks->links() }}</div>
            </div>
        </div>
    </div>
    
{{-- GUNAKAN SATU BLOK @push('scripts') UNTUK SEMUA JAVASCRIPT --}}
@push('scripts')
<script>
    // Fungsi ini akan dijalankan setiap kali halaman dimuat
    // baik saat pertama kali, maupun setelah navigasi via wire:navigate
    document.addEventListener('livewire:navigated', () => {

        // =================================================================
        // INISIALISASI TOM SELECT
        // =================================================================
        let tomSelectPelangganEdit = null;

        function initTomSelectEdit() {
            // Hancurkan instance lama jika ada untuk mencegah duplikasi
            if (tomSelectPelangganEdit) {
                tomSelectPelangganEdit.destroy();
            }
            
            tomSelectPelangganEdit = new TomSelect('#tom-select-pelanggan-edit', {
                // Ambil data langsung dari properti Livewire yang sudah terhidrasi
                options: @this.get('semuaPelangganOptions'),
                items: [ @this.get('id_pelanggan') ],
                placeholder: 'Ketik untuk mencari pelanggan...',
                allowEmptyOption: true,
                plugins: ['clear_button'],
                onChange: (value) => {
                    @this.set('id_pelanggan', value);
                }
            });
        }
        
        // Panggil fungsi inisialisasi
        initTomSelectEdit();

        // Listener untuk me-refresh data TomSelect setelah ada pelanggan baru
        Livewire.on('pelanggan-list-updated', (event) => {
            // Tidak perlu memanggil init lagi, cukup update options
            if(tomSelectPelangganEdit) {
                tomSelectPelangganEdit.clearOptions();
                tomSelectPelangganEdit.addOptions(event.options);
                tomSelectPelangganEdit.setValue(@this.get('id_pelanggan'), true);
            }
        });
        
        // =================================================================
        // LISTENER UNTUK SWEETALERT
        // =================================================================
        // Listener untuk notifikasi (Toast)
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

        // Listener untuk konfirmasi update
        Livewire.on('show-update-confirmation', (event) => {
            Swal.fire({
                title: 'Update transaksi ini?',
                text: "Perubahan pada stok akan diterapkan. Pastikan data sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('updateConfirmed');
                }
            });
        });
        
    });
</script>
@endpush