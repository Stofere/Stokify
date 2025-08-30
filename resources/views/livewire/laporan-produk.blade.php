<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Stok & Katalog Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- BAGIAN 1: KONTROL FILTER DAN AKSI --}}
                    <div class="md:flex md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
                        {{-- Filter Dropdown --}}
                        <div class="flex-1 min-w-0">
                            <label for="filterKategori" class="text-sm font-medium text-gray-700">Filter berdasarkan Kategori</label>
                            <select wire:model.live="filterKategori" id="filterKategori" class="mt-1 block w-full md:w-auto form-select rounded-md shadow-sm border-gray-300">
                                <option value="">Tampilkan Semua Kategori</option>
                                @foreach($semuaKategori as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Tombol Aksi --}}
                        <div class="flex space-x-3">
                            <button 
                                wire:click="exportExcel"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" ...> ... </svg>
                                Export Excel
                            </button>
                            <button 
                                wire:click="cetakPdf"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" ...> ... </svg>
                                Cetak PDF
                            </button>
                        </div>
                    </div>

                    {{-- BAGIAN 2: TABEL LAPORAN PRODUK --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $currentKategori = null;
                                    $counter = 1;
                                @endphp
                                @forelse($produks as $produk)
                                    {{-- Jika filter "Semua Kategori" aktif, tampilkan header grup --}}
                                    @if(empty($filterKategori) && $produk->kategori->nama !== $currentKategori)
                                        <tr class="bg-gray-100 font-bold">
                                            <td colspan="8" class="px-6 py-2 text-gray-700">{{ $produk->kategori->nama }}</td>
                                        </tr>
                                        @php
                                            $currentKategori = $produk->kategori->nama;
                                            $counter = 1; // Reset nomor urut setiap kali ganti kategori
                                        @endphp
                                    @endif
                                    <tr wire:key="{{ $produk->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $counter++ }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->kode_barang ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $produk->nama_produk }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->kategori->nama ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{-- [FIX] Gunakan helper untuk format yang konsisten --}}
                                            @if($produk->lacak_stok)
                                                {{ format_jumlah($produk->stok, $produk->satuan) }}
                                            @else
                                                ∞
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ $produk->satuan }} </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produk->lokasi ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">Rp {{ number_format($produk->harga_jual_standar, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada produk yang cocok dengan filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginasi --}}
                    <div class="mt-4">
                        {{ $produks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>