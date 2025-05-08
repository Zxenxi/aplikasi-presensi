@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul & Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Jadwal Petugas Piket</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola jadwal petugas piket harian.</p>
            </div>
            @if (Auth::user()->isSuperAdmin())
                {{-- Pastikan cek otorisasi di view juga --}}
                <a href="{{ route('admin.picket-schedules.create') }}"
                    class="w-40 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Jadwal
                </a>
            @endif
        </div>

        {{-- @include('partials.common._alert') Tampilkan alert jika ada --}}

        {{-- Form Filter Bulan & Tahun --}}
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
            <form method="GET" action="{{ route('admin.picket-schedules.index') }}" class="flex flex-wrap items-end gap-4">
                {{-- Dropdown Bulan --}}
                <div>
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select">
                        @foreach ($months as $monthNumber => $monthName)
                            <option value="{{ $monthNumber }}" {{ $selectedMonth == $monthNumber ? 'selected' : '' }}>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Dropdown Tahun --}}
                <div>
                    <label for="year" class="form-label">Tahun</label>
                    <select name="year" id="year" class="form-select">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Tombol Tampilkan --}}
                <div>
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Tampilkan</button>
                </div>
                {{-- Tombol Bulan Ini (opsional) --}}
                <div>
                    <a href="{{ route('admin.picket-schedules.index') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-indigo-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Bulan
                        Ini</a>
                </div>
            </form>
        </div>

        {{-- Tabel Jadwal (Kode tabel sama seperti sebelumnya) --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    {{-- thead --}}
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                                Petugas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Catatan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    {{-- tbody --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $schedule->duty_date->isoFormat('dddd, D MMMM Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $schedule->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $schedule->user->role ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"
                                    title="{{ $schedule->notes }}">{{ $schedule->notes ?? '-' }}</td>
                                {{-- Tambah truncate & title --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if (Auth::user()->isSuperAdmin())
                                        {{-- Cek otorisasi di view --}}
                                        <div class="flex justify-center items-center space-x-1">
                                            <a href="{{ route('admin.picket-schedules.edit', $schedule) }}"
                                                title="Edit Jadwal" class="action-button"><i data-lucide="edit-2"></i></a>
                                            <form action="{{ route('admin.picket-schedules.destroy', $schedule) }}"
                                                method="POST" onsubmit="return confirm('Yakin hapus jadwal piket ini?');"
                                                class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Hapus Jadwal"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50"><i
                                                        data-lucide="trash-2"></i></button>
                                            </form>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">Belum ada jadwal piket untuk
                                    bulan <span
                                        class="font-medium">{{ \Carbon\Carbon::create()->month($selectedMonth)->isoFormat('MMMM') }}
                                        {{ $selectedYear }}</span>.</td>
                            </tr> {{-- Sesuaikan colspan --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Style --}}
    <style>
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        /* ... (style lain: button, alert, action-button, dll) ... */
    </style>
@endsection
