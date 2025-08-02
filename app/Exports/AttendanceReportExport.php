<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection; // Ganti FromQuery menjadi FromCollection
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings; // <-- Tambahkan ini
use Maatwebsite\Excel\Concerns\WithEvents; // <-- Tambahkan ini
use Maatwebsite\Excel\Events\AfterSheet; // <-- Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // <-- Tambahkan ini
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings, WithEvents
{
    protected $attendances;
    protected $imageRows = []; // Simpan baris mana saja yang punya gambar

    public function __construct(array $filters = [])
    {
        // Logika query yang sama dari controller kita pindahkan ke sini
        $query = Attendance::query()->with(['user.kelas']);

        if (!empty($filters['tanggal_mulai']) && !empty($filters['tanggal_selesai'])) {
            $query->whereBetween('tanggal', [$filters['tanggal_mulai'], $filters['tanggal_selesai']]);
        } else {
            $query->whereBetween('tanggal', [now()->subDays(6)->toDateString(), now()->toDateString()]);
        }

        if (!empty($filters['tipe_user'])) {
            $query->whereHas('user', fn($q) => $q->where('role', $filters['tipe_user']));
        }
        if (!empty($filters['tipe_user']) && $filters['tipe_user'] === 'Siswa' && !empty($filters['kelas_id'])) {
            $query->whereHas('user', fn($q) => $q->where('kelas_id', $filters['kelas_id']));
        }
        if (!empty($filters['status_presensi'])) {
            $query->where('status', $filters['status_presensi']);
        }

        $this->attendances = $query->orderBy('tanggal', 'asc')->orderBy('user_id', 'asc')->orderBy('jam_masuk', 'asc')->get();
    
        // Siapkan data baris mana yang ada gambarnya
        foreach ($this->attendances as $index => $attendance) {
            if ($attendance->selfie_path && Storage::disk('public')->exists($attendance->selfie_path)) {
                // +2 karena baris data mulai dari 2 (setelah header)
                $this->imageRows[] = $index + 2;
            }
        }
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
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
            'Foto Selfie', // <-- Tambah kolom selfie
        ];
    }

    public function map($attendance): array
    {
        $user = $attendance->user;
        $kelasName = ($user?->role === 'Siswa' && $user?->kelas) ? $user->kelas->nama_kelas : '-';
        $locationValid = is_null($attendance->is_location_valid) ? 'N/A' : ($attendance->is_location_valid ? 'Ya' : 'Tidak');
        $coordinates = ($attendance->latitude && $attendance->longitude) ? number_format($attendance->latitude, 5) . ', ' . number_format($attendance->longitude, 5) : '-';
        $jamMasuk = $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : '-';

        return [
            $attendance->tanggal->isoFormat('DD/MM/YYYY'),
            $user->name ?? 'N/A',
            $user->role ?? 'N/A',
            $kelasName,
            $jamMasuk,
            $attendance->status,
            $locationValid,
            $coordinates,
            $attendance->keterangan ?? '',
            '', // Kolom selfie dikosongkan, akan diisi oleh drawing
        ];
    }

    public function drawings()
    {
        $drawings = [];
        foreach ($this->attendances as $index => $attendance) {
            if ($attendance->selfie_path && Storage::disk('public')->exists($attendance->selfie_path)) {
                $drawing = new Drawing();
                $drawing->setName('Selfie');
                $drawing->setDescription('Selfie Pengguna');
                $drawing->setPath(storage_path('app/public/' . $attendance->selfie_path));
                $drawing->setHeight(60); // Tinggi gambar dalam pixel
                $drawing->setCoordinates('J' . ($index + 2)); // Kolom J, baris ke-($index + 2)

                $drawings[] = $drawing;
            }
        }
        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Atur tinggi baris untuk semua baris yang memiliki gambar
                foreach ($this->imageRows as $row) {
                    $event->sheet->getDelegate()->getRowDimension($row)->setRowHeight(50); // Atur tinggi baris (dalam points)
                }
                 // Mengatur alignment vertikal untuk seluruh sheet agar di tengah
                 $event->sheet->getDelegate()->getStyle(
                    'A1:' . $event->sheet->getDelegate()->getHighestColumn() . $event->sheet->getDelegate()->getHighestRow()
                )->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}