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
// app/Http/Controllers/Admin/KelasController.php
public function index(Request $request) // Tambahkan Request $request
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    // if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) { // Sesuaikan jika Petugas Piket dihapus
    if (!$user->isSuperAdmin()) { // Jika hanya Super Admin
         abort(403, 'Akses Ditolak');
    }

    $filterTingkat = $request->input('tingkat');
    $filterWaliKelas = $request->input('wali_kelas_id');

    $query = Kelas::query()->with(['waliKelas', 'students']);

    if ($filterTingkat) {
        $query->where('tingkat', $filterTingkat);
    }

    if ($filterWaliKelas) {
        $query->where('wali_kelas_id', $filterWaliKelas);
    }

    $kelas = $query->orderBy('tingkat')->orderBy('nama_kelas')->get();
    $guru = User::where('role', 'Guru')->where('is_active', true)->orderBy('name')->get(); // Hanya guru aktif
    $tingkatOptions = Kelas::select('tingkat')->distinct()->orderBy('tingkat')->pluck('tingkat'); // Ambil opsi tingkat

    return view('admin.classes.index', compact('kelas', 'guru', 'tingkatOptions', 'filterTingkat', 'filterWaliKelas'));
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
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        if (!empty($validated['wali_kelas_id'])) {
            $wali = User::find($validated['wali_kelas_id']);
            /** @var \App\Models\User|null $wali */ // Hint tambahan untuk $wali
            if (!$wali || !$wali->isGuru()) { // Panggil isGuru() pada objek User $wali
                 return back()
                        ->withErrors(['wali_kelas_id' => 'Wali kelas yang dipilih harus memiliki role Guru.'])
                        ->withInput();
            }
        } else {
            $validated['wali_kelas_id'] = null;
        }

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
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        if (!empty($validated['wali_kelas_id'])) {
            $wali = User::find($validated['wali_kelas_id']);
             /** @var \App\Models\User|null $wali */ // Hint tambahan untuk $wali
            if (!$wali || !$wali->isGuru()) { // Panggil isGuru() pada objek User $wali
                 return back()
                        ->withErrors(['wali_kelas_id' => 'Wali kelas yang dipilih harus memiliki role Guru.'])
                        ->withInput();
            }
        } else {
            $validated['wali_kelas_id'] = null;
        }

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
    // ... (kode otorisasi) ...
    $filterStatusSiswa = $request->input('status_siswa', '1');

    $kela->load(['waliKelas', 'students' => function ($query) use ($filterStatusSiswa) {
        $query->where('role', 'Siswa');
        if ($filterStatusSiswa !== 'all') {
            $query->where('is_active', (bool)$filterStatusSiswa);
        }
        $query->orderBy('name');
    }]);

    // Ambil semua kelas KECUALI kelas saat ini untuk opsi pindah kelas
    $allKelas = Kelas::where('id', '!=', $kela->id)->orderBy('nama_kelas')->get();

    return view('admin.classes.show', compact('kela', 'allKelas', 'filterStatusSiswa'));
}
 public function bulkUpdateStudents(Request $request, Kelas $kela)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'bulk_action' => 'required|string|in:activate,deactivate,move_class',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
            'target_kelas_id' => 'nullable|required_if:bulk_action,move_class|exists:kelas,id|different:'.$kela->id,
        ],[
            'bulk_action.required' => 'Aksi massal harus dipilih.',
            'siswa_ids.required' => 'Tidak ada siswa yang dipilih.',
            'target_kelas_id.required_if' => 'Kelas tujuan harus dipilih untuk aksi pindah kelas.',
            'target_kelas_id.different' => 'Kelas tujuan tidak boleh sama dengan kelas asal.',
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
                DB::rollBack(); // Pastikan rollback jika tidak ada siswa yang valid
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
                            continue 2; // <-- PERBAIKAN DI SINI: Lanjutkan ke iterasi foreach berikutnya
                        }
                        $siswa->is_active = false;
                        $siswa->save();
                        $updatedCount++;
                        break;
                    case 'move_class':
                        $siswa->kelas_id = $validated['target_kelas_id'];
                        // Pertimbangkan untuk mengaktifkan siswa jika dipindahkan ke kelas baru, jika relevan
                        // $siswa->is_active = true;
                        $siswa->save();
                        $updatedCount++;
                        break;
                }
            }
            DB::commit();
            // Ganti _ dengan spasi dan buat huruf pertama kapital untuk pesan yang lebih baik
            $actionFriendlyName = ucfirst(str_replace('_', ' ', $action));
            return back()->with('success', "$updatedCount siswa berhasil diproses dengan aksi: " . $actionFriendlyName . ".");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat aksi massal siswa: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Baris: ' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan saat memproses aksi massal. Silakan coba lagi.');
        }
    }
}