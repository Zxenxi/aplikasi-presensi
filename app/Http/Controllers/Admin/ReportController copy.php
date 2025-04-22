<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceReportExport;
use Barryvdh\DomPDF\Facade\Pdf; // Import facade PDF

class ReportController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan dan hasil laporan jika ada filter.
     */
    public function index(Request $request) // Terima Request untuk ambil filter
    {
        // Otorisasi: Super Admin & Petugas Piket bisa akses
          /** @var \App\Models\User $user */ // <-- PHPDoc Hint
          $user = Auth::user();

          // Otorisasi: Super Admin & Petugas Piket bisa akses
          if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) {
              abort(403, 'Akses Ditolak');
          }

        // Ambil data untuk filter dropdown
        $kelas = Kelas::orderBy('nama_kelas')->get();

        // Query dasar untuk presensi, termasuk data user dan kelas user
        $query = Attendance::query()->with(['user' => function ($query) {
            $query->with('kelas'); // Eager load kelas milik user
        }]);

        // Proses Filter jika ada input dari request GET
        $filters = $request->only(['tanggal_mulai', 'tanggal_selesai', 'tipe_user', 'kelas_id', 'status_presensi']);
        $results = null; // Inisialisasi hasil

        // Hanya proses query jika ada filter tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            // Validasi tanggal dasar
            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'tipe_user' => 'nullable|in:Guru,Siswa',
                'kelas_id' => 'nullable|exists:kelas,id',
                'status_presensi' => 'nullable|in:Hadir,Telat,Izin,Sakit,Absen', // Tambah filter status
            ]);

            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);

            // Filter berdasarkan Tipe User (Role)
            if ($request->filled('tipe_user')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('role', $request->tipe_user);
                });
            }

            // Filter berdasarkan Kelas (hanya jika tipe user adalah Siswa)
            if ($request->tipe_user === 'Siswa' && $request->filled('kelas_id')) {
                 $query->whereHas('user', function ($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }

             // Filter berdasarkan Status Presensi
            if ($request->filled('status_presensi')) {
                $query->where('status', $request->status_presensi);
            }


            // Ambil hasil query, urutkan
            $results = $query->orderBy('tanggal', 'asc')
                            ->orderBy('jam_masuk', 'asc')
                            ->get(); // Gunakan get() untuk laporan, atau paginate() jika sangat banyak
        }


        // Kirim data kelas, hasil query (jika ada), dan input filter ke view
        return view('admin.reports.index', compact('kelas', 'results', 'filters'));
    }

    // Anda bisa menambahkan method lain di sini, misal untuk export ke Excel/PDF
    // public function export(Request $request) { ... }
    // Tambahkan use statement di atas class ReportController

// Method export di dalam class ReportController
public function export(Request $request)
{
      /** @var \App\Models\User $user */ // <-- PHPDoc Hint
      $user = Auth::user();

      // Otorisasi
      if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) {
          abort(403, 'Akses Ditolak');
      }
     // Validasi filter (minimal tanggal harus ada)
    $validatedFilters = $request->validate([ // Simpan hasil validasi
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'tipe_user' => 'nullable|in:Guru,Siswa',
        'kelas_id' => 'nullable|exists:kelas,id',
        'status_presensi' => 'nullable|in:Hadir,Telat,Izin,Sakit,Absen',
    ]);

    // Buat nama file dinamis
    $fileName = 'laporan_presensi_' . $validatedFilters['tanggal_mulai'] . '_sd_' . $validatedFilters['tanggal_selesai'] . '.xlsx';

    // Panggil export dengan filter yang sudah divalidasi dan download file
    return Excel::download(new AttendanceReportExport($validatedFilters), $fileName);
}
// Tambahkan use statement di atas class ReportController

// Tambahkan method ini di dalam class ReportController
public function exportPdf(Request $request)
{
       /** @var \App\Models\User $user */ // <-- PHPDoc Hint
       $user = Auth::user();

       // Otorisasi
       if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) {
           abort(403, 'Akses Ditolak');
       }
    // Validasi filter (minimal tanggal harus ada)
    $validatedFilters = $request->validate([
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'tipe_user' => 'nullable|in:Guru,Siswa',
        'kelas_id' => 'nullable|exists:kelas,id',
        'status_presensi' => 'nullable|in:Hadir,Telat,Izin,Sakit,Absen',
    ]);

    // --- Bangun Query (Sama seperti di index() dan export Excel) ---
    // Refactor ke private method jika ingin lebih bersih
    $query = Attendance::query()->with(['user' => function ($query) {
            $query->with('kelas');
        }]);

    $query->whereBetween('tanggal', [$validatedFilters['tanggal_mulai'], $validatedFilters['tanggal_selesai']]);

    if (!empty($validatedFilters['tipe_user'])) {
        $query->whereHas('user', fn($q) => $q->where('role', $validatedFilters['tipe_user']));
    }
    if (!empty($validatedFilters['tipe_user']) && $validatedFilters['tipe_user'] === 'Siswa' && !empty($validatedFilters['kelas_id'])) {
        $query->whereHas('user', fn($q) => $q->where('kelas_id', $validatedFilters['kelas_id']));
    }
    if (!empty($validatedFilters['status_presensi'])) {
        $query->where('status', $validatedFilters['status_presensi']);
    }

    $results = $query->orderBy('tanggal', 'asc')->orderBy('user_id', 'asc')->orderBy('jam_masuk', 'asc')->get();
    // --- Akhir Query Builder ---

    // Ambil data kelas untuk info di header PDF
    $kelas = Kelas::all(); // Atau query yg lebih efisien jika perlu

    // Buat nama file dinamis
    $fileName = 'laporan_presensi_' . $validatedFilters['tanggal_mulai'] . '_sd_' . $validatedFilters['tanggal_selesai'] . '.pdf';

    // Load view PDF dengan data
    $pdf = Pdf::loadView('admin.reports.pdf', [
        'results' => $results,
        'filters' => $validatedFilters, // Kirim filter ke view PDF
        'kelas' => $kelas // Kirim data kelas ke view PDF
    ]);

    // Set orientasi kertas menjadi landscape (opsional)
    $pdf->setPaper('a4', 'landscape');

    // Download file PDF
    return $pdf->download($fileName);
}
}