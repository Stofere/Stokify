<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Transaksi Penjualan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- BAGIAN 1: FILTER & PENCARIAN --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="md:col-span-2">
                             <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Kode Nota / Nama Pelanggan..." class="w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                        <div>
                            <label for="startDate" class="text-sm text-gray-500">Dari Tanggal</label>
                            <input type="date" id="startDate" wire:model.live="startDate" class="w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                        <div>
                            <label for="endDate" class="text-sm text-gray-500">Sampai Tanggal</label>
                            <input type="date" id="endDate" wire:model.live="endDate" class="w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                    </div>

                    {{-- BAGIAN 2: TABEL RIWAYAT --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Nota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marketing</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transaksis as $transaksi)
                                <tr wire:key="{{ $transaksi->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">{{ $transaksi->kode_transaksi }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->isoFormat('dddd, D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $transaksi->pelanggan->nama ?? 'Umum' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaksi->marketing }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-semibold">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button wire:click="showDetail({{ $transaksi->id }})" class="text-indigo-600 hover:text-indigo-900">Lihat Detail</button>
                                        @if(auth()->user()->peran === 'admin')
                                            <a href="{{ route('penjualan.edit', $transaksi->id) }}" wire:navigate class="text-yellow-600 hover:text-yellow-900 ml-4">Edit</a>
                                        @endif
                                        <button wire:click="cetakInvoice({{ $transaksi->id }})" class="text-green-600 ml-4">Cetak</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada transaksi yang cocok dengan filter Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $transaksis->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- BAGIAN 3: MODAL DETAIL TRANSAKSI --}}
    @if($isDetailModalOpen && $selectedTransaksi)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Detail Transaksi: {{ $selectedTransaksi->kode_transaksi }}
                            </h3>
                            <div class="mt-4 text-sm text-gray-600 space-y-2">
                                <p><strong>Pelanggan:</strong> {{ $selectedTransaksi->pelanggan->nama ?? 'Umum' }}</p>
                                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($selectedTransaksi->tanggal_transaksi)->isoFormat('dddd, D MMMM YYYY') }}</p>
                                <p><strong>Marketing:</strong> {{ $selectedTransaksi->marketing }}</p>
                            </div>
                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-semibold mb-2">Item yang Dibeli:</h4>
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 text-xs uppercase">
                                        <tr>
                                            <th class="px-2 py-1 text-left">Produk</th>
                                            <th class="px-2 py-1 text-center">Jumlah</th>
                                            <th class="px-2 py-1 text-right">Harga Satuan</th>
                                            <th class="px-2 py-1 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedTransaksi->detail as $item)
                                        <tr class="border-b">
                                            <td class="px-2 py-1">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</td>
                                            <td class="px-2 py-1 text-center">
                                                {{ format_jumlah($item->jumlah, $item->satuan_saat_transaksi) }} {{ $item->satuan_saat_transaksi }}
                                            </td>
                                            <td class="px-2 py-1 text-right">Rp {{ number_format($item->harga_satuan_deal, 0, ',', '.') }}</td>
                                            <td class="px-2 py-1 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="font-bold">
                                        <tr>
                                            <td colspan="3" class="px-2 py-1 text-right">Total Keseluruhan:</td>
                                            <td class="px-2 py-1 text-right">Rp {{ number_format($selectedTransaksi->total_harga, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>