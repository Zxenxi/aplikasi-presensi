<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User; // Untuk ambil data guru
use Illuminate\Support\Facades\Auth; // Import Auth

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */ // <-- PHPDoc Hint
        $user = Auth::user();

        // Otorisasi: Super Admin & Petugas Piket bisa lihat
        if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) {
             abort(403, 'Akses Ditolak');
        }

        $kelas = Kelas::with(['waliKelas', 'students'])
                      ->orderBy('tingkat')
                      ->orderBy('nama_kelas')
                      ->get();
        // $guru = User::where('role', 'Guru')->orderBy('name')->get();
        // Di KelasController::index()
$guru = User::where('role', 'Guru')->where('is_active', true)->orderBy('name')->get();

        return view('admin.classes.index', compact('kelas', 'guru'));
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
}