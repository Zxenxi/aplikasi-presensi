<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;      // Ambil data dari query Eloquent
use Maatwebsite\Excel\Concerns\WithHeadings;  // Tentukan header kolom
use Maatwebsite\Excel\Concerns\WithMapping;   // Ubah data per baris
use Maatwebsite\Excel\Concerns\ShouldAutoSize;// Atur lebar kolom otomatis
use Maatwebsite\Excel\Concerns\WithStyles;    // Beri style (misal bold header)
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Storage; // Untuk akses URL selfie
use Carbon\Carbon; // Untuk format tanggal/waktu

class AttendanceReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    // Terima filter dari controller
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // --- Bangun Query Sama Persis Seperti di ReportController@index ---
        // Ini penting agar data yang diexport konsisten dengan yang tampil
        $query = Attendance::query()->with(['user' => function ($query) {
                $query->with('kelas'); // Eager load kelas user jika ada
            }]);

        // Terapkan filter tanggal (Harus ada)
        if (!empty($this->filters['tanggal_mulai']) && !empty($this->filters['tanggal_selesai'])) {
            $query->whereBetween('tanggal', [$this->filters['tanggal_mulai'], $this->filters['tanggal_selesai']]);
        } else {
             // Default jika tidak ada tanggal (misal 7 hari terakhir), sesuaikan jika perlu
             // Atau bisa juga mengembalikan query kosong jika tanggal wajib
              $query->whereBetween('tanggal', [now()->subDays(6)->toDateString(), now()->toDateString()]);
        }


        // Terapkan filter lain
         if (!empty($this->filters['tipe_user'])) {
            $query->whereHas('user', fn($q) => $q->where('role', $this->filters['tipe_user']));
        }
        if (!empty($this->filters['tipe_user']) && $this->filters['tipe_user'] === 'Siswa' && !empty($this->filters['kelas_id'])) {
            $query->whereHas('user', fn($q) => $q->where('kelas_id', $this->filters['kelas_id']));
        }
         if (!empty($this->filters['status_presensi'])) {
            $query->where('status', $this->filters['status_presensi']);
        }

        // Urutkan data
        $query->orderBy('tanggal', 'asc')->orderBy('user_id', 'asc')->orderBy('jam_masuk', 'asc');

        return $query; // Kembalikan builder query, package akan menjalankannya
    }

     /**
     * @return array
     */
    public function headings(): array
    {
        // Definisikan header kolom di file Excel
        return [
            'Tanggal',
            'Nama Pengguna',
            'Role',
            'Kelas',
            'Jam Masuk',
            'Status Presensi',
            'Lokasi Valid?',
            'Koordinat (Lat,Lon)',
            'Keterangan',
            // 'URL Selfie', // Jarang diperlukan di Excel, bisa ditambahkan jika mau
        ];
    }

    /**
     * Memetakan data dari setiap model Attendance ke array untuk baris Excel.
     * @param Attendance $attendance
     * @return array
     */
    public function map($attendance): array
    {
         // Format data per baris sesuai urutan headings
         $user = $attendance->user; // Akses relasi user yg sudah di-eager load
         $kelasName = ($user?->role === 'Siswa' && $user?->kelas) ? $user->kelas->nama_kelas : '-'; // Cek null safety
         $locationValid = is_null($attendance->is_location_valid) ? 'N/A' : ($attendance->is_location_valid ? 'Ya' : 'Tidak');
         $coordinates = ($attendance->latitude && $attendance->longitude) ? number_format($attendance->latitude, 5) . ', ' . number_format($attendance->longitude, 5) : '-';
         $jamMasuk = $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : '-';
         // $selfieUrl = $attendance->selfie_path && Storage::disk('public')->exists($attendance->selfie_path) ? Storage::url($attendance->selfie_path) : '';

        return [
            $attendance->tanggal->isoFormat('DD/MM/YYYY'), // Format tanggal Excel friendly
            $user->name ?? 'N/A',
            $user->role ?? 'N/A',
            $kelasName,
            $jamMasuk,
            $attendance->status,
            $locationValid,
            $coordinates,
            $attendance->keterangan ?? '', // Tambahkan keterangan
            // $selfieUrl,
        ];
    }

    /**
     * Menerapkan style ke sheet Excel.
     */
     public function styles(Worksheet $sheet)
    {
        return [
            // Membuat baris pertama (header) menjadi tebal (bold)
            1    => ['font' => ['bold' => true]],
        ];
    }
}