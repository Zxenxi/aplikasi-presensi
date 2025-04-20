@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Laporan Presensi</h1>
            <p class="text-sm text-gray-500 mt-1">Filter dan lihat data presensi pengguna.</p>
        </div>

        {{-- Form Filter --}}
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-6">
            <form method="GET" action="{{ route('admin.reports.index') }}"
                class="space-y-4 md:space-y-0 md:grid md:grid-cols-12 md:gap-4 md:items-end">
                {{-- Tanggal Mulai --}}
                <div class="col-span-6 sm:col-span-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                        value="{{ $filters['tanggal_mulai'] ?? date('Y-m-d') }}" {{-- Default hari ini --}} class="form-input">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="col-span-6 sm:col-span-3">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" required
                        value="{{ $filters['tanggal_selesai'] ?? date('Y-m-d') }}" class="form-input">
                </div>

                {{-- Tipe User --}}
                <div class="col-span-6 sm:col-span-2" x-data="{ tipeUser: '{{ $filters['tipe_user'] ?? '' }}' }">
                    <label for="tipe_user" class="form-label">Tipe User</label>
                    <select id="tipe_user" name="tipe_user" x-model="tipeUser" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="Guru">Guru</option>
                        <option value="Siswa">Siswa</option>
                    </select>
                </div>

                {{-- Kelas (Hanya tampil jika tipe = Siswa) --}}
                <div class="col-span-6 sm:col-span-2" x-show="tipeUser === 'Siswa'" x-transition>
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}"
                                {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status Presensi --}}
                <div class="col-span-6 sm:col-span-2">
                    <label for="status_presensi" class="form-label">Status</label>
                    <select id="status_presensi" name="status_presensi" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ ($filters['status_presensi'] ?? '') == 'Hadir' ? 'selected' : '' }}>Hadir
                        </option>
                        <option value="Telat" {{ ($filters['status_presensi'] ?? '') == 'Telat' ? 'selected' : '' }}>Telat
                        </option>
                        <option value="Izin" {{ ($filters['status_presensi'] ?? '') == 'Izin' ? 'selected' : '' }}>Izin
                        </option>
                        <option value="Sakit" {{ ($filters['status_presensi'] ?? '') == 'Sakit' ? 'selected' : '' }}>Sakit
                        </option>
                        <option value="Absen" {{ ($filters['status_presensi'] ?? '') == 'Absen' ? 'selected' : '' }}>Absen
                        </option>
                    </select>
                </div>


                {{-- Tombol Submit --}}
                <div class="col-span-12 sm:col-span-2 md:col-span-1 ">
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
                {{-- Tombol Reset (opsional) --}}
                <div class="col-span-12 sm:col-span-2 md:col-span-1 ">
                    <a href="{{ route('admin.reports.index') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Hasil Laporan --}}
        @isset($results)
            <div class="p-4 border-b flex justify-between items-center flex-wrap gap-2">
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Hasil Laporan Presensi ...</h3>
                    <p class="text-sm text-gray-600 mt-1">Ditemukan {{ $results->count() }} data presensi.</p>
                </div>
                {{-- Grup Tombol Export --}}
                <div class="flex space-x-2">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route('admin.reports.export', $filters) }}" {{-- Kirim filter aktif --}}
                        class="inline-flex items-center px-3 py-1.5 border border-green-600 text-xs font-medium rounded shadow-sm text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-1.5"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.reports.exportPdf', $filters) }}" {{-- Kirim filter aktif --}} target="_blank"
                        {{-- Buka di tab baru (opsional) --}}
                        class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-medium rounded shadow-sm text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i data-lucide="file-text" class="w-4 h-4 mr-1.5"></i> Export PDF
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-800">
                        Hasil Laporan Presensi
                        @if (isset($filters['tanggal_mulai']) && isset($filters['tanggal_selesai']))
                            <span class="text-sm font-normal text-gray-500">
                                (Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->isoFormat('D MMM Y') }} -
                                {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->isoFormat('D MMM Y') }})
                            </span>
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Ditemukan {{ $results->count() }} data presensi.</p>
                    {{-- Tombol Export bisa ditambahkan di sini nanti --}}
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($results as $index => $att)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        {{ $att->tanggal->isoFormat('D MMM YYYY') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        {{ $att->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ $att->user->role ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ $att->user->role === 'Siswa' ? $att->user->kelas->nama_kelas ?? '-' : '-' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                                        @php
                                            /* ... (Logika Badge Status sama seperti history) ... */
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadge }}">
                                            {{ $att->status }} </span>
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
                                        @if (is_null($att->is_location_valid))
                                            <span class="status-badge badge-gray">N/A</span>
                                        @elseif($att->is_location_valid)
                                            <span class="status-badge badge-green">Valid</span>
                                        @else
                                            <span class="status-badge badge-red">Tidak Valid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-10 text-gray-500">
                                        Tidak ada data presensi ditemukan untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Di dalam @isset($results) --}}
        @else
            {{-- Tampilan jika belum ada filter yang dijalankan --}}
            @if (!empty($filters))
                {{-- Cek apakah ada filter tapi belum ada tanggal --}}
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    Silakan pilih rentang tanggal (Tanggal Mulai dan Tanggal Selesai) untuk menampilkan laporan.
                </div>
            @else
                <div class="text-center py-10 text-gray-500">
                    Silakan pilih filter di atas untuk menampilkan laporan presensi.
                </div>
            @endif
        @endisset

    </div>

    {{-- Style untuk form & badge jika belum global --}}
    <style>
        .form-label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.3);
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-yellow {
            background-color: #fef9c3;
            color: #f59e0b;
        }

        .badge-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* ... (badge lain) ... */
    </style>
@endsection
