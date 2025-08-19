@extends('layouts.admin')

@section('content')
    {{-- Nama komponen Alpine diubah agar unik untuk halaman ini --}}
    <div class="p-4 sm:p-6 lg:p-8 space-y-6" x-data="classShowPageData()">

        {{-- 1. JUDUL HALAMAN & TOMBOL KEMBALI --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Detail Kelas: {{ $kela->nama_kelas }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    <span>Tingkat: {{ $kela->tingkat }}</span>
                    <span class="mx-2">|</span>
                    <span>Jurusan: {{ $kela->jurusan ?? '-' }}</span>
                    <span class="mx-2">|</span>
                    <span>
                        Jumlah Siswa:
                        <span class="font-medium text-gray-800">{{ $activeStudentCountInClass }}</span>
                        <span class="text-gray-400">/ {{ $totalStudentCountInClass }}</span>
                        (Aktif / Total)
                    </span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                {{-- <form action="{{ route('admin.classes.deactivateWithStudents', $kela) }}" method="POST"
                    onsubmit="return confirm('Nonaktifkan kelas dan seluruh siswa?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="btn-danger px-4 py-2 rounded text-white bg-red-600 hover:bg-red-700 text-sm font-medium">Nonaktifkan
                        Kelas & Siswa</button>
                </form> --}}
                {{-- <form action="{{ route('admin.classes.bulkUpdateStudents', $kela) }}" method="POST"
                    onsubmit="return confirm('Aktifkan semua siswa di kelas ini?');">
                    @csrf
                    <input type="hidden" name="bulk_action" value="activate">
                    @foreach ($students->where('is_active', false) as $siswa)
                        <input type="hidden" name="siswa_ids[]" value="{{ $siswa->id }}">
                    @endforeach
                    <button type="submit"
                        class="btn-success px-4 py-2 rounded text-white bg-green-600 hover:bg-green-700 text-sm font-medium"
                        @if ($students->where('is_active', false)->count() == 0) disabled @endif>
                        Aktifkan Semua Siswa
                    </button>
                </form> --}}
            </div>
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



        {{-- 3. FILTER STATUS SISWA (GET) --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.classes.show', $kela) }}" id="filterSiswaFormOnPage"
                class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-grow w-full sm:w-auto">
                    <label for="status_siswa_filter_select" class="form-label">Tampilkan Siswa:</label>
                    <select name="status_siswa" id="status_siswa_filter_select" class="form-select"
                        onchange="this.form.submit()">
                        <option value="1" {{ $filterStatusSiswa == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $filterStatusSiswa == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="all" {{ $filterStatusSiswa == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- 4. FORM UTAMA UNTUK AKSI MASSAL (dibungkus sampai tabel siswa) --}}
        {{-- 4. FORM UTAMA UNTUK AKSI MASSAL (hanya membungkus opsi dan tombol massal, tabel di luar) --}}
        <form method="POST" action="{{ route('admin.classes.bulkUpdateStudents', $kela) }}" id="bulkActionForm"
            x-ref="bulkActionForm">
            @csrf
            <!-- Hidden siswa_ids[] akan diisi oleh JS sebelum submit -->
            <template x-for="id in selectedSiswaIds" :key="id">
                <input type="hidden" name="siswa_ids[]" :value="id">
            </template>
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 space-y-6">
                {{-- Opsi Aksi Massal --}}
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
                        <select name="target_kelas_id" id="target_kelas_dropdown" class="form-select" x-model="targetKelasId">
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
                        x-bind:disabled="selectedSiswaIds.length === 0 || !selectedBulkAction || (selectedBulkAction === 'move_class' && !targetKelasId)"
                        class="btn-primary w-400 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed">
                        Terapkan Aksi ke <span x-text="selectedSiswaIds.length"> </span> Siswa Terpilih
                    </button>
                </div>
            </div> {{-- akhir .bg-white --}}
            {{-- Hidden input siswa_ids[] akan diisi via JS --}}
        </form> {{-- Akhir Form Utama --}}

        {{-- Tabel Daftar Siswa --}}
        <h3 class="text-lg font-medium text-gray-700 mb-1">Daftar Siswa di Kelas Ini</h3>
        @if ($students->count() > 0)
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 w-10 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAllDisplayedStudents()"
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
                        @foreach ($students as $siswa)
                            <tr>
                                <td class="p-3 text-center">
                                    <input type="checkbox" value="{{ $siswa->id }}" x-model="selectedSiswaIds"
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
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('classShowPageData', () => ({
                selectedSiswaIds: [],
                selectAll: false,
                selectedBulkAction: '',
                targetKelasId: '',
                // ID siswa yang ditampilkan (dari PHP)
                allDisplayedStudentIds: @json($students->pluck('id')->map(fn($id) => (string) $id)),

                toggleSelectAllDisplayedStudents() {
                    this.selectedSiswaIds = [];
                    if (this.selectAll) {
                        this.selectedSiswaIds = [...this.allDisplayedStudentIds];
                    }
                },

                init() {
                    this.$watch('selectedBulkAction', value => {
                        if (value !== 'move_class') {
                            this.targetKelasId = '';
                        }
                    });

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
