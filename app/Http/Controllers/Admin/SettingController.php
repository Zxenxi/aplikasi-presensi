<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Model Setting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk cek user login
use Illuminate\Validation\Rule; // Jika diperlukan untuk validasi kompleks

class SettingController extends Controller
{
    /**
     * Show the form for editing the application settings.
     */
    public function edit()
    {
        // Otorisasi: Hanya Super Admin yang boleh akses
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
        }

        // Ambil data setting. Gunakan firstOrCreate untuk memastikan selalu ada data,
        // meskipun seeder seharusnya sudah mengisi data awal.
        // Sediakan nilai default jika data belum ada sama sekali.
        $settings = Setting::firstOrCreate([], [
            'school_latitude' => -7.741303, // Default jika tabel kosong
            'school_longitude' => 109.915419, // Default jika tabel kosong
            // 'school_latitude' => -6.200000, // Default jika tabel kosong
            // 'school_longitude' => 106.816666, // Default jika tabel kosong
            'allowed_radius_meters' => 100,
            'attendance_start_time' => '07:00:00',
            'attendance_end_time' => '16:00:00',
            'late_threshold_time' => '07:15:00',
        ]);


        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update the application settings in storage.
     */
    public function update(Request $request)
    {
         // Otorisasi: Hanya Super Admin
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
        }

        // Validasi input dari form
        $validated = $request->validate([
            // Koordinat: Wajib, Numerik
            'school_latitude' => ['required', 'numeric', 'between:-90,90'], // Rentang valid latitude
            'school_longitude' => ['required', 'numeric', 'between:-180,180'], // Rentang valid longitude
            // Radius: Wajib, Angka, Minimal 10 meter (misalnya)
            'allowed_radius_meters' => ['required', 'integer', 'min:10'],
            // Waktu: Wajib, Format H:i atau H:i:s
            'attendance_start_time' => ['required', 'date_format:H:i', 'before:attendance_end_time'], // Jam mulai harus sebelum jam selesai
            'attendance_end_time' => ['required', 'date_format:H:i', 'after:attendance_start_time'], // Jam selesai harus setelah jam mulai
            'late_threshold_time' => ['required', 'date_format:H:i', 'after:attendance_start_time', 'before:attendance_end_time'], // Batas telat harus di antara jam mulai & selesai
        ],[
            // Custom error messages (opsional)
            'school_latitude.between' => 'Latitude harus antara -90 dan 90.',
            'school_longitude.between' => 'Longitude harus antara -180 dan 180.',
            'allowed_radius_meters.min' => 'Radius minimal adalah 10 meter.',
            'date_format' => 'Format waktu harus HH:MM (contoh: 07:00).',
            'attendance_start_time.before' => 'Jam Mulai Presensi harus sebelum Jam Selesai Presensi.',
            'attendance_end_time.after' => 'Jam Selesai Presensi harus setelah Jam Mulai Presensi.',
            'late_threshold_time.after' => 'Batas Waktu Telat harus setelah Jam Mulai Presensi.',
            'late_threshold_time.before' => 'Batas Waktu Telat harus sebelum Jam Selesai Presensi.',
        ]);

        // Ambil data setting (seharusnya hanya ada 1 baris)
        $settings = Setting::firstOrFail();

        // Update data setting dengan data tervalidasi
        $settings->update($validated);

        // Redirect kembali ke halaman edit dengan pesan sukses
        return redirect()->route('settings.edit')
                         ->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }
}