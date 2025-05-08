<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PicketSchedule;
use App\Models\User; // Untuk ambil daftar petugas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PicketScheduleController extends Controller
{
    // Middleware bisa ditaruh di route khusus Super Admin

    /**
     * Display a listing of the resource.
     */ 
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Otorisasi: Hanya Super Admin? Atau Piket juga boleh lihat? Sesuaikan.
        if (!$user->isSuperAdmin()) { // Contoh: Hanya Super Admin
             abort(403, 'Hanya Super Admin yang dapat mengakses jadwal piket.');
        }

        // Ambil filter bulan dan tahun dari request
        // Default ke bulan dan tahun saat ini jika tidak ada input
        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', Carbon::now()->month); // Ambil angka bulan (1-12)

        // Validasi dasar (pastikan angka)
        $selectedYear = filter_var($selectedYear, FILTER_VALIDATE_INT, ['options' => ['default' => Carbon::now()->year]]);
        $selectedMonth = filter_var($selectedMonth, FILTER_VALIDATE_INT, ['options' => ['default' => Carbon::now()->month, 'min_range' => 1, 'max_range' => 12]]);

        // Buat tanggal awal dan akhir bulan berdasarkan filter
        try {
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            // Handle jika tanggal tidak valid (jarang terjadi dengan validasi di atas)
            $startDate = Carbon::now()->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            // Set ulang selectedYear & selectedMonth ke nilai default
             $selectedYear = $startDate->year;
             $selectedMonth = $startDate->month;
        }


        // Ambil data jadwal sesuai rentang tanggal
        $schedules = PicketSchedule::with('user')
                        ->whereBetween('duty_date', [$startDate, $endDate])
                        ->orderBy('duty_date', 'asc')
                        ->get();

        // Siapkan data untuk dropdown tahun (misal: 2 tahun ke belakang & 1 tahun ke depan)
        $currentYear = Carbon::now()->year;
        $years = range($currentYear + 1, $currentYear - 2); // Urutan dari baru ke lama

        // Siapkan data bulan
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->isoFormat('MMMM'); // Nama bulan lengkap
        }


        // Kirim data ke view
        return view('admin.picket_schedules.index', compact(
            'schedules',
            'selectedYear',
            'selectedMonth',
            'years', // Kirim rentang tahun
            'months' // Kirim nama bulan
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) abort(403);

        // Ambil user yang bisa piket (misal: Petugas Piket atau Guru)
        $potentialOfficers = User::whereIn('role', ['Petugas Piket', 'Guru', 'Super Admin']) // Sesuaikan role yang boleh piket
                                 ->orderBy('name')
                                 ->get();

        return view('admin.picket_schedules.create', compact('potentialOfficers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) abort(403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) {
                // Validasi tambahan: Pastikan user yg dipilih punya role yg diizinkan
                $piketUser = User::find($value);
                if (!$piketUser || !in_array($piketUser->role, ['Petugas Piket', 'Guru', 'Super Admin'])) {
                    $fail('Pengguna yang dipilih tidak valid untuk tugas piket.');
                }
            }],
            'duty_date' => 'required|date|after_or_equal:today', // Tidak bisa jadwal tanggal lampau
            'notes' => 'nullable|string',
            // Tambahkan validasi unique jika diperlukan
            // 'duty_date' => 'required|date|unique:picket_schedules,duty_date' // Jika hanya 1 per hari
        ],[
            'user_id.required' => 'Petugas wajib dipilih.',
            'duty_date.required' => 'Tanggal tugas wajib diisi.',
            'duty_date.after_or_equal' => 'Tanggal tugas tidak boleh tanggal yang sudah lewat.',
            // 'duty_date.unique' => 'Sudah ada petugas yang dijadwalkan di tanggal ini.'
        ]);

         // Cek unique user per tanggal jika aturannya begitu
         $existing = PicketSchedule::where('user_id', $validated['user_id'])
                                   ->where('duty_date', $validated['duty_date'])
                                   ->exists();
         if($existing) {
             return back()->with('error', 'Petugas ini sudah dijadwalkan di tanggal tersebut.')->withInput();
         }

        PicketSchedule::create($validated);

        return redirect()->route('admin.picket-schedules.index', ['month' => Carbon::parse($validated['duty_date'])->format('Y-m')])
                         ->with('success', 'Jadwal piket berhasil ditambahkan.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PicketSchedule $picketSchedule) // Route model binding
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) abort(403);

        $potentialOfficers = User::whereIn('role', ['Petugas Piket', 'Guru', 'Super Admin'])
                                 ->orderBy('name')
                                 ->get();
        $picketSchedule->load('user'); // Load data user terkait

        return view('admin.picket_schedules.edit', compact('picketSchedule', 'potentialOfficers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PicketSchedule $picketSchedule)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) abort(403);

        $validated = $request->validate([
             'user_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) { /* ... validasi role ... */ }],
             'duty_date' => 'required|date', // Boleh edit tanggal lampau? Sesuaikan jika tidak
             'notes' => 'nullable|string',
              // Validasi unique jika perlu (kecuali untuk ID yg diedit)
             // 'duty_date' => 'required|date|unique:picket_schedules,duty_date,' . $picketSchedule->id
        ]);

        // Cek unique user per tanggal (kecuali untuk ID yg diedit)
         $existing = PicketSchedule::where('user_id', $validated['user_id'])
                                   ->where('duty_date', $validated['duty_date'])
                                   ->where('id', '!=', $picketSchedule->id)
                                   ->exists();
         if($existing) {
             return back()->with('error', 'Petugas ini sudah dijadwalkan di tanggal tersebut.')->withInput();
         }

        $picketSchedule->update($validated);

        return redirect()->route('admin.picket-schedules.index', ['month' => Carbon::parse($validated['duty_date'])->format('Y-m')])
                         ->with('success', 'Jadwal piket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PicketSchedule $picketSchedule)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) abort(403);

        $month = $picketSchedule->duty_date->format('Y-m'); // Simpan bulan sebelum dihapus
        $picketSchedule->delete();

        return redirect()->route('admin.picket-schedules.index', ['month' => $month])
                         ->with('success', 'Jadwal piket berhasil dihapus.');
    }
}