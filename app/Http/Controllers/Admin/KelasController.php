<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User; // Untuk ambil data guru
use Illuminate\Support\Facades\Auth; // Import Auth
use Illuminate\Support\Facades\Log;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak');
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */ // <-- PHPDoc Hint
        $user = Auth::user();

        // Otorisasi: Hanya Super Admin
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak');
        }

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:100',
        ]);


        Kelas::create($validated);
        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kela)
    {
        /** @var \App\Models\User $user */ // <-- PHPDoc Hint
        $user = Auth::user();

        // Otorisasi: Hanya Super Admin
        if (!$user->isSuperAdmin()) {
             abort(403, 'Akses Ditolak');
        }

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas,' . $kela->id,
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:100',
            // 'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $kela->update($validated);
        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kela)
    {
        /** @var \App\Models\User $user */ // <-- PHPDoc Hint
        $user = Auth::user();

        // Otorisasi: Hanya Super Admin
        if (!$user->isSuperAdmin()) {
             abort(403, 'Akses Ditolak');
        }

        if ($kela->students()->count() > 0) {
            return back()->with('error', 'Gagal menghapus: Kelas ini masih memiliki siswa terdaftar.');
        }

        $kela->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
     /**
     * Menampilkan form untuk proses kenaikan kelas.
     */
    public function showPromotionForm()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('admin.classes.promote', compact('kelas'));
    }

    /**
     * Memproses kenaikan kelas.
     */
    public function processPromotion(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'kelas_asal_id' => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id|different:kelas_asal_id',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:users,id', // Pastikan semua ID siswa valid
        ], [
            'kelas_asal_id.required' => 'Kelas asal harus dipilih.',
            'kelas_tujuan_id.required' => 'Kelas tujuan harus dipilih.',
            'kelas_tujuan_id.different' => 'Kelas tujuan tidak boleh sama dengan kelas asal.',
            'siswa_ids.required' => 'Tidak ada siswa yang dipilih untuk dinaikkan kelasnya.',
        ]);

        $kelasAsal = Kelas::find($validated['kelas_asal_id']);
        $kelasTujuan = Kelas::find($validated['kelas_tujuan_id']);

        if (!$kelasAsal || !$kelasTujuan) {
            return back()->with('error', 'Kelas asal atau tujuan tidak valid.');
        }

        $siswaUntukDinaikkan = User::where('role', 'Siswa')
            ->where('kelas_id', $kelasAsal->id)
            ->whereIn('id', $validated['siswa_ids']) // Hanya siswa yang diceklist dan ada di kelas asal
            ->where('is_active', true) // Pertimbangkan hanya siswa aktif
            ->get();

        if ($siswaUntukDinaikkan->isEmpty()) {
            return back()->with('warning', 'Tidak ada siswa aktif yang valid dari kelas asal yang dipilih untuk dipindahkan.');
        }

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($siswaUntukDinaikkan as $siswa) {
                $siswa->kelas_id = $kelasTujuan->id;
                $siswa->save();
                $updatedCount++;
            }
            DB::commit();
            return redirect()->route('admin.classes.index')->with('success', "$updatedCount siswa dari kelas {$kelasAsal->nama_kelas} berhasil dinaikkan ke kelas {$kelasTujuan->nama_kelas}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat proses kenaikan kelas: ' . $e->getMessage());
        }
    }

    public function show(Kelas $kela, Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    if (!$user->isSuperAdmin()) {
        abort(403, 'Akses Ditolak');
    }

    $filterStatusSiswa = $request->input('status_siswa', '1');

    $kela->load(['students' => function ($query) use ($filterStatusSiswa) {
        $query->where('role', 'Siswa');

        if ($filterStatusSiswa === '1') {
            $query->where('is_active', true);
        } elseif ($filterStatusSiswa === '0') {
            $query->where('is_active', false);
        }
        // Jika 'all', tidak ada filter is_active yang diterapkan
        
        $query->orderBy('name');
    }]);
    
    $activeStudentCountInClass = User::where('kelas_id', $kela->id)->where('is_active', true)->count();
    $totalStudentCountInClass = User::where('kelas_id', $kela->id)->count();
    $allKelas = Kelas::where('id', '!=', $kela->id)->orderBy('nama_kelas')->get();

    return view('admin.classes.show', compact('kela', 'allKelas', 'filterStatusSiswa', 'activeStudentCountInClass', 'totalStudentCountInClass'));
}

     public function bulkUpdateStudents(Request $request, Kelas $kela)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'bulk_action' => 'required|string|in:activate,deactivate',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
        ],[
            'bulk_action.required' => 'Aksi massal harus dipilih.',
            'siswa_ids.required' => 'Tidak ada siswa yang dipilih.',
        ]);

        $siswaIds = $validated['siswa_ids'];
        $action = $validated['bulk_action'];
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            $studentsToUpdate = User::whereIn('id', $siswaIds)
                                    ->where('kelas_id', $kela->id)
                                    ->where('role', 'Siswa')
                                    ->get();

            if($studentsToUpdate->isEmpty()){
                DB::rollBack();
                return back()->with('warning', 'Tidak ada siswa valid yang ditemukan untuk diproses dari kelas ini.');
            }

            foreach ($studentsToUpdate as $siswa) {
                switch ($action) {
                    case 'activate':
                        $siswa->is_active = true;
                        $siswa->save();
                        $updatedCount++;
                        break;
                    case 'deactivate':
                        if ($siswa->id === $currentUser->id) {
                            continue 2;
                        }
                        $siswa->is_active = false;
                        $siswa->save();
                        $updatedCount++;
                        break;
                }
            }
            DB::commit();
            $actionFriendlyName = ucfirst(str_replace('_', ' ', $action));
            return back()->with('success', "$updatedCount siswa berhasil diproses dengan aksi: " . $actionFriendlyName . ".");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat aksi massal siswa: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Baris: ' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan saat memproses aksi massal. Silakan coba lagi.');
        }
    }
}