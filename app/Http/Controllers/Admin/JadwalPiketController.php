<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPiket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Untuk unique rule yang lebih kompleks jika perlu

class JadwalPiketController extends Controller
{
    // Hanya Super Admin yang boleh akses semua method di controller ini
    public function __construct()
    {
        // Anda bisa terapkan middleware di sini atau di route
        // $this->middleware('role:Super Admin');
    }

    public function index()
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');

        // Ambil semua jadwal, diurutkan berdasarkan hari, lalu jam, lalu nama guru
        // Eager load relasi user agar tidak terjadi N+1 query
        $jadwalPiketList = JadwalPiket::with('user')
                            ->orderBy('hari_ke')
                            ->orderBy('jam_mulai')
                            ->orderBy(User::select('name')->whereColumn('users.id', 'jadwal_piket.user_id'))
                            ->get();

        // Kelompokkan berdasarkan hari untuk tampilan yang lebih baik
        $jadwalPiketGrouped = $jadwalPiketList->groupBy('hari_ke');
        $daysOrder = [1, 2, 3, 4, 5, 6, 7]; // Senin - Minggu

        return view('admin.jadwal_piket.index', compact('jadwalPiketGrouped', 'daysOrder'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');
        // Ambil hanya user dengan role 'Guru' atau 'Petugas Piket' yang bisa piket
        $teachers = User::whereIn('role', ['Guru', 'Petugas Piket'])->orderBy('name')->get();
        return view('admin.jadwal_piket.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || (!$user->isGuru() && !$user->isPetugasPiket())) {
                        $fail('Petugas piket harus seorang Guru atau Petugas Piket.');
                    }
                },
                // Unique rule untuk user_id dan hari_ke
                Rule::unique('jadwal_piket')->where(function ($query) use ($request) {
                    return $query->where('hari_ke', $request->hari_ke);
                })
            ],
            'hari_ke' => 'required|integer|between:1,7',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after_or_equal:jam_mulai',
            'keterangan_tugas' => 'nullable|string|max:1000',
        ], [
            'user_id.unique' => 'Guru tersebut sudah memiliki jadwal piket pada hari yang sama.',
            'jam_selesai.after_or_equal' => 'Jam selesai harus setelah atau sama dengan jam mulai.'
        ]);

        JadwalPiket::create($validated);
        return redirect()->route('admin.picket_schedules.index')
                         ->with('success', 'Jadwal piket berhasil ditambahkan.');
    }

    public function edit(JadwalPiket $picket_schedule) // Route model binding
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');
        $teachers = User::whereIn('role', ['Guru', 'Petugas Piket'])->orderBy('name')->get();
        return view('admin.jadwal_piket.edit', compact('picket_schedule', 'teachers'));
    }

    public function update(Request $request, JadwalPiket $picket_schedule)
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || (!$user->isGuru() && !$user->isPetugasPiket())) {
                        $fail('Petugas piket harus seorang Guru atau Petugas Piket.');
                    }
                },
                Rule::unique('jadwal_piket')->where(function ($query) use ($request) {
                    return $query->where('hari_ke', $request->hari_ke);
                })->ignore($picket_schedule->id) // Abaikan record saat ini
            ],
            'hari_ke' => 'required|integer|between:1,7',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after_or_equal:jam_mulai',
            'keterangan_tugas' => 'nullable|string|max:1000',
        ], [
            'user_id.unique' => 'Guru tersebut sudah memiliki jadwal piket lain pada hari yang sama.',
             'jam_selesai.after_or_equal' => 'Jam selesai harus setelah atau sama dengan jam mulai.'
        ]);

        $picket_schedule->update($validated);
        return redirect()->route('admin.picket_schedules.index')
                         ->with('success', 'Jadwal piket berhasil diperbarui.');
    }

    public function destroy(JadwalPiket $picket_schedule)
    {
        if (Auth::user()->role !== 'Super Admin') abort(403, 'Akses Ditolak');
        $picket_schedule->delete();
        return redirect()->route('admin.picket_schedules.index')
                         ->with('success', 'Jadwal piket berhasil dihapus.');
    }
}