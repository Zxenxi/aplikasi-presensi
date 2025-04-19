<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan dan hasil laporan jika ada filter.
     */
    public function index(Request $request) // Terima Request untuk ambil filter
    {
        // Otorisasi: Super Admin & Petugas Piket bisa akses
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isPetugasPiket()) {
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
}