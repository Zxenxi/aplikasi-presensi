<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    // ... index(), store(), update(), destroy() methods are all correct and do not need changes ...

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(43, 'Akses Ditolak');
        }

        $filterTingkat = $request->input('tingkat');
        $filterNamaKelas = $request->input('search_nama_kelas');

        $query = Kelas::query()->withCount([
            'students',
            'students as active_students_count' => function ($query) {
                $query->where('is_active', true);
            }
        ]);

        if ($filterTingkat) {
            $query->where('tingkat', $filterTingkat);
        }
        if ($filterNamaKelas) {
            $query->where('nama_kelas', 'like', '%' . $filterNamaKelas . '%');
        }

        $kelas = $query->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $guru = User::where('role', 'Guru')->where('is_active', true)->orderBy('name')->get();
        $tingkatOptions = Kelas::select('tingkat')->distinct()->orderBy('tingkat')->pluck('tingkat');
        $filters = $request->only(['tingkat', 'search_nama_kelas']);

        return view('admin.classes.index', compact('kelas', 'guru', 'tingkatOptions', 'filters', 'filterTingkat', 'filterNamaKelas'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['message' => 'Akses Ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_kelas' => [
                'required', 'string', 'max:255',
                Rule::unique('kelas')->where(fn ($query) => $query->where('jurusan', $request->jurusan)->where('tingkat', $request->tingkat)),
            ],
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:100',
        ], [
            'nama_kelas.unique' => 'Kombinasi nama kelas, tingkat, dan jurusan sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Kelas::create($validator->validated());
        return response()->json(['success' => 'Kelas berhasil ditambahkan.']);
    }
    
    public function update(Request $request, Kelas $class)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['message' => 'Akses Ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_kelas' => [
                'required', 'string', 'max:255',
                Rule::unique('kelas')->where(fn ($query) => $query->where('jurusan', $request->jurusan)->where('tingkat', $request->tingkat))->ignore($class->id),
            ],
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:100',
        ], [
            'nama_kelas.unique' => 'Kombinasi nama kelas, tingkat, dan jurusan sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $class->update($validator->validated());
        return response()->json(['success' => 'Data kelas berhasil diperbarui.']);
    }

    public function destroy(Kelas $class)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
             abort(403, 'Akses Ditolak');
        }

        if ($class->students()->count() > 0) {
            return back()->with('error', 'Gagal menghapus: Kelas ini masih memiliki siswa terdaftar.');
        }

        $class->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }


    /**
     * Display the specified resource.
     */
 public function show(Kelas $class, Request $request)
    {
      /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
             abort(403, 'Akses Ditolak');
        }

        $filterStatusSiswa = $request->input('status_siswa', '1');

        $studentsQuery = User::where('kelas_id', $class->id)->where('role', 'Siswa');
        if ($filterStatusSiswa === '1') {
            $studentsQuery->where('is_active', true);
        } elseif ($filterStatusSiswa === '0') {
            $studentsQuery->where('is_active', false);
        }
        $students = $studentsQuery->orderBy('name')->get();

        $activeStudentCountInClass = User::where('kelas_id', $class->id)->where('role', 'Siswa')->where('is_active', true)->count();
        $totalStudentCountInClass = User::where('kelas_id', $class->id)->where('role', 'Siswa')->count();
        $allKelas = Kelas::where('id', '!=', $class->id)->orderBy('nama_kelas')->get();

        return view('admin.classes.show', [
            'kela' => $class, // tetap 'kela' untuk view
            'allKelas' => $allKelas,
            'filterStatusSiswa' => $filterStatusSiswa,
            'activeStudentCountInClass' => $activeStudentCountInClass,
            'totalStudentCountInClass' => $totalStudentCountInClass,
            'students' => $students
        ]);
    }

    /**
     * Handle bulk actions for students in a class.
     */
 // app/Http/Controllers/Admin/KelasController.php

    // app/Http/Controllers/Admin/KelasController.php

// app/Http/Controllers/Admin/KelasController.php

public function bulkUpdateStudents(Request $request, Kelas $class)
{
    // 1. Validasi input yang masuk
    $validated = $request->validate([
        'bulk_action' => 'required|string|in:activate,deactivate',
        'siswa_ids' => 'required|array|min:1',
        'siswa_ids.*' => 'integer|exists:users,id', // Memastikan semua ID ada di tabel users
    ]);

    $siswaIds = $validated['siswa_ids'];
    $action = $validated['bulk_action'];

    // 2. Tentukan status boolean berdasarkan aksi yang dipilih
    // Jika aksinya 'activate', maka $newStatus akan menjadi true.
    // Jika aksinya 'deactivate', maka $newStatus akan menjadi false.
    $newStatus = ($action === 'activate');

    try {
        // 3. Lakukan update ke database dalam satu query yang efisien
        $updateCount = User::where('kelas_id', $class->id)
                            ->whereIn('id', $siswaIds)
                            ->update(['is_active' => $newStatus]);

        // 4. Berikan feedback ke admin
        if ($updateCount > 0) {
            $actionText = $newStatus ? 'diaktifkan': 'dinonaktifkan';
return back()->with('success', "$updateCount siswa berhasil {$actionText}.");
} else {
// Ini terjadi jika semua siswa yang dipilih sudah memiliki status yang sama
// dengan aksi yang dijalankan (misal: mencoba menonaktifkan siswa yang sudah nonaktif).
return back()->with('info', 'Tidak ada siswa yang diperbarui. Status mereka mungkin sudah sesuai dengan aksi yang dipilih.');
}} catch (\Exception $e) {
    // 5. Tangani jika terjadi error pada database
    Log::error('Gagal melakukan aksi massal siswa: ' . $e->getMessage());
    return back()->with('error', 'Terjadi kesalahan pada server saat mencoba memperbarui data siswa.');
}
}
}