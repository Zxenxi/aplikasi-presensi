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
    public function bulkUpdateStudents(Request $request, Kelas $class)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'bulk_action' => 'required|string|in:activate,deactivate,move_class',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:users,id',
            'target_kelas_id' => 'required_if:bulk_action,move_class|exists:kelas,id',
        ], [
            'bulk_action.required' => 'Aksi massal harus dipilih.',
            'siswa_ids.required' => 'Tidak ada siswa yang dipilih.',
            'siswa_ids.array' => 'Format data siswa tidak valid.',
            'target_kelas_id.required_if' => 'Kelas tujuan harus dipilih untuk aksi pindah kelas.',
        ]);

        $siswaIds = $validated['siswa_ids'];

        DB::beginTransaction();
        try {
            $studentsToUpdate = User::whereIn('id', $siswaIds)->where('kelas_id', $class->id)->get();

            foreach ($studentsToUpdate as $student) {
                if ($validated['bulk_action'] === 'activate') {
                    $student->is_active = true;
                    $student->save();
                } elseif ($validated['bulk_action'] === 'deactivate') {
                    if ($student->id !== $currentUser->id) {
                        $student->is_active = false;
                        $student->save();
                    }
                } elseif ($validated['bulk_action'] === 'move_class') {
                    $student->kelas_id = $validated['target_kelas_id'];
                    $student->save();
                }
            }

            DB::commit();

            $selectedCount = count($siswaIds);

            if ($validated['bulk_action'] === 'move_class') {
                $targetKelas = Kelas::find($validated['target_kelas_id']);
                return redirect()->route('admin.classes.show', ['class' => $class])
                    ->with('success', "$selectedCount siswa berhasil dipindahkan ke kelas {$targetKelas->nama_kelas}.");
            }

            $actionFriendlyName = $validated['bulk_action'] === 'activate' ? 'diaktifkan' : 'dinonaktifkan';
            $redirectFilter = $validated['bulk_action'] === 'activate' ? '1' : '0';

            return redirect()->route('admin.classes.show', [
                'class' => $class,
                'status_siswa' => $redirectFilter
            ])->with('success', "$selectedCount siswa berhasil {$actionFriendlyName}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat aksi massal siswa: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses aksi massal.');
        }
    }
}
