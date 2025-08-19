@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Kelas</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data kelas di sekolah.</p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <div class="flex items-center space-x-2">
                    <button type="button" @click="openCreateClassModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Kelas
                    </button>
                    <a href="{{ route('admin.classes.promotionForm') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Kenaikan Kelas
                    </a>
                </div>
            @endif
        </div>

        {{-- BAGIAN FILTER YANG DIPERBAIKI --}}
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
            <form method="GET" action="{{ route('admin.classes.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end"> {{-- Grid diubah ke 3 kolom --}}
                {{-- Filter Tingkat (Tetap) --}}
                <div>
                    <label for="filter_tingkat" class="form-label">Filter Tingkat</label>
                    <select name="tingkat" id="filter_tingkat" class="form-select">
                        <option value="">Semua Tingkat</option>
                        @foreach ($tingkatOptions as $tingkat)
                            <option value="{{ $tingkat }}" {{ ($filterTingkat ?? '') == $tingkat ? 'selected' : '' }}>
                                Tingkat {{ $tingkat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PENGGANTI FILTER WALI KELAS --}}
                <div>
                    <label for="search_nama_kelas" class="form-label">Cari Nama Kelas</label>
                    <input type="text" name="search_nama_kelas" id="search_nama_kelas" class="form-input"
                        placeholder="Masukkan nama kelas..." value="{{ $filterNamaKelas ?? '' }}">
                </div>

                {{-- Tombol Filter dan Reset --}}
                <div class="flex space-x-2">
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filter
                    </button>
                    <a href="{{ route('admin.classes.index') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Pesan Sukses/Error (Tetap Sama) --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none" style="min-height: 120px;">
                <div
                    class="bg-white border border-green-200 rounded-xl shadow-lg px-6 py-4 flex items-center space-x-3 pointer-events-auto">
                    <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-green-700 font-semibold text-base">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none" style="min-height: 120px;">
                <div
                    class="bg-white border border-red-200 rounded-xl shadow-lg px-6 py-4 flex items-center space-x-3 pointer-events-auto">
                    <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-red-700 font-semibold text-base">{{ session('error') }}</span>
                </div>
            </div>
        @endif
        @if ($errors->any())
            {{-- ... kode notifikasi ... --}}
        @endif

        {{-- BAGIAN TABEL YANG DIPERBAIKI --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 class-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col">Nama Kelas</th>
                            <th scope="col" class="text-center">Tingkat</th>
                            <th scope="col">Jurusan</th>
                            <th scope="col" class="text-center">Jumlah Siswa</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($kelas as $item)
                            <tr>
                                <td class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.classes.show', $item) }}"
                                        class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                        {{ $item->nama_kelas }}
                                    </a>
                                </td>
                                <td class="text-sm text-gray-500 text-center">{{ $item->tingkat }}</td>
                                <td class="text-sm text-gray-500">{{ $item->jurusan ?? '-' }}</td>
                                <td class="text-sm text-gray-500 text-center">{{ $item->active_students_count }}</td>
                                <td class="text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{-- Tombol Edit: Pass data sebagai JSON ke fungsi Alpine --}}
                                            <button type="button" title="Edit Kelas" class="action-button"
                                                @click="openEditClassModal({
                                                    id: {{ $item->id }},
                                                    nama: '{{ addslashes($item->nama_kelas) }}',
                                                    tingkat: {{ $item->tingkat }},
                                                    jurusan: '{{ addslashes($item->jurusan ?? '') }}',
                                                    jumlahSiswa: {{ $item->students->count() }}
                                                })">
                                                <i data-lucide="edit-2"></i>
                                            </button>

                                            {{-- Tombol Arsip/Nonaktifkan Kelas --}}
                                            <form action="{{ route('admin.classes.deactivateWithStudents', $item) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Arsipkan/nonaktifkan kelas dan seluruh siswa?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="Arsipkan/Nonaktifkan Kelas"
                                                    class="action-button text-red-600 hover:text-white hover:bg-red-600">
                                                    <i data-lucide="archive"></i>
                                                </button>
                                            </form>

                                            {{-- Tombol Aktifkan Semua Siswa --}}
                                            <form action="{{ route('admin.classes.bulkUpdateStudents', $item) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Aktifkan semua siswa di kelas ini?');">
                                                @csrf
                                                <input type="hidden" name="bulk_action" value="activate">
                                                @foreach ($item->students->where('is_active', false) as $siswa)
                                                    <input type="hidden" name="siswa_ids[]" value="{{ $siswa->id }}">
                                                @endforeach
                                                <button type="submit" title="Aktifkan Semua Siswa"
                                                    class="action-button text-green-600 hover:text-white hover:bg-green-600"
                                                    @if ($item->students->where('is_active', false)->count() == 0) disabled @endif>
                                                    <i data-lucide="user-check"></i>
                                                </button>
                                            </form>

                                            {{-- Tombol Hapus (Tetap Sama) --}}
                                            <form action="{{ route('admin.classes.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('PERINGATAN: Menghapus kelas ini tidak dapat dibatalkan (jika tidak ada siswa). Yakin?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Kelas"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500"> {{-- Colspan diubah dari 6 menjadi 5 --}}
                                    Belum ada data kelas.
                                    @if (auth()->user()->isSuperAdmin())
                                        <button type="button" @click="openCreateClassModal()"
                                            class="text-indigo-600 hover:underline ml-2">Tambah Kelas Baru</button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Include Modal --}}
        @include('admin.classes._modal')

    </div>

    {{-- ... (kode style dan script lainnya tetap sama) ... --}}
@endsection
