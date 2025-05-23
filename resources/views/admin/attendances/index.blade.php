@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Data Presensi</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data kehadiran, izin, sakit, atau absen pengguna.</p>
            </div>
            {{-- Tombol Tambah HANYA untuk Super Admin --}}
            @if (Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.attendances.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Data Presensi
                </a>
            @endif
        </div>
        {{-- Include Partial Alert (buat file ini jika belum) --}}
        {{-- resources/views/partials/common/_alert.blade.php --}}
        @if (View::exists('partials.common._alert'))
            @include('partials.common._alert')
        @else
            {{-- Fallback jika partial tidak ada --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded text-sm">
                    {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">{{ session('error') }}
                </div>
            @endif
        @endif


        {{-- Filter Sederhana --}}
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
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam
                                Masuk</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Selfie</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    {{-- Contoh di resources/views/admin/attendances/index.blade.php (bagian tbody tabel) --}}

                    {{-- Contoh di resources/views/admin/attendances/index.blade.php (bagian tbody tabel) --}}

                    {{-- Contoh di resources/views/admin/attendances/index.blade.php (bagian tbody tabel) --}}

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                {{-- ... kolom data presensi (Nama, Tanggal, Status, Jam Masuk, dll.) ... --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $attendance->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $attendance->tanggal->isoFormat('dddd, D MMMM Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->status }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $attendance->jam_masuk ?? '-' }}</td>
                                {{-- ... kolom lain jika ada (misal lokasi, selfie) ... --}}

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        {{-- Tombol Edit: Hanya tampil jika user lolos Gate manageTodayAttendanceAdmin (Super Admin ATAU terjadwal piket hari ini) --}}
                                        @can('manageTodayAttendanceAdmin')
                                            {{-- Note: Controller sudah membatasi edit hanya hari ini untuk non-admin --}}
                                            <a href="{{ route('admin.attendances.edit', $attendance) }}" title="Edit Presensi"
                                                class="action-button text-blue-400 hover:text-blue-600 hover:bg-blue-50">
                                                <i data-lucide="edit-2"></i>
                                            </a>
                                        @endcan

                                        {{-- Tombol Hapus: Tetap hanya untuk Super Admin --}}
                                        @if (Auth::user()->isSuperAdmin())
                                            <form action="{{ route('admin.attendances.destroy', $attendance) }}"
                                                method="POST" onsubmit="return confirm('Yakin hapus data presensi ini?');"
                                                class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Hapus Presensi"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Tampilkan penanda jika tidak ada aksi yang tersedia untuk baris ini --}}
                                        {{-- Tampilkan jika BUKAN Super Admin DAN tidak bisa manageAttendanceAdmin --}}
                                        @if (!Auth::user()->isSuperAdmin() && !Gate::allows('manageTodayAttendanceAdmin'))
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="99" class="text-center py-10 text-gray-500">Tidak ada data presensi
                                    ditemukan.</td> {{-- Sesuaikan colspan --}}
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Link Paginasi --}}
            @if ($attendances->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attendances->appends($filters)->links() }} {{-- Tambah appends agar filter terbawa saat ganti halaman --}}
                </div>
            @endif
        </div>
    </div>

    {{-- Style --}}
    <style>
        .form-label {
            /* ... */
        }

        .form-input,
        .form-select {
            /* ... */
        }

        .action-button {
            /* ... */
        }

        .action-button:hover {
            /* ... */
        }

        .action-button i {
            /* ... */
        }

        .status-badge {
            /* ... */
        }

        .badge-red {
            /* ... */
        }

        .badge-yellow {
            /* ... */
        }

        .badge-green {
            /* ... */
        }

        .badge-gray {
            /* ... */
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        .badge-purple {
            background-color: #ede9fe;
            color: #7c3aed;
        }

        .btn-primary {
            /* ... */
        }

        .btn-secondary {
            /* ... */
        }

        /* Style untuk alert (contoh) */
        .alert-success {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-danger {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
    </style>
@endsection
