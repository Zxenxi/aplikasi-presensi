<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User; // <-- Tambahkan jika belum
use App\Models\Kelas; // <-- Tambahkan jika belum
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan jika belum
use Illuminate\Support\Facades\Storage; // <-- Tambahkan jika belum
use Carbon\Carbon; // <-- Tambahkan jika belum
use Illuminate\Validation\Rule; // Opsional

class AttendanceController extends Controller
{
    // Middleware bisa diterapkan di route group saja ('role:Super Admin,Petugas Piket')

    /**
     * Display a listing of the resource.
     * Dapat diakses oleh Super Admin & Petugas Piket.
     */
    public function index(Request $request)
    {
        // Otorisasi sudah ditangani oleh middleware route group

        $query = Attendance::query()->with(['user' => fn($q) => $q->with('kelas')])
                    ->latest('tanggal') // Urutkan tanggal terbaru dulu
                    ->latest('jam_masuk'); // Lalu jam terbaru

        // Implementasi filter sederhana
        if ($request->filled('search_name')) {
             $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search_name . '%'));
        }
        if ($request->filled('filter_date')) {
             $query->whereDate('tanggal', $request->filter_date);
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
       }

        $attendances = $query->paginate(20)->withQueryString(); // Tambahkan withQueryString agar filter terbawa di paginasi
        $filters = $request->only(['search_name', 'filter_date', 'filter_status']); // Kirim filter aktif ke view

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
        // Otorisasi: Hanya Super Admin
        
        /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin...');
        }

         $users = User::whereIn('role', ['Guru', 'Siswa'])->orderBy('name')->get();
         $attendance->load('user'); // Load relasi user
         return view('admin.attendances.edit', compact('attendance', 'users'));
    }

    /**
     * Update the specified resource in storage.
     * Hanya Super Admin.
     */
    public function update(Request $request, Attendance $attendance)
    {
        // Otorisasi: Hanya Super Admin
       /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
       $user = Auth::user();

       if (!$user->isSuperAdmin()) {
          abort(403, 'Anda tidak memiliki izin...');
      }

        $validated = $request->validate([
            // User ID tidak diubah saat edit, jadi tidak perlu divalidasi ulang di sini
            // 'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Telat,Izin,Sakit,Absen',
            'jam_masuk' => 'nullable|required_if:status,Hadir,Telat|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ],[
            'jam_masuk.required_if' => 'Jam masuk wajib diisi jika status Hadir atau Telat.'
        ]);

         // Cek duplikasi (kecuali untuk record yang sedang diedit)
         // Tidak perlu cek duplikasi saat update jika user_id dan tanggal tidak boleh diubah
         $existing = Attendance::where('user_id', $attendance->user_id) // Gunakan user_id dari record yg diedit
                              ->where('tanggal', $validated['tanggal'])
                              ->where('id', '!=', $attendance->id)
                              ->first();
         if ($existing) {
             return back()->with('error', 'Sudah ada data presensi lain untuk user & tanggal tersebut.')->withInput();
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

        // Hanya update field yang relevan untuk input manual
        $attendance->update([
             'tanggal' => $validated['tanggal'],
             'status' => $validated['status'],
             'jam_masuk' => $validated['jam_masuk'], // Akan null jika status bukan Hadir/Telat
             'keterangan' => $validated['keterangan'],
             // Biarkan field selfie, gps, dll tetap seperti semula
        ]);

        return redirect()->route('attendances.index')->with('success', 'Data presensi berhasil diperbarui.');
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
}