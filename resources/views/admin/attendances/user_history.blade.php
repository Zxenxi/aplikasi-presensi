@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Riwayat Presensi untuk {{ $user->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar kehadiran yang tercatat untuk pengguna ini.</p>
            </div>
            <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Kembali ke Manajemen Presensi
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Selfie</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat</th>
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
                                    @php
                                        $status = $att->status;
                                        $statusBadge = '';

                                        if ($status === 'Hadir') {
                                            $statusBadge = 'bg-green-100 text-green-800';
                                        } elseif ($status === 'Telat') {
                                            $statusBadge = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($status === 'Izin') {
                                            $statusBadge = 'bg-blue-100 text-blue-800';
                                        } elseif ($status === 'Sakit') {
                                            $statusBadge = 'bg-purple-100 text-purple-800';
                                        } else {
                                            $statusBadge = 'bg-red-100 text-red-800';
                                        }
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadge }}">
                                        {{ $att->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    @if ($att->selfie_path && Storage::disk('public')->exists($att->selfie_path))
                                        <img src="{{ Storage::url($att->selfie_path) }}" alt="Selfie" class="w-10 h-10 object-cover rounded-full shadow inline-block cursor-pointer" loading="lazy" onclick="showModal('{{ Storage::url($att->selfie_path) }}')">
                                    @else
                                        <span class="text-gray-400 text-xs">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if (is_null($att->is_location_valid))
                                        <span class="status-badge badge-gray">N/A</span>
                                    @elseif($att->is_location_valid)
                                        <span class="status-badge badge-green">Valid</span>
                                    @else
                                        <span class="status-badge badge-red" title="Lokasi presensi di luar area sekolah">Tidak Valid</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    @if ($att->latitude && $att->longitude)
                                        {{ number_format($att->latitude, 5) }}, {{ number_format($att->longitude, 5) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada riwayat presensi untuk pengguna ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($attendances->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

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
@endsection

@push('scripts')
<script>
    const selfieModal = document.getElementById('selfieModal');
    const modalImage = document.getElementById('modalImage');

    function showModal(imageUrl) {
        modalImage.src = imageUrl;
        selfieModal.classList.remove('hidden');
        selfieModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function hideModal() {
        selfieModal.classList.add('hidden');
        selfieModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideModal();
        }
    });
</script>
@endpush
