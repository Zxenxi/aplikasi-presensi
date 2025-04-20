<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi</title>
    <style>
        body {
            font-family: 'sans-serif';
            /* Gunakan font dasar */
            font-size: 10px;
            /* Ukuran font kecil cocok untuk PDF tabel */
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .header-info strong {
            display: inline-block;
            min-width: 100px;
            /* Atur lebar label */
        }

        .page-break {
            /* Jika perlu paksa ganti halaman */
            page-break-after: always;
        }

        .badge {
            font-size: 0.7em;
            padding: 2px 5px;
            border-radius: 10px;
            text-transform: capitalize;
            white-space: nowrap;
            border: 1px solid #ccc;
            /* Beri border tipis */
        }

        .badge-green {
            background-color: #dcfce7;
            color: #166534;
            border-color: #a7f3d0;
        }

        .badge-yellow {
            background-color: #fef9c3;
            color: #854d0e;
            border-color: #fde68a;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .badge-purple {
            background-color: #ede9fe;
            color: #5b21b6;
            border-color: #ddd6fe;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #4b5563;
            border-color: #e5e7eb;
        }
    </style>
</head>

<body>
    <h1 style="text-align: center; margin-bottom: 20px;">Laporan Presensi</h1>

    {{-- Informasi Filter --}}
    <div class="header-info">
        <div><strong>Periode:</strong>
            {{ $filters['tanggal_mulai'] ? \Carbon\Carbon::parse($filters['tanggal_mulai'])->isoFormat('D MMM Y') : 'N/A' }}
            -
            {{ $filters['tanggal_selesai'] ? \Carbon\Carbon::parse($filters['tanggal_selesai'])->isoFormat('D MMM Y') : 'N/A' }}
        </div>
        @if (!empty($filters['tipe_user']))
            <div><strong>Tipe User:</strong> {{ $filters['tipe_user'] }}</div>
        @endif
        @if (!empty($filters['kelas_id']) && isset($kelas))
            {{-- Cari nama kelas --}}
            @php $namaKelas = $kelas->firstWhere('id', $filters['kelas_id'])->nama_kelas ?? 'N/A'; @endphp
            <div><strong>Kelas:</strong> {{ $namaKelas }}</div>
        @endif
        @if (!empty($filters['status_presensi']))
            <div><strong>Status:</strong> {{ $filters['status_presensi'] }}</div>
        @endif
        <div><strong>Tanggal Cetak:</strong> {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
        <div><strong>Total Data:</strong> {{ $results->count() }}</div>
    </div>

    {{-- Tabel Hasil --}}
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
            </tr>
        </thead>
        <tbody>
            @forelse($results as $index => $att)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $att->tanggal->isoFormat('DD/MM/YY') }}</td>
                    <td>{{ $att->user->name ?? 'N/A' }}</td>
                    <td>{{ $att->user->role ?? 'N/A' }}</td>
                    <td>{{ $att->user?->role === 'Siswa' ? $att->user?->kelas?->nama_kelas ?? '-' : '-' }}</td>
                    <td>{{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}</td>
                    <td>
                        @php
                            $statusBadgePdf = match ($att->status) {
                                'Hadir' => 'badge-green',
                                'Telat' => 'badge-yellow',
                                'Izin' => 'badge-blue',
                                'Sakit' => 'badge-purple',
                                default => 'badge-red',
                            };
                        @endphp
                        <span class="badge {{ $statusBadgePdf }}"> {{ $att->status }} </span>
                    </td>
                    <td>
                        @if (!is_null($att->latitude))
                            {{ is_null($att->is_location_valid) ? 'N/A' : ($att->is_location_valid ? 'Ya' : 'Tidak') }}
                        @else
                            Manual
                        @endif
                    </td>
                    <td>{{ $att->keterangan ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
