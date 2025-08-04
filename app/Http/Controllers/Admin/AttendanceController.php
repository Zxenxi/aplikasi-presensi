<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule; // Opsional
use Carbon\Carbon; // <-- Tambahkan jika belum
use App\Models\User; // <-- Tambahkan jika belum
use App\Models\Kelas; // <-- Tambahkan jika belum
use Illuminate\Support\Facades\Auth; // <-- Tambahkan jika belum
use Illuminate\Support\Facades\Storage; // <-- Tambahkan jika belum

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * Dapat diakses oleh Super Admin & Petugas Piket.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // Otorisasi: hanya Super Admin dan Guru yang piket hari ini
        if (!($user && $user instanceof \App\Models\User && $user->isSuperAdmin())) {
            if (!($user && $user instanceof \App\Models\User && $user->isPetugasPiket())) {
                abort(403, 'Akses hanya untuk Super Admin atau Guru yang sedang piket hari ini.');
            }
        }

        $query = Attendance::query()->with(['user']);

        // Filter by search (user name)
        if ($request->filled('search_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search_name . '%');
            });
        }

        // Filter by date
        if ($request->filled('filter_date')) {
            $query->whereDate('tanggal', $request->filter_date);
        }

        // Filter by status
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        $attendances = $query->orderByDesc('tanggal')
                            ->orderByDesc('jam_masuk')
                            ->paginate(20)
                            ->withQueryString();

        $filters = $request->only(['search_name', 'filter_date', 'filter_status']);

        return view('admin.attendances.index', compact('attendances', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     * Hanya Super Admin.
     */
    public function create()
    {
        /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
        $user = Auth::user();

        // Sekarang IDE tahu $user adalah App\Models\User
        // dan tidak akan menampilkan error untuk isSuperAdmin()
        if (!$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin...');
        }

        // ... sisa kode method create ...
        $users = User::whereIn('role', ['Guru', 'Siswa'])->orderBy('name')->get();
        return view('admin.attendances.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     * Hanya Super Admin.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin...');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Telat,Izin,Sakit,Absen',
            'jam_masuk' => 'nullable|required_if:status,Hadir,Telat|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ],[
            'jam_masuk.required_if' => 'Jam masuk wajib diisi jika status Hadir atau Telat.',
            'user_id.required' => 'Pengguna wajib dipilih.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        // Cek duplikasi untuk user & tanggal yg sama
         $existing = Attendance::where('user_id', $validated['user_id'])
                               ->where('tanggal', $validated['tanggal'])
                               ->first();

        if ($existing) {
            return back()->with('error', 'Data presensi untuk user ini pada tanggal tersebut sudah ada. Gunakan fitur edit jika ingin mengubah.')->withInput();
        }

        // Jika status bukan Hadir/Telat, pastikan jam masuk null
        if (!in_array($validated['status'], ['Hadir', 'Telat'])) {
            $validated['jam_masuk'] = null;
        } else {
            // Jika Hadir/Telat, pastikan jam_masuk diisi
             if (empty($validated['jam_masuk'])) {
                 return back()->withErrors(['jam_masuk' => 'Jam masuk wajib diisi untuk status Hadir/Telat.'])->withInput();
             }
        }

        // Set data default yang tidak diinput manual
        $validated['latitude'] = null;
        $validated['longitude'] = null;
        $validated['selfie_path'] = null;
        $validated['is_location_valid'] = null;

        Attendance::create($validated);

        return redirect()->route('admin.attendances.index')->with('success', 'Data presensi manual berhasil ditambahkan.');
    }

    /**
     * Display the specified resource. (Tidak Digunakan)
     */
    public function show(Attendance $attendance)
    {
         return redirect()->route('attendances.index');
    }

    /**
     * Show the form for editing the specified resource.
     * Hanya Super Admin.
     */
    public function edit(Attendance $attendance)
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        // Otorisasi: Super Admin ATAU Petugas Piket
        if (!$loggedInUser->isSuperAdmin() && !$loggedInUser->isPetugasPiket()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data presensi ini.');
        }


        $users = User::whereIn('role', ['Guru', 'Siswa'])->orderBy('name')->get();
        $attendance->load('user');
        return view('admin.attendances.edit', compact('attendance', 'users'));
    }


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
            'remarks' => 'nullable|string|max:1000', // Contoh jika menggunakan 'remarks'
        ], [
            'jam_masuk.required_if' => 'Jam masuk wajib diisi jika status Hadir atau Telat.',
        ]);
    
        // Cek duplikasi (kecuali untuk record yang sedang diedit) - logika Anda sudah baik
        $existing = Attendance::where('user_id', $attendance->user_id)
                            ->where('tanggal', $validated['tanggal'])
                            ->where('id', '!=', $attendance->id)
                            ->first();
        if ($existing) 
        {
            return back()->with('error', 'Sudah ada data presensi lain untuk user & tanggal tersebut.')->withInput();
        }
    
        // Jika status bukan Hadir/Telat, pastikan jam masuk null
        $dataToUpdate = [
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'jam_masuk' => (in_array($validated['status'], ['Hadir', 'Telat']) && !empty($validated['jam_masuk'])) ? $validated['jam_masuk'] : null,
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
    }

    /**
     * Remove the specified resource from storage.
     * Hanya Super Admin.
     */
    public function destroy(Attendance $attendance)
    {
         // Otorisasi: Hanya Super Admin
      /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
    $user = Auth::user();

        if (!$user->isSuperAdmin()) {
         abort(403, 'Anda tidak memiliki izin...');
    }
        $attendance->delete();
        return back()->with('success', 'Data presensi berhasil dihapus.');
    }

    public function userHistory(User $user)
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('admin.attendances.user_history', compact('user', 'attendances'));
    }
}