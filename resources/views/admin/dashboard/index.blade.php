@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Selamat Datang --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan data presensi hari ini ({{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }})
                    {{-- Ganti 'Memuat...' dengan waktu update jika perlu real-time --}}
                    <span id="last-updated-time"></span>
                </p>
            </div>
            {{-- Filter (bisa diaktifkan nanti) --}}
        </div>

        {{-- Ringkasan Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Siswa --}}
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Siswa Aktif</h3>
                    <span class="flex-shrink-0 p-1.5 rounded-full bg-indigo-100"><i data-lucide="users"
                            class="h-5 w-5 text-indigo-600"></i></span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalSiswa) }}</p>
            </div>
            {{-- Kehadiran Siswa --}}
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Kehadiran Siswa (Hari Ini)</h3>
                    <span class="flex-shrink-0 p-1.5 rounded-full bg-green-100"><i data-lucide="user-check"
                            class="h-5 w-5 text-green-600"></i></span>
                </div>
                <p class="text-3xl font-bold text-green-600">{{ $persentaseHadirSiswa }}%</p>
                <div class="text-xs mt-1 space-x-2 flex flex-wrap gap-y-1">
                    <span class="text-gray-600">H:{{ $hadirSiswaCount }}</span>
                    <span class="text-orange-600">T:{{ $telatSiswaCount }}</span>
                    <span class="text-blue-600">I:{{ $izinSiswaCount }}</span>
                    <span class="text-purple-600">S:{{ $sakitSiswaCount }}</span>
                    <span class="text-red-600">A:{{ $absenSiswaCount }}</span>
                </div>
            </div>
            {{-- Total Guru --}}
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Guru Aktif</h3>
                    <span class="flex-shrink-0 p-1.5 rounded-full bg-blue-100"><i data-lucide="briefcase"
                            class="h-5 w-5 text-blue-600"></i></span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalGuru) }}</p>
            </div>
            {{-- Kehadiran Guru --}}
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Kehadiran Guru (Hari Ini)</h3>
                    <span class="flex-shrink-0 p-1.5 rounded-full bg-cyan-100"><i data-lucide="clipboard-check"
                            class="h-5 w-5 text-cyan-600"></i></span>
                </div>
                <p class="text-3xl font-bold text-cyan-600">{{ $persentaseHadirGuru }}%</p>
                <div class="text-xs mt-1 space-x-2 flex flex-wrap gap-y-1">
                    <span class="text-gray-600">H:{{ $hadirGuruCount }}</span>
                    <span class="text-orange-600">T:{{ $telatGuruCount }}</span>
                    <span class="text-blue-600">I:{{ $izinGuruCount }}</span>
                    <span class="text-purple-600">S:{{ $sakitGuruCount }}</span>
                    <span class="text-red-600">A:{{ $absenGuruCount }}</span>
                </div>
            </div>
        </div>

        {{-- Chart & Daftar Tidak Hadir --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"> {{-- Ubah jadi 3 kolom --}}
            {{-- Kolom Chart Tren --}}
            <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
                {{-- Lebarkan jadi 2 kolom --}}
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Tren Kehadiran 7 Hari Terakhir (Semua User)
                    </h3>
                    {{-- Tombol Opsi jika perlu --}}
                </div>
                <div class="h-72 relative">
                    <canvas id="chartTrendKehadiran"></canvas> {{-- Canvas untuk chart tren --}}
                </div>
            </div>

            {{-- Kolom Kanan (Ringkasan Guru & Daftar Tidak Hadir) --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Chart Ringkasan Guru --}}
                <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Ringkasan Guru Hari Ini</h3>
                    </div>
                    <div class="h-60 flex justify-center items-center relative">
                        <canvas id="chartRingkasanGuru"></canvas> {{-- Canvas untuk chart guru --}}
                    </div>
                </div>

                {{-- Daftar Guru Tidak Hadir --}}
                <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-semibold text-gray-800">Guru Tidak Tercatat Hadir</h3>
                        {{-- Link lihat semua jika perlu --}}
                    </div>
                    <ul class="space-y-3 max-h-40 overflow-y-auto pr-2">
                        @forelse($guruTidakHadir as $guru)
                            <li class="flex items-center justify-between space-x-3 p-2 rounded-md hover:bg-gray-50">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <img class="h-9 w-9 rounded-full object-cover flex-shrink-0 ring-1 ring-gray-200"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($guru->name) }}&background=f87171&color=ffffff"
                                        alt="{{ $guru->name }}">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $guru->name }}</p>
                                        {{-- Tambahkan info mapel jika ada --}}
                                        {{-- <p class="text-xs text-gray-500 truncate">Matematika</p> --}}
                                    </div>
                                </div>
                                <span class="status-badge badge-red flex-shrink-0">Absen</span>
                            </li>
                        @empty
                            <li class="text-center text-sm text-gray-500 py-4">Semua guru tercatat hadir.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Daftar Siswa Tidak Hadir --}}
                <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-semibold text-gray-800">Siswa Tidak Tercatat Hadir</h3>
                        {{-- Link lihat semua jika perlu --}}
                    </div>
                    <ul class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        @forelse($siswaTidakHadir as $siswa)
                            <li class="flex items-center justify-between space-x-3 p-2 rounded-md hover:bg-gray-50">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <img class="h-9 w-9 rounded-full object-cover flex-shrink-0 ring-1 ring-gray-200"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($siswa->name) }}&background=fca5a5&color=ffffff"
                                        alt="{{ $siswa->name }}">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $siswa->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}</p>
                                    </div>
                                </div>
                                <span class="status-badge badge-red flex-shrink-0">Absen</span>
                            </li>
                        @empty
                            <li class="text-center text-sm text-gray-500 py-4">Semua siswa tercatat hadir.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Style badge (jika belum global) --}}
    <style>
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

        /* ... (warna badge lain) ... */
    </style>
@endsection

@push('scripts')
    <script>
        // Pastikan Chart.js sudah di-load (misal di app.js atau layout)
        document.addEventListener('DOMContentLoaded', () => {
            // Data dari Controller (diconvert ke JSON)
            const chartTrendData = @json($chartTrendKehadiran ?? ['labels' => [], 'datasets' => []]);
            const chartRingkasanGuruData = @json($chartRingkasanGuru ?? ['labels' => [], 'datasets' => []]);

            // Render Chart Tren Kehadiran (Line Chart)
            const ctxTrend = document.getElementById('chartTrendKehadiran');
            if (ctxTrend && typeof Chart !== 'undefined') {
                new Chart(ctxTrend, {
                    type: 'line',
                    data: chartTrendData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            } // Hanya angka bulat di sumbu Y
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            } else if (!ctxTrend) {
                console.error("Canvas element with ID 'chartTrendKehadiran' not found.");
            } else {
                console.error("Chart.js library is not loaded.");
            }


            // Render Chart Ringkasan Guru (Doughnut Chart)
            const ctxRingkasan = document.getElementById('chartRingkasanGuru');
            if (ctxRingkasan && typeof Chart !== 'undefined') {
                new Chart(ctxRingkasan, {
                    type: 'doughnut',
                    data: chartRingkasanGuruData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            } else if (!ctxRingkasan) {
                console.error("Canvas element with ID 'chartRingkasanGuru' not found.");
            } else {
                console.error("Chart.js library is not loaded.");
            }
        });
    </script>
@endpush
