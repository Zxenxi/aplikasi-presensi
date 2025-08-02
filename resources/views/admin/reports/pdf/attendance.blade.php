
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 80px; /* Sesuaikan ukuran logo */
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .selfie-img {
            max-width: 70px;
            max-height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
        .text-center {
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        .signature-section p {
            margin: 0;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        {{-- Ganti dengan path logo sekolah Anda --}}
        {{-- <img src="{{ public_path('images/logo_sekolah.png') }}" alt="Logo Sekolah"> --}}
        <h1>SMK KRISTEN PENABUR</h1>
        <p>Laporan Kehadiran</p>
        <p>Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_selesai }}</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Status</th>
                    <th>Lokasi Valid</th>
                    <th>Keterangan</th>
                    <th class="text-center">Foto Selfie</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $index => $attendance)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->tanggal)->isoFormat('DD/MM/YY') }}</td>
                        <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                        <td>{{ $attendance->user->role ?? 'N/A' }}</td>
                        <td>{{ $attendance->user->role === 'Siswa' && $attendance->user->kelas ? $attendance->user->kelas->nama_kelas : '-' }}</td>
                        <td>{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->status }}</td>
                        <td class="text-center">{{ is_null($attendance->is_location_valid) ? 'N/A' : ($attendance->is_location_valid ? 'Ya' : 'Tidak') }}</td>
                        <td>{{ $attendance->keterangan ?? '' }}</td>
                        <td class="text-center">
                            @if ($attendance->selfie_path && file_exists(storage_path('app/public/' . $attendance->selfie_path)))
                                <img src="{{ storage_path('app/public/' . $attendance->selfie_path) }}" alt="Selfie" class="selfie-img">
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data untuk periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <p>Mengetahui,</p>
        <div class="signature-space"></div>
        <p>_________________________</p>
        <p>Kepala Sekolah</p>
    </div>

</body>
</html>
