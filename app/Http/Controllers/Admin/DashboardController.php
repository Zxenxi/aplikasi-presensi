<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PicketSchedule;
use App\Models\User; // Import User
use Carbon\Carbon; // Import Carbon
use App\Http\Controllers\Controller;
use App\Models\Kelas; // Import Kelas
use App\Models\Attendance; // Import Attendance
use Illuminate\Support\Facades\DB; // Import DB Facade for complex queries if needed

class DashboardController extends Controller
{
    public function index()
    {
        // --- Data Statistik Umum ---
        $totalSiswa = User::where('role', 'Siswa')->count();
        $totalGuru = User::where('role', 'Guru')->count();

        // --- Data Kehadiran Hari Ini ---
        $today = Carbon::today()->toDateString();

        // Kehadiran Siswa Hari Ini
        $attendanceSiswaToday = Attendance::where('tanggal', $today)
            ->whereHas('user', fn($q) => $q->where('role', 'Siswa'))
            ->get();

        $hadirSiswaCount = $attendanceSiswaToday->where('status', 'Hadir')->count();
        $telatSiswaCount = $attendanceSiswaToday->where('status', 'Telat')->count();
        $izinSiswaCount = $attendanceSiswaToday->where('status', 'Izin')->count(); // Jika ada status Izin/Sakit
        $sakitSiswaCount = $attendanceSiswaToday->where('status', 'Sakit')->count();
        $totalPresensiSiswa = $attendanceSiswaToday->count();
        $absenSiswaCount = max(0, $totalSiswa - $totalPresensiSiswa); // Perkiraan absen
        // Persentase kehadiran (Hadir + Telat) dari total siswa
        $persentaseHadirSiswa = $totalSiswa > 0 ? round((($hadirSiswaCount + $telatSiswaCount) / $totalSiswa) * 100, 1) : 0;

        // Kehadiran Guru Hari Ini
        $attendanceGuruToday = Attendance::where('tanggal', $today)
            ->whereHas('user', fn($q) => $q->where('role', 'Guru'))
            ->get();

        $hadirGuruCount = $attendanceGuruToday->where('status', 'Hadir')->count();
        $telatGuruCount = $attendanceGuruToday->where('status', 'Telat')->count();
        $izinGuruCount = $attendanceGuruToday->where('status', 'Izin')->count();
        $sakitGuruCount = $attendanceGuruToday->where('status', 'Sakit')->count();
        $totalPresensiGuru = $attendanceGuruToday->count();
        $absenGuruCount = max(0, $totalGuru - $totalPresensiGuru); // Perkiraan absen
        // Persentase kehadiran (Hadir + Telat) dari total guru
        $persentaseHadirGuru = $totalGuru > 0 ? round((($hadirGuruCount + $telatGuruCount) / $totalGuru) * 100, 1) : 0;


        // --- Daftar Tidak Hadir Hari Ini (Contoh: 5 terbaru) ---
        // Cari ID user Siswa & Guru yg sudah presensi hari ini
        $attendedSiswaIds = $attendanceSiswaToday->pluck('user_id');
        $attendedGuruIds = $attendanceGuruToday->pluck('user_id');

        // Ambil Siswa yg TIDAK ada di daftar hadir hari ini
        $siswaTidakHadir = User::where('role', 'Siswa')
                               ->whereNotIn('id', $attendedSiswaIds)
                               ->with('kelas') // Ambil data kelasnya
                               ->latest() // Urutkan berdasarkan terbaru dibuat (atau kriteria lain)
                               ->take(5) // Ambil 5 saja
                               ->get();

        // Ambil Guru yg TIDAK ada di daftar hadir hari ini
        $guruTidakHadir = User::where('role', 'Guru')
                             ->whereNotIn('id', $attendedGuruIds)
                             ->latest()
                             ->take(5)
                             ->get();

        // --- Data untuk Chart Tren Kehadiran (Misal 7 hari terakhir) ---
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $trendData = Attendance::selectRaw('tanggal, status, COUNT(*) as count')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('tanggal', 'status')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Format data untuk Chart.js (Line Chart)
        $trendLabels = [];
        $trendHadirData = [];
        $trendTelatData = [];
        $trendLainData = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate); // Parse end date sekali saja
        
