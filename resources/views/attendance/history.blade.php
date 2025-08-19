@extends('layouts.admin') {{-- Gunakan layout admin --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Riwayat Presensi Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar kehadiran Anda yang tercatat.</p>
            </div>
            <a href="{{ route('attendance.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i data-lucide="camera" class="w-4 h-4 mr-1.5 -ml-1"></i> Lakukan Presensi Hari Ini
            </a>
        </div>

        {{-- Pesan Info/Warning/Sukses --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-4 p-4 bg-yellow-100 text-yellow-700 border border-yellow-400 rounded text-sm">
                {{ session('warning') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabel Riwayat --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam
                                Masuk</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Selfie</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Koordinat</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($attendances as $att)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $att->tanggal->isoFormat('dddd, D MMMM YYYY') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{-- Kode BARU dengan if/elseif/else (Kompatibel dengan PHP 7.x) --}}
                                    @php
                                        $status = $att->status; // Ambil status
                                        $statusBadge = ''; // Inisialisasi variabel

                                        if ($status === 'Hadir') {
                                            $statusBadge = 'bg-green-100 text-green-800';
                                        } elseif ($status === 'Telat') {
                                            $statusBadge = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($status === 'Izin') {
                                            $statusBadge = 'bg-blue-100 text-blue-800';
                                        } elseif ($status === 'Sakit') {
                                            $statusBadge = 'bg-purple-100 text-purple-800';
                                        } else {
                                            // Default (misal: Absen atau status lain)
                                            $statusBadge = 'bg-red-100 text-red-800';
                                        }
                                    @endphp
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadge }}">
                                        {{ $att->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    @if ($att->selfie_path && Storage::disk('public')->exists($att->selfie_path))
                                        <img src="{{ Storage::url($att->selfie_path) }}" alt="Selfie"
                                            class="w-10 h-10 object-cover rounded-full shadow inline-block cursor-pointer"
                                            loading="lazy"
                                            onclick="showModal('{{ Storage::url($att->selfie_path) }}')">
                                    @else
                                        <span class="text-gray-400 text-xs">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{-- Logika Tampilan Status Lokasi sama --}}
                                    @if (is_null($att->is_location_valid))
                                        <span class="status-badge badge-gray">N/A</span>
                                    @elseif($att->is_location_valid)
                                        <span class="status-badge badge-green">Valid</span>
                                    @else
                                        <span class="status-badge badge-red"
                                            title="Lokasi presensi di luar area sekolah">Tidak Valid</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{-- Tampilan Koordinat sama --}}
                                    @if ($att->latitude && $att->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                            {{ number_format($att->latitude, 5) }}, {{ number_format($att->longitude, 5) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada riwayat
                                    presensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Link Paginasi --}}
            @if ($attendances->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Tampilan Selfie --}}
    <div id="selfieModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-75 hidden" onclick="hideModal()">
        <div class="p-4 max-w-xl max-h-full mx-auto" onclick="event.stopPropagation()">
            <div class="relative">
                <img id="modalImage" src="" alt="Selfie" class="rounded-lg shadow-2xl w-full h-auto">
                <button onclick="hideModal()" class="absolute top-2 right-2 bg-black bg-opacity-50 text-white rounded-full p-1 hover:bg-opacity-75 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Style badge (jika belum global di admin.css) --}}
    <style>
        .status-badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-yellow {
            background-color: #fef9c3;
            color: #f59e0b;
        }

        .badge-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* ... (tambahkan warna badge lain jika perlu) ... */
    </style>

@push('scripts')
<script>
    const selfieModal = document.getElementById('selfieModal');
    const modalImage = document.getElementById('modalImage');

    function showModal(imageUrl) {
        modalImage.src = imageUrl;
        selfieModal.classList.remove('hidden');
        selfieModal.classList.add('flex');
        document.body.classList.add('overflow-hidden'); // Mencegah scroll di background
    }

    function hideModal() {
        selfieModal.classList.add('hidden');
        selfieModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    // Menutup modal saat tombol Escape ditekan
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideModal();
        }
    });
</script>
@endpush
@endsection
