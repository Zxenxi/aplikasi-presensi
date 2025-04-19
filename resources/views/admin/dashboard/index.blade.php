@extends('layouts.admin') {{-- Gunakan layout admin --}}

@section('content')
    {{-- Salin HANYA bagian konten dashboard dari index versi 2.html --}}
    {{-- Contoh: <div x-show="activeTab === 'dashboard'" x-cloak> ... </div> --}}
    {{-- Hapus x-show jika menggunakan multi-page navigation --}}


    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Selamat Datang --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    Selamat Datang, {{ auth()->user()->name }}! {{-- Tampilkan nama user --}}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan data presensi hari ini (<span id="last-updated-time">Memuat...</span>)
                </p>
            </div>
            {{-- Filter (bisa diaktifkan nanti) --}}
        </div>

        {{-- Ringkasan Statistik (Ganti angka dengan variabel dari controller nanti) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Siswa</h3>
                <p class="text-3xl font-bold text-gray-900">--</p> {{-- Ganti Nanti: {{ $totalSiswa ?? '--' }} --}}
            </div>
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Kehadiran Siswa (Hari Ini)</h3>
                <p class="text-3xl font-bold text-green-600">--%</p> {{-- Ganti Nanti --}}
            </div>
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Guru</h3>
                <p class="text-3xl font-bold text-gray-900">--</p> {{-- Ganti Nanti: {{ $totalGuru ?? '--' }} --}}
            </div>
            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Kehadiran Guru (Hari Ini)</h3>
                <p class="text-3xl font-bold text-cyan-600">--%</p> {{-- Ganti Nanti --}}
            </div>
        </div>

        {{-- Chart & Daftar Absen (Implementasikan nanti saat ada data) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Tren Kehadiran Bulanan</h3>
                    <div class="h-72 relative"><canvas id="totalAttendanceChart"></canvas></div>
                </div>
            </div>
            <div class="lg:col-span-1 space-y-6">
                {{-- Daftar Guru/Siswa Tidak Hadir --}}
            </div>
        </div>

    </div>
@endsection

{{-- Script khusus untuk halaman ini (misal init chart) --}}
@push('scripts')
    <script>
        // Panggil fungsi init chart Anda di sini jika perlu
        // document.addEventListener('DOMContentLoaded', () => {
        //    // Contoh: initCharts('semua'); // Fungsi dari app.js Anda
        // });
    </script>
@endpush

{{-- * Ganti data statis (angka, nama) dengan variabel Blade yang akan diisi dari controller nanti (misal:
`{{ $totalSiswa ?? '--' }}`). Tanda `?? '--'` memberikan nilai default jika variabel belum ada. --}}
