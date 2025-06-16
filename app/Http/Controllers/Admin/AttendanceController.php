<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Kelas; // Jika digunakan dalam filter atau view
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Jika ada upload/path file
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Jika diperlukan untuk validasi
use Illuminate\Support\Facades\Gate; // <-- PASTIKAN BARIS INI ADA!

class AttendanceController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    // Route middleware 'role:Super Admin,Petugas Piket' di grup route /admin
    // akan membatasi akses dasar ke controller ini.
    // Otorisasi yang lebih spesifik (siapa yang bisa INDEX, EDIT, UPDATE)
    // akan dilakukan di dalam metode ini menggunakan Gates.

    /**
     * Display a listing of the resource.
     * Bisa diakses oleh Super Admin, role statis Petugas Piket, ATAU user terjadwal piket hari ini.
     * Ini ditegakkan menggunakan Gate 'viewAdminAttendanceList'.
     */
    public function index(Request $request)
    {
        // Otorisasi menggunakan Gate 'viewAdminAttendanceList'
        // Gate ini memeriksa Super Admin, role Petugas Piket statis, atau terjadwal piket hari ini.
        $this->authorize('viewAdminAttendanceList'); // <-- OTORISASI UNTUK MELIHAT DAFTAR

        $query = Attendance::query()->with(['user' => fn($q) => $q->with('kelas')]) // Eager load user dan kelasnya
                     ->latest('tanggal') // Urutkan terbaru dulu
                     ->latest('jam_masuk');

        // --- Implementasi filter (sesuai kode Anda) ---
        if ($request->filled('search_name')) {
             $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search_name . '%'));
        }
        if ($request->filled('filter_date')) {
             $query->whereDate('tanggal', $request->filter_date);
        }
        if ($request->filled('filter_status')) {
             $query->where('status', $request->filter_status);
        }
        // --- Akhir Filter ---

        $attendances = $query->paginate(20)->withQueryString(); // Paginasi dan pertahankan query string filter
        $filters = $request->only(['search_name', 'filter_date', 'filter_status']); // Kirim filter aktif ke view

        return view('admin.attendances.index', compact('attendances', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     * Tetap hanya Super Admin.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Otorisasi: Hanya Super Admin yang bisa menambah manual
        if (!$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk menambah presensi manual.');
        }
        // Ambil user yang relevan jika diperlukan untuk dropdown di form create
        $users = User::whereIn('role', ['Guru', 'Siswa'])->orderBy('name')->get();
        return view('admin.attendances.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     * Tetap hanya Super Admin.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Otorisasi: Hanya Super Admin yang bisa menyimpan manual
        if (!$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk menyimpan presensi manual.');
        }
        // --- Validasi dan penyimpanan (sesuai kode Anda) ---
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Telat,Izin,Sakit,Absen',
            'jam_masuk' => 'nullable|required_if:status,Hadir,Telat|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ],[ /* ... custom messages ... */ ]);

         $existing = Attendance::where('user_id', $validated['user_id'])
                               ->where('tanggal', $validated['tanggal'])
                               ->first();
         if ($existing) {
             return back()->with('error', 'Data presensi untuk user ini pada tanggal tersebut sudah ada. Gunakan fitur edit jika ingin mengubah.')->withInput();
         }

         if (!in_array($validated['status'], ['Hadir', 'Telat'])) {
             $validated['jam_masuk'] = null;
         } else {
              if (empty($validated['jam_masuk']) && $request->filled('jam_masuk')) {
                 return back()->withErrors(['jam_masuk' => 'Jam masuk wajib diisi untuk status Hadir/Telat.'])->withInput();
              }
         }

        $validated['latitude'] = null;
        $validated['longitude'] = null;
        $validated['selfie_path'] = null;
        $validated['is_location_valid'] = null;

        Attendance::create($validated);

        return redirect()->route('admin.attendances.index')->with('success', 'Data presensi manual berhasil ditambahkan.');
        // --- Akhir Validasi dan penyimpanan ---
    }

    /**
     * Display the specified resource. (Tidak Digunakan sesuai route Anda)
     */
    public function show(Attendance $attendance)
    {
         return redirect()->route('admin.attendances.index');
    }

    /**
     * Show the form for editing the specified resource.
     * Hanya Super Admin.
     */// app/Http/Controllers/Admin/AttendanceController.php
// ... (use statements) ...

    public function edit(Attendance $attendance)
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        // Otorisasi: Super Admin ATAU Petugas Piket
        if (!$loggedInUser->isSuperAdmin() && !$loggedInUser->isPetugasPiket()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data presensi ini.');
        }

        // Jika Petugas Piket, mungkin ada batasan tambahan?
        // Misalnya, hanya boleh edit data hari ini atau beberapa hari ke belakang?
        // if ($loggedInUser->isPetugasPiket() && $attendance->tanggal < now()->subDays(1)->toDateString()) {
        //     abort(403, 'Petugas piket hanya dapat mengubah data presensi hari ini.');
        // }

        $users = User::whereIn('role', ['Guru', 'Siswa'])->orderBy('name')->get();
        $attendance->load('user');
        return view('admin.attendances.edit', compact('attendance', 'users'));
    }

    /**
     * Update the specified resource in storage.
     * Bisa diakses oleh Super Admin ATAU user terjadwal piket hari ini.
     * Ini ditegakkan menggunakan Gate 'manageTodayAttendanceAdmin'.
     */
    public function update(Request $request, Attendance $attendance)
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();
    
        if (!$loggedInUser->isSuperAdmin() && !$loggedInUser->isPetugasPiket()) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui data presensi ini.');
        }
    
        // Tambahkan validasi untuk 'remarks' jika Anda menggunakan field ini
        // Kolom 'keterangan' di validasi Anda sebelumnya bisa diganti/disesuaikan menjadi 'remarks'
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'status' => ['required', Rule::in(['Hadir', 'Telat', 'Izin', 'Sakit', 'Absen'])],
            'jam_masuk' => 'nullable|required_if:status,Hadir,Telat|date_format:H:i',
            // 'keterangan' => 'nullable|string|max:255', // Ganti atau tambahkan 'remarks'
            'remarks' => 'nullable|string|max:1000', // Contoh jika menggunakan 'remarks'
        ], [
            'jam_masuk.required_if' => 'Jam masuk wajib diisi jika status Hadir atau Telat.',
        ]);
    
        // Cek duplikasi (kecuali untuk record yang sedang diedit) - logika Anda sudah baik
        $existing = Attendance::where('user_id', $attendance->user_id)
                            ->where('tanggal', $validated['tanggal'])
                            ->where('id', '!=', $attendance->id)
                            ->first();
        if ($existing) {
            return back()->with('error', 'Sudah ada data presensi lain untuk user & tanggal tersebut.')->withInput();
        }
    
        // Jika status bukan Hadir/Telat, pastikan jam masuk null
        $dataToUpdate = [
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'jam_masuk' => (in_array($validated['status'], ['Hadir', 'Telat']) && !empty($validated['jam_masuk'])) ? $validated['jam_masuk'] : null,
            // 'keterangan' => $validated['keterangan'] ?? null, // Ganti dengan remarks
            'remarks' => $validated['remarks'] ?? null,
            'updated_by_user_id' => $loggedInUser->id, // Simpan ID user yang melakukan update
        ];
    
        // Jika Hadir/Telat, tapi jam_masuk kosong (setelah validasi required_if)
        if (in_array($validated['status'], ['Hadir', 'Telat']) && empty($dataToUpdate['jam_masuk'])) {
            // Controller Anda sudah punya validasi required_if, tapi ini pengaman tambahan
            return back()->withErrors(['jam_masuk' => 'Jam masuk wajib diisi untuk status Hadir/Telat.'])->withInput();
        }
    
        $attendance->update($dataToUpdate);
    
        return redirect()->route('admin.attendances.index')->with('success', 'Data presensi berhasil diperbarui.');
        // --- Akhir Validasi dan Update ---
    }

    /**
     * Remove the specified resource from storage.
     * Tetap hanya Super Admin.
     */
    public function destroy(Attendance $attendance)
    {
         /** @var \App\Models\User $user */
         $user = Auth::user();

         // Otorisasi: Hanya Super Admin
         if (!$user->isSuperAdmin()) {
             abort(403, 'Anda tidak memiliki izin untuk menghapus presensi.');
         }

         $attendance->delete();

         return back()->with('success', 'Data presensi berhasil dihapus.');
    }
}