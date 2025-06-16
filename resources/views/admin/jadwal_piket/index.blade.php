@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Jadwal Piket Guru</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola jadwal piket mingguan untuk guru.</p>
            </div>
            @if (Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.picket_schedules.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Jadwal
                </a>
            @endif
        </div>

        {{-- Alert Messages --}}
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

        {{-- Loop untuk setiap hari --}}
        @php
            // Helper untuk mendapatkan nama hari dari nomor hari_ke
            // Anda bisa memindahkannya ke AppServiceProvider atau Model jika sering digunakan
            if (!function_exists('getNamaHari')) {
                function getNamaHari($nomorHari)
                {
                    $hari = [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ];
                    return $hari[$nomorHari] ?? 'Tidak Diketahui';
                }
            }
        @endphp

        @forelse($daysOrder as $dayNum)
            @php
                $schedulesForDay = $jadwalPiketGrouped->get($dayNum);
                $dayName = getNamaHari($dayNum);
            @endphp

            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-3 border-b pb-2">{{ $dayName }}</h2>
                @if ($schedulesForDay && $schedulesForDay->count() > 0)
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Guru</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jam Mulai</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jam Selesai</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Keterangan Tugas</th>
                                        @if (Auth::user()->isSuperAdmin())
                                            <th
                                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($schedulesForDay as $schedule)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ $schedule->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $schedule->jam_mulai ? \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $schedule->jam_selesai ? \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $schedule->keterangan_tugas ?: '-' }}
                                            </td>
                                            @if (Auth::user()->isSuperAdmin())
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                                    <div class="flex justify-center items-center space-x-1">
                                                        <a href="{{ route('admin.picket_schedules.edit', $schedule->id) }}"
                                                            title="Edit Jadwal" class="action-button">
                                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                        </a>
                                                        <form
                                                            action="{{ route('admin.picket_schedules.destroy', $schedule->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal piket ini?');"
                                                            class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" title="Hapus Jadwal"
                                                                class="action-button text-red-500 hover:text-red-700 hover:bg-red-100">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white text-center py-6 px-4 rounded-xl shadow-md border border-gray-200">
                        <i data-lucide="calendar-off" class="w-12 h-12 text-gray-300 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-500">Tidak ada jadwal piket untuk hari {{ strtolower($dayName) }}.</p>
                    </div>
                @endif
            </div>
        @empty
            {{-- Ini seharusnya tidak terjadi jika $daysOrder selalu ada --}}
            <div class="text-center py-10 text-gray-500">
                <p>Tidak ada data jadwal piket yang dapat ditampilkan.</p>
            </div>
        @endforelse

        @if ($jadwalPiketGrouped->isEmpty())
            <div class="bg-white text-center py-10 px-4 rounded-xl shadow-md border border-gray-200">
                <i data-lucide="search-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <p class="text-lg text-gray-600 mb-1">Belum Ada Jadwal Piket</p>
                <p class="text-sm text-gray-500 mb-4">Saat ini belum ada data jadwal piket yang tersimpan di sistem.</p>
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.picket_schedules.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Buat Jadwal Piket Pertama
                    </a>
                @endif
            </div>
        @endif

    </div>

    {{-- Style umum bisa dipindahkan ke app.css jika sering digunakan --}}
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

        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding-left: 1rem;
            padding-right: 1rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            border-width: 1px;
            border-color: transparent;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            color: #ffffff;
            background-color: #4f46e5;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-primary:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
            --tw-ring-opacity: 1;
            --tw-ring-color: rgba(79, 70, 229, var(--tw-ring-opacity));
        }

        .action-button {
            color: #9ca3af;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .action-button:hover {
            color: #4f46e5;
            background-color: #eef2ff;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Jika ada script khusus untuk halaman ini, tambahkan di sini
        // Misalnya, untuk mengaktifkan Lucide icons jika belum di layout utama
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
