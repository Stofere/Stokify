<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Welcome Message --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-semibold">Selamat Datang Kembali, {{ Auth::user()->nama }}!</h3>
                    <p class="text-gray-600 mt-1">Berikut adalah ringkasan aktivitas penjualan Anda hari ini.</p>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Card 1: Penjualan Hari Ini -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-start space-x-4">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-500">Total Penjualan Hari Ini</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                <!-- Card 2: Nota Hari Ini -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-start space-x-4">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-500">Jumlah Nota Hari Ini</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $notaHariIni }}</p>
                    </div>
                </div>

                <!-- Card 3: Link Cepat (Contoh) -->
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-start space-x-4">
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-500">Aksi Cepat</h3>
                        <a href="{{ route('penjualan.buat') }}" class="mt-2 inline-block text-blue-600 hover:underline">
                            + Buat Transaksi Baru
                        </a>
                    </div>
                </div>
            </div>

            {{-- Produk Terlaris --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Produk Terlaris Minggu Ini</h3>
                    <ul class="divide-y divide-gray-200">
                        @forelse($produkTerlaris as $item)
                        <li class="flex justify-between items-center py-3">
                            <div class="flex items-center space-x-4">
                                @if($item->produk && $item->produk->foto)
                                    <img src="{{ asset('storage/' . $item->produk->foto) }}" alt="{{ $item->produk->nama_produk }}" class="w-12 h-12 object-cover rounded-md">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</p>
                                    <p class="text-sm text-gray-500">{{ $item->produk->kode_barang ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <span class="font-bold bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                {{ format_jumlah($item->total_terjual, $item->produk->satuan ?? 'pcs') }} {{ $item->produk->satuan ?? '' }}
                            </span>
                        </li>
                        @empty
                        <p class="text-gray-500 py-4">Belum ada penjualan minggu ini.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>