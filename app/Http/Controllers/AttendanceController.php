<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Untuk simpan file
use Carbon\Carbon; // Untuk manipulasi waktu

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman form untuk melakukan presensi.
     */
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $settings = Setting::firstOrFail(); // Ambil pengaturan

        // Cek apakah sudah presensi hari ini
        $alreadyAttended = Attendance::where('user_id', $user->id)
                                    ->where('tanggal', $today)
                                    ->exists();

        if ($alreadyAttended) {
            return redirect()->route('attendance.history')->with('warning', 'Anda sudah melakukan presensi hari ini.');
        }

        return view('attendance.create');
    }

    /**
     * Menyimpan data presensi baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $settings = Setting::firstOrFail();
        $now = Carbon::now();
        $currentTime = $now->toTimeString('minute'); // Format HH:MM

        // --- Validasi Awal ---
        // 1. Cek lagi apakah sudah presensi
        $alreadyAttended = Attendance::where('user_id', $user->id)->where('tanggal', $today)->exists();
        if ($alreadyAttended) {
            return redirect()->route('attendance.history')->with('warning', 'Anda sudah melakukan presensi hari ini.');
        }

         // 2. Cek lagi waktu presensi
        $startTime = Carbon::parse($settings->attendance_start_time);
        $endTime = Carbon::parse($settings->attendance_end_time);
        if (!$now->between($startTime, $endTime)) {
             return redirect()->route('attendance.history')
                             ->with('error', 'Waktu presensi sudah habis.');
        }

         // 3. Validasi Input Request
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'selfie_image_base64' => ['required', 'string'], // Validasi base64 string
        ],[
            'latitude.required' => 'Lokasi GPS tidak terdeteksi.',
            'longitude.required' => 'Lokasi GPS tidak terdeteksi.',
            'selfie_image_base64.required' => 'Foto selfie wajib diambil.',
        ]);

        // --- Proses Data ---
        // 1. Validasi Lokasi
        $distance = $this->calculateDistance(
            $request->latitude, $request->longitude,
            $settings->school_latitude, $settings->school_longitude
        );
        $is_location_valid = $distance <= $settings->allowed_radius_meters;

         // Jika lokasi tidak valid, bisa ditolak atau tetap disimpan dengan tanda
        if (!$is_location_valid) {
            return back()->with('error', 'Presensi gagal: Anda berada di luar area sekolah.');
        }


        // 2. Simpan Gambar Selfie
        $selfie_path = null;
        if ($request->filled('selfie_image_base64')) {
            try {
                // Ambil data base64, hapus prefix
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->selfie_image_base64));
                // Buat nama file unik
                $imageName = 'selfie_' . $user->id . '_' . time() . '.png';
                // Simpan ke storage/app/public/selfies
                Storage::disk('public')->put('selfies/' . $imageName, $imageData);
                // Simpan path relatif untuk database
                $selfie_path = 'selfies/' . $imageName;
            } catch (\Exception $e) {
                // Handle error jika gagal simpan gambar
                return back()->with('error', 'Gagal menyimpan foto selfie. Silakan coba lagi.');
            }
        } else {
             return back()->with('error', 'Foto selfie tidak ditemukan.'); // Seharusnya sudah divalidasi required
        }

        // 3. Tentukan Status Presensi (Hadir/Telat)
        $lateTime = Carbon::parse($settings->late_threshold_time);
        $status = $now->lte($lateTime) ? 'Hadir' : 'Telat';


        // --- Simpan ke Database ---
        try {
            Attendance::create([
                'user_id' => $user->id,
                'tanggal' => $today,
                'jam_masuk' => $currentTime,
                'status' => $status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'selfie_path' => $selfie_path,
                'is_location_valid' => $is_location_valid,
            ]);

            return redirect()->route('attendance.history')
                             ->with('success', 'Presensi berhasil dicatat pada jam ' . $currentTime . '. Status: ' . $status . ($is_location_valid ? '' : ' (Lokasi Tidak Valid)'));

        } catch (\Exception $e) {
            // Jika gagal simpan ke DB, hapus foto yang mungkin sudah terupload
            if ($selfie_path && Storage::disk('public')->exists($selfie_path)) {
                Storage::disk('public')->delete($selfie_path);
            }
             // Tampilkan pesan error umum
             Log::error("Error saving attendance: ". $e->getMessage()); // Log error untuk debug
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data presensi.');
        }
    }


    /**
     * Menampilkan riwayat presensi user yang sedang login.
     */
    public function index()
    {
        /** @var \App\Models\User $user */ // <-- TAMBAHKAN PHPDoc HINT INI
        $user = Auth::user();

        // Otorisasi manual
        if (!$user->isGuru() && !$user->isSiswa()) { // IDE sekarang mengenali isGuru/isSiswa
             abort(403, 'Hanya Guru dan Siswa yang dapat melihat riwayat presensi.');
        }

        // IDE sekarang mengenali method attendances() pada $user
        $attendances = $user->attendances()
                            ->orderBy('tanggal', 'desc') // Urutkan terbaru dulu
                            ->paginate(20); // Paginasi

        return view('attendance.history', compact('attendances'));
    }


    /**
     * Menghitung jarak antara dua titik GPS menggunakan formula Haversine.
     * Mengembalikan jarak dalam meter.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Jarak dalam meter

        return $distance;
    }
}