        while ($currentDate <= $endDateCarbon) { // Loop dari tanggal mulai hingga tanggal akhir
            $trendLabels[] = $currentDate->isoFormat('D MMM'); // Format label tanggal (misal: 3 Mei)
        
            // --- INI BAGIAN PENTING YANG DIPERBAIKI ---
            // Filter collection $trendData dimana tanggal item (objek Carbon) sama dengan $currentDate (objek Carbon)
            $dailyData = $trendData->filter(function ($item) use ($currentDate) {
                // $item->tanggal adalah objek Carbon karena ada casting di Model
                return $item->tanggal->isSameDay($currentDate); // Gunakan isSameDay untuk perbandingan tanggal
            });
            // --- AKHIR BAGIAN PENTING ---
        
            // Cari status ('Hadir', 'Telat', 'Izin', 'Sakit') dalam data harian ($dailyData) yang sudah difilter
            $hadir = $dailyData->firstWhere('status', 'Hadir');
            $telat = $dailyData->firstWhere('status', 'Telat');
            $izin = $dailyData->firstWhere('status', 'Izin');
            $sakit = $dailyData->firstWhere('status', 'Sakit');
        
            // Ambil nilai 'count' dari hasil agregasi database, atau 0 jika status tidak ditemukan untuk hari itu
            $trendHadirData[] = $hadir ? $hadir->count : 0;
            $trendTelatData[] = $telat ? $telat->count : 0;
            $trendLainData[] = ($izin ? $izin->count : 0) + ($sakit ? $sakit->count : 0); // Gabungkan Izin & Sakit
        
            $currentDate->addDay(); // Lanjut ke hari berikutnya
        }
          // --- === DATA BARU: Petugas Piket Hari Ini === ---
          $petugasPiketHariIni = PicketSchedule::with('user') // Ambil data user terkait
          ->where('duty_date', $today)
          ->get();
        // --- AKHIR BLOK PENGGANTI ---
        
        // Buat struktur data chart (ini seharusnya sudah benar)
        $chartTrendKehadiran = [
            'labels' => $trendLabels,
            'datasets' => [
                ['label' => 'Hadir', 'data' => $trendHadirData, 'borderColor' => '#16a34a', 'backgroundColor' => 'rgba(22, 163, 74, 0.1)', 'tension' => 0.1],
                ['label' => 'Telat', 'data' => $trendTelatData, 'borderColor' => '#f59e0b', 'backgroundColor' => 'rgba(245, 158, 11, 0.1)', 'tension' => 0.1],
                ['label' => 'Izin/Sakit', 'data' => $trendLainData, 'borderColor' => '#6366f1', 'backgroundColor' => 'rgba(99, 102, 241, 0.1)', 'tension' => 0.1],
            ]
        ];
        
        // --- Data untuk Chart Ringkasan Guru Hari Ini (Pie/Doughnut) ---
         $chartRingkasanGuru = [
            'labels' => ['Hadir', 'Telat', 'Izin', 'Sakit', 'Absen'],
            'datasets' => [[
                'data' => [
                    $hadirGuruCount,
                    $telatGuruCount,
                    $izinGuruCount,
                    $sakitGuruCount,
                    $absenGuruCount
                ],
                'backgroundColor' => ['#10b981', '#f59e0b', '#3b82f6', '#a855f7', '#ef4444'], // Warna sesuai status
                'hoverOffset' => 4
            ]]
         ];


        // --- Kirim Semua Data ke View ---
        return view('admin.dashboard.index', compact(
            'totalSiswa',
            'totalGuru',
            'persentaseHadirSiswa',
            'hadirSiswaCount',
            'telatSiswaCount',
            'izinSiswaCount',
            'sakitSiswaCount',
            'absenSiswaCount',
            'persentaseHadirGuru',
            'hadirGuruCount',
            'telatGuruCount',
            'izinGuruCount',
            'sakitGuruCount',
            'absenGuruCount',
            'siswaTidakHadir',
            'guruTidakHadir',
            'chartTrendKehadiran', // Data untuk chart tren
            'chartRingkasanGuru', // Data untuk chart ringkasan guru
            'petugasPiketHariIni'
        ));
    }
}