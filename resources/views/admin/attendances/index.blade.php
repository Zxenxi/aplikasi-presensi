@extends('layouts.admin')

@section('content')
    {{-- 1. Tambahkan x-data di sini --}}
    <div x-data="{ showModal: false, modalImageUrl: '' }" class="p-4 sm:p-6 lg:p-8 space-y-6">

        {{-- Judul Halaman dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Data Presensi</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data kehadiran, izin, sakit, atau absen pengguna.</p>
            </div>
            @if (Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.attendances.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Data Presensi
                </a>
            @endif
        </div>

        {{-- Notifikasi Sukses atau Gagal --}}
        @if (View::exists('partials.common._alert'))
            @include('partials.common._alert')
        @else
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded text-sm">
                    {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">{{ session('error') }}
                </div>
            @endif
        @endif

        {{-- Filter Pencarian --}}
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
            <form method="GET" action="{{ route('admin.attendances.index') }}"
                class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="search_name" class="form-label">Cari Nama</label>
                    <input type="text" name="search_name" id="search_name" value="{{ $filters['search_name'] ?? '' }}"
                        class="form-input" placeholder="Masukkan nama...">
                </div>
                <div>
                    <label for="filter_date" class="form-label">Tanggal</label>
                    <input type="date" name="filter_date" id="filter_date" value="{{ $filters['filter_date'] ?? '' }}"
                        class="form-input">
                </div>
                <div>
                    <label for="filter_status" class="form-label">Status</label>
                    <select id="filter_status" name="filter_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ ($filters['filter_status'] ?? '') == 'Hadir' ? 'selected' : '' }}>Hadir
                        </option>
                        <option value="Telat" {{ ($filters['filter_status'] ?? '') == 'Telat' ? 'selected' : '' }}>Telat
                        </option>
                        <option value="Izin" {{ ($filters['filter_status'] ?? '') == 'Izin' ? 'selected' : '' }}>Izin
                        </option>
                        <option value="Sakit" {{ ($filters['filter_status'] ?? '') == 'Sakit' ? 'selected' : '' }}>Sakit
                        </option>
                        <option value="Absen" {{ ($filters['filter_status'] ?? '') == 'Absen' ? 'selected' : '' }}>Absen
                        </option>
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-start md:justify-end space-x-2">
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="search" class="w-4 h-4 mr-1"></i> Cari
                    </button>
                    <a href="{{ route('admin.attendances.index') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Data Presensi --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kelas
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jam Masuk
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Selfie
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $att)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $att->tanggal->isoFormat('D MMM YY') }}
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.attendances.user_history', $att->user) }}" class="text-indigo-600 hover:text-indigo-900" title="Lihat riwayat presensi {{ $att->user->name }}">
                                        {{ $att->user->name ?? 'N/A' }}
                                    </a>
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ $att->user?->role === 'Siswa' ? $att->user?->kelas?->nama_kelas ?? '-' : '-' }}
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @php
                                        $statusBadge = match ($att->status) {
                                            'Hadir' => 'badge-green',
                                            'Telat' => 'badge-yellow',
                                            'Izin' => 'badge-blue',
                                            'Sakit' => 'badge-purple',
                                            default => 'badge-red',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusBadge }}">{{ $att->status }}</span>

                                    @if ($att->remarks)
                                        <span class="block text-xs text-gray-400 italic mt-1" title="Keterangan">
                                            {{ $att->remarks }}
                                        </span>
                                    @endif
                                </td>

                                {{-- 2. Ubah bagian Selfie di sini --}}
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                    @if ($att->selfie_path && Storage::disk('public')->exists($att->selfie_path))
                                        <button type="button"
                                            @click="modalImageUrl = '{{ Storage::url($att->selfie_path) }}'; showModal = true"
                                            class="focus:outline-none">
                                            <img src="{{ Storage::url($att->selfie_path) }}" alt="Lihat Selfie"
                                                class="w-10 h-10 object-cover rounded-full shadow-md inline-block cursor-pointer transition-transform duration-200 hover:scale-110"
                                                loading="lazy">
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if (!is_null($att->latitude))
                                        @if (is_null($att->is_location_valid))
                                            <span class="status-badge badge-gray">N/A</span>
                                        @elseif($att->is_location_valid)
                                            <span class="status-badge badge-green">Valid</span>
                                        @else
                                            <span class="status-badge badge-red">Tidak Valid</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">Manual</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        @php
                                            $canEdit = Auth::user()->isSuperAdmin();
                                            if (Auth::user()->isPetugasPiket()) {
                                                $canEdit = true;
                                            }
                                        @endphp

                                        @if ($canEdit)
                                            <a href="{{ route('admin.attendances.edit', $att) }}" title="Edit Presensi"
                                                class="action-button">
                                                <i data-lucide="edit-2"></i>
                                            </a>
                                        @endif

                                        @if (Auth::user()->isSuperAdmin())
                                            <form action="{{ route('admin.attendances.destroy', $att) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus data presensi ini? Foto selfie terkait juga akan dihapus.');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Presensi"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (!$canEdit && !Auth::user()->isSuperAdmin())
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-gray-500">
                                    Tidak ada data presensi ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attendances->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attendances->appends($filters)->links() }}
                </div>
            @endif
        </div>

        {{-- 3. Tambahkan kode Modal di sini, sebelum div penutup utama --}}
        <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @keydown.escape.window="showModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4" style="display: none;">

            <div @click="showModal = false" class="absolute inset-0"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl max-h-full">
                <img :src="modalImageUrl" alt="Selfie Presensi" class="object-contain rounded-lg max-h-[90vh]">

                <button @click="showModal = false"
                    class="absolute -top-3 -right-3 flex items-center justify-center w-8 h-8 bg-red-600 text-white rounded-full hover:bg-red-700 focus:outline-none">
                    &times;
                </button>
            </div>
        </div>

    </div>
@endsection
