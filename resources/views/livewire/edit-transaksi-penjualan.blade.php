<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Transaksi: <span class="font-mono">{{ $transaksi->kode_transaksi }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- BAGIAN 1: INFO TRANSAKSI --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="tanggal_transaksi" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                            <input type="date" wire:model="tanggal_transaksi" id="tanggal_transaksi" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('tanggal_transaksi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="id_pelanggan" class="block text-sm font-medium text-gray-700">Pelanggan</label>
                            <select wire:model.live="id_pelanggan" id="id_pelanggan" class="mt-1 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="">Pilih Pelanggan (Umum)</option>
                                @foreach($semuaPelanggan as $pelanggan)
                                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                                @endforeach
                            </select>
                            @error('id_pelanggan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="marketing" class="block text-sm font-medium text-gray-700">Marketing</label>
                            {{-- Ubah wire:model dan perulangan --}}
                            <select wire:model="id_marketing" id="marketing" class="w-full mt-1 ...">
                                <option value="">Pilih Marketing</option>
                                @foreach($semuaMarketing as $marketing)
                                    <option value="{{ $marketing->id }}">{{ $marketing->nama }}</option>
                                @endforeach
                            </select>
                            @error('id_marketing') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BAGIAN 2: PENCARIAN & PENAMBAHAN PRODUK --}}
                    <div class="border-t pt-6">
                        <label for="search_produk" class="block text-sm font-medium text-gray-700">Cari dan Tambah Produk</label>
                        <input type="text" wire:model.live.debounce.300ms="searchProduk" id="search_produk" placeholder="Ketik nama atau kode produk..." class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @if(strlen($searchProduk) > 2 && !empty($hasilPencarian) && $hasilPencarian->isNotEmpty())
                            <ul class="border border-gray-300 rounded-md mt-1 max-h-60 overflow-y-auto bg-white shadow-lg z-10">
                                @foreach($hasilPencarian as $produk)
                                    <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer" wire:click="tambahProdukKeKeranjang({{ $produk->id }})">
                                        <p class="font-semibold">{{ $produk->nama_produk }}</p>
                                        <p class="text-sm text-gray-500">Stok: {{ $produk->stok }} | Harga: Rp {{ number_format($produk->harga_jual_standar, 0, ',', '.') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- BAGIAN 3: KERANJANG BELANJA --}}
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900">Daftar Barang</h3>
                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($keranjang as $index => $item)
                                        <tr wire:key="keranjang-{{ $index }}">
                                            <td class="px-4 py-2">{{ $item['nama_produk'] }}</td>
                                            <td class="px-4 py-2"><input type="number" step="0.01" wire:model.live.debounce.300ms="keranjang.{{ $index }}.jumlah" class="w-24 border-gray-300 rounded-md shadow-sm"></td>
                                            <td class="px-4 py-2"><input
                                                type="number"
                                                step="1"
                                                inputmode="numeric"
                                                wire:model.live="keranjang.{{ $index }}.harga_satuan_deal"
                                                class="w-40 border-gray-300 rounded-md shadow-sm"
                                                x-data
                                                @keydown="
                                                    if (!['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'].includes($event.key) && isNaN(Number($event.key))) {
                                                        $event.preventDefault();
                                                    }
                                                "
                                            >
                                            </td>
                                            <td class="px-4 py-2">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                            <td class="px-4 py-2"><button wire:click="hapusItemDariKeranjang({{ $index }})" class="text-red-500 hover:text-red-700">Hapus</button></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-gray-500">Keranjang masih kosong.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- BAGIAN 4: RINGKASAN & SIMPAN --}}
                    <div class="border-t pt-6 flex justify-between items-end">
                        <textarea wire:model.defer="catatan" placeholder="Catatan transaksi (opsional)..." rows="3" class="w-1/2 border-gray-300 rounded-md shadow-sm"></textarea>
                        <div class="text-right">
                            @if($transaksi->edited_at)
                                <div class="text-xs text-gray-500 mb-2">
                                    Terakhir diedit oleh {{ $transaksi->editor->nama ?? 'N/A' }} <br>
                                    pada {{ Carbon\Carbon::parse($transaksi->edited_at)->isoFormat('D MMM YYYY, HH:mm') }}
                                </div>
                            @endif
                            <div class="text-xl font-bold">
                                Total: <span class="text-blue-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-4 flex justify-end items-center space-x-3">
                                {{-- TOMBOL BATAL BARU --}}
                                <a href="{{ route('penjualan.riwayat') }}" wire:navigate
                                class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                                    Batal
                                </a>

                                {{-- Tombol Update (tidak berubah) --}}
                                <button wire:click="konfirmasiUpdateTransaksi" 
                                        class="px-6 py-3 bg-yellow-500 text-white font-semibold rounded-md hover:bg-yellow-600 disabled:opacity-50" 
                                        @if(empty($keranjang)) disabled @endif>
                                    Update Transaksi
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')

    @endpush

    <div 
        x-data
        x-on:show-notification.window="Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, didOpen: (toast) => {toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer);}}).fire({icon: $event.detail.type || 'success', title: $event.detail.message})"
        x-on:show-update-confirmation.window="Swal.fire({title: 'Update transaksi ini?', text: 'Perubahan pada stok akan diterapkan secara otomatis. Pastikan data sudah benar.', icon: 'question', showCancelButton: true, confirmButtonColor: '#f59e0b', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Update!', cancelButtonText: 'Batal'}).then((result) => {if (result.isConfirmed) { Livewire.dispatch('updateConfirmed') }})"
    >
    </div>
</div>