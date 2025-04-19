<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User; // Untuk ambil data guru
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Otorisasi: Super Admin & Petugas Piket bisa lihat
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isPetugasPiket()) abort(403, 'Akses Ditolak');

        $kelas = Kelas::with(['waliKelas', 'students']) // Eager load relasi waliKelas dan students (untuk count)
                      ->orderBy('tingkat')
                      ->orderBy('nama_kelas')
                      ->get();

        // Ambil data guru untuk dropdown wali kelas di modal
        $guru = User::where('role', 'Guru')->orderBy('name')->get();

        return view('admin.classes.index', compact('kelas', 'guru'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Otorisasi: Hanya Super Admin
        if (!Auth::user()->isSuperAdmin()) abort(403, 'Akses Ditolak');

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
            'tingkat' => 'required|integer|min:1|max:12', // Sesuaikan max jika perlu
            'jurusan' => 'nullable|string|max:100',
            'wali_kelas_id' => 'nullable|exists:users,id', // Pastikan ID user ada
        ]);

        // Validasi tambahan: pastikan wali_kelas_id adalah seorang Guru
        if (!empty($validated['wali_kelas_id'])) {
            $wali = User::find($validated['wali_kelas_id']);
            if (!$wali || !$wali->isGuru()) {
                 // Kembalikan dengan error spesifik
                 return back()
                        ->withErrors(['wali_kelas_id' => 'Wali kelas yang dipilih harus memiliki role Guru.'])
                        ->withInput(); // Kembalikan input sebelumnya
            }
        } else {
            $validated['wali_kelas_id'] = null; // Set null jika kosong
        }


        Kelas::create($validated);

        return back()->with('success', 'Kelas berhasil ditambahkan.'); // Kembali ke halaman index kelas
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kela) // Nama parameter $kela sesuai route
    {
        // Otorisasi: Hanya Super Admin
        if (!Auth::user()->isSuperAdmin()) abort(403, 'Akses Ditolak');

         $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas,' . $kela->id, // Abaikan ID kelas ini saat cek unik
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:100',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

         // Validasi tambahan: pastikan wali_kelas_id adalah seorang Guru
        if (!empty($validated['wali_kelas_id'])) {
            $wali = User::find($validated['wali_kelas_id']);
            if (!$wali || !$wali->isGuru()) {
                 return back()
                        ->withErrors(['wali_kelas_id' => 'Wali kelas yang dipilih harus memiliki role Guru.'])
                        ->withInput();
            }
        } else {
            $validated['wali_kelas_id'] = null; // Set null jika kosong
        }


        $kela->update($validated);

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kela)
    {
        // Otorisasi: Hanya Super Admin
        if (!Auth::user()->isSuperAdmin()) abort(403, 'Akses Ditolak');

        // PENTING: Cek relasi ke siswa sebelum menghapus
        if ($kela->students()->count() > 0) {
            return back()->with('error', 'Gagal menghapus: Kelas ini masih memiliki siswa terdaftar. Pindahkan atau hapus siswa terlebih dahulu.');
        }

        // Opsional: Hapus relasi wali kelas jika diperlukan (biasanya tidak perlu jika onDelete null/cascade tidak diset)
        // $kela->wali_kelas_id = null;
        // $kela->save();

        $kela->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}