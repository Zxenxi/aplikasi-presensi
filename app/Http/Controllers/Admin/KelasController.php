<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User; // Untuk ambil data guru
use Illuminate\Http\Request;
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
        $guru = User::where('role', 'Guru')->orderBy('name')->get();

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
}