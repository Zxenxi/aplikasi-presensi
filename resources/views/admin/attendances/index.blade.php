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
                <a href="{{ route('attendances.create') }}"
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
            <form method="GET" action="{{ route('attendances.index') }}"
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
                    <button type="submit" class="btn-primary px-4 py-2">
                        <i data-lucide="search" class="w-4 h-4 mr-1"></i> Cari
                    </button>
                    <a href="{{ route('attendances.index') }}" class="btn-secondary px-4 py-2">
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
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $att)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $att->tanggal->isoFormat('D MMM YYYY') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $att->user->name ?? 'N/A' }}</td>
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
                                    <span class="status-badge {{ $statusBadge }}"> {{ $att->status }} </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                    @if ($att->selfie_path && Storage::disk('public')->exists($att->selfie_path))
                                        <img src="{{ Storage::url($att->selfie_path) }}" alt="Selfie"
                                            class="w-8 h-8 object-cover rounded-full shadow inline-block" loading="lazy">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if (!is_null($att->latitude))
                                        {{-- Cek jika ada data GPS --}}
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
                                        {{-- Tombol Edit & Hapus HANYA untuk Super Admin --}}
                                        @if (Auth::user()->isSuperAdmin())
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('attendances.edit', $att) }}" title="Edit Presensi"
                                                class="action-button">
                                                <i data-lucide="edit-2"></i>
                                            </a>
                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('attendances.destroy', $att) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus data presensi ini? Foto selfie terkait juga akan dihapus.');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Presensi"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Petugas Piket tidak bisa edit/hapus --}}
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-gray-500">Tidak ada data presensi
                                    ditemukan.</td>
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
