@extends('layouts.admin')

@section('content')
    {{-- Nama komponen Alpine diubah agar unik untuk halaman ini --}}
    <div class="p-4 sm:p-6 lg:p-8 space-y-6" x-data="classShowPageData()">

        {{-- 1. JUDUL HALAMAN & TOMBOL KEMBALI --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Detail Kelas: {{ $kela->nama_kelas }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Tingkat: {{ $kela->tingkat }} |
                    Jurusan: {{ $kela->jurusan ?? '-' }} |
                    Wali Kelas: {{ $kela->waliKelas->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('admin.classes.index') }}" class="text-sm text-indigo-600 hover:underline">
                &larr; Kembali ke Manajemen Kelas
            </a>
        </div>

        {{-- 2. NOTIFIKASI --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                {{ session('warning') }}
            </div>
        @endif


        {{-- 3. FORM UTAMA UNTUK AKSI MASSAL --}}
        {{-- Pastikan form ini tidak tersembunyi oleh kondisi x-show yang salah --}}
        <form method="POST" action="{{ route('admin.classes.bulkUpdateStudents', $kela) }}">
            @csrf
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 space-y-6">

                {{-- Filter Tampilan Siswa (Aktif/Tidak Aktif/Semua) --}}
                <div class="mb-4">
                    {{-- Form ini hanya untuk reload halaman dengan parameter GET, bukan bagian dari form POST utama --}}
                    <form method="GET" action="{{ route('admin.classes.show', $kela) }}" id="filterSiswaFormOnPage"
                        class="flex items-end space-x-2">
                        <div>
                            <label for="status_siswa_filter_select" class="form-label text-sm">Tampilkan Siswa:</label>
                            <select name="status_siswa" id="status_siswa_filter_select" class="form-select form-select-sm"
                                onchange="document.getElementById('filterSiswaFormOnPage').submit()">
                                <option value="1" @if ($filterStatusSiswa == '1') selected @endif>Aktif</option>
                                <option value="0" @if ($filterStatusSiswa == '0') selected @endif>Tidak Aktif
                                </option>
                                <option value="all" @if ($filterStatusSiswa == 'all') selected @endif>Semua</option>
                            </select>
                        </div>
                    </form>
                </div>

                {{-- Opsi Aksi Massal --}}
                {{-- Pastikan blok ini tidak tersembunyi --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 pb-4">
                    <div class="flex-grow w-full sm:w-auto">
                        <label for="bulk_action_dropdown" class="form-label">Pilih Aksi Massal:</label>
                        <select name="bulk_action" id="bulk_action_dropdown" x-model="selectedBulkAction"
                            class="form-select">
                            <option value="">-- Pilih Aksi --</option>
                            <option value="activate">Aktifkan Siswa Terpilih</option>
                            <option value="deactivate">Nonaktifkan Siswa Terpilih</option>
                            <option value="move_class">Pindahkan ke Kelas Lain</option>
                        </select>
                    </div>

                    <div x-show="selectedBulkAction === 'move_class'" x-transition class="flex-grow w-full sm:w-auto">
                        <label for="target_kelas_dropdown" class="form-label">Pilih Kelas Tujuan:</label>
                        <select name="target_kelas_id" id="target_kelas_dropdown" class="form-select">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach ($allKelas as $kelasOption)
                                @if ($kelasOption->id !== $kela->id)
                                    <option value="{{ $kelasOption->id }}">{{ $kelasOption->nama_kelas }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-b pb-4 mb-4">
                    <button type="submit"
                        x-bind:disabled="selectedSiswaIds.length === 0 || !selectedBulkAction || (selectedBulkAction === 'move_class' &&
                            !document.getElementById('target_kelas_dropdown').value)"
                        class="btn-primary w-400 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed">
                        Terapkan Aksi ke <span x-text="selectedSiswaIds.length"> </span> Siswa Terpilih
                    </button>
                </div>


                {{-- Tabel Daftar Siswa --}}
                <h3 class="text-lg font-medium text-gray-700 mb-1">Daftar Siswa di Kelas Ini</h3>
                @if ($kela->students->count() > 0)
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 w-10 text-center">
                                        <input type="checkbox" x-model="selectAll"
                                            @change="toggleSelectAllDisplayedStudents()"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    </th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Siswa</th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email</th>
                                    <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status Akun</th>
                                    <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($kela->students as $siswa)
                                    <tr>
                                        <td class="p-3 text-center">
                                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}"
                                                x-model="selectedSiswaIds"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </td>
                                        <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ $siswa->name }}</td>
                                        <td class="p-3 whitespace-nowrap text-sm text-gray-500">{{ $siswa->email }}</td>
                                        <td class="p-3 whitespace-nowrap text-sm text-center">
                                            @if ($siswa->is_active)
                                                <span class="status-badge badge-green">Aktif</span>
                                            @else
                                                <span class="status-badge badge-red">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td class="p-3 whitespace-nowrap text-sm text-center">
                                            <form action="{{ route('admin.users.toggleStatus', $siswa) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Anda yakin ingin {{ $siswa->is_active ? 'menonaktifkan' : 'mengaktifkan' }} siswa {{ $siswa->name }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    title="{{ $siswa->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                                    class="action-button {{ $siswa->is_active ? 'text-yellow-500 hover:text-yellow-700 hover:bg-yellow-100' : 'text-green-500 hover:text-green-700 hover:bg-green-100' }} p-1 rounded">
                                                    <i data-lucide="{{ $siswa->is_active ? 'user-x' : 'user-check' }}"
                                                        class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        Tidak ada siswa
                        {{ strtolower($filterStatusSiswa == '1' ? 'aktif' : ($filterStatusSiswa == '0' ? 'tidak aktif' : '')) }}
                        yang ditemukan di kelas ini.
                    </div>
                @endif
            </div> {{-- akhir .bg-white --}}
        </form> {{-- Akhir Form Utama --}}
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('classShowPageData', () => ({ // Ubah nama komponen data
                selectedSiswaIds: [],
                selectAll: false,
                selectedBulkAction: '',
                // ID siswa yang ditampilkan (dari PHP)
                allDisplayedStudentIds: @json($kela->students->pluck('id')->map(fn($id) => (string) $id)),

                toggleSelectAllDisplayedStudents() {
                    this.selectedSiswaIds = [];
                    if (this.selectAll) {
                        this.selectedSiswaIds = [...this.allDisplayedStudentIds];
                    }
                },

                init() {
                    // Kode untuk mengontrol tampilan dropdown kelas tujuan berdasarkan pilihan aksi massal
                    const bulkActionSelect = document.getElementById('bulk_action_dropdown');
                    const targetKelasDiv = document.querySelector(
                        '[x-show="selectedBulkAction === \'move_class\'"]'); // Cari div dengan x-show
                    const targetKelasSelect = document.getElementById('target_kelas_dropdown');

                    if (bulkActionSelect && targetKelasDiv && targetKelasSelect) {
                        // Set kondisi awal berdasarkan nilai x-model (selectedBulkAction)
                        // Alpine akan menangani show/hide div secara otomatis.
                        // Kita hanya perlu memastikan 'required' pada select target.
                        this.$watch('selectedBulkAction', value => {
                            if (value === 'move_class') {
                                targetKelasSelect.setAttribute('required', 'required');
                            } else {
                                targetKelasSelect.removeAttribute('required');
                                targetKelasSelect.value = '';
                            }
                        });
                        // Inisialisasi saat load halaman jika old value ada
                        if (this.selectedBulkAction === 'move_class') {
                            targetKelasSelect.setAttribute('required', 'required');
                        } else {
                            targetKelasSelect.removeAttribute('required');
                        }
                    }

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                }
            }));
        });
    </script>
@endpush
