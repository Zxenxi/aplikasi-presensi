@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6" x-data="classPromotion"> {{-- Saya tetap menggunakan classPromotion sesuai saran terakhir --}}

        {{-- 1. JUDUL HALAMAN --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Kenaikan Kelas Siswa</h1>
                <p class="text-sm text-gray-500 mt-1">Pindahkan siswa dari satu kelas ke kelas lainnya.</p>
            </div>
            <a href="{{ route('admin.classes.index') }}" class="text-sm text-indigo-600 hover:underline">
                Kembali ke Manajemen Kelas
            </a>
        </div>

        {{-- 2. PESAN SUKSES/ERROR --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if (session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('warning') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Oops! Ada kesalahan:</strong>
                <ul class="mt-1 list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 3. FORM UTAMA (Termasuk Dropdown Kelas Asal & Tujuan) --}}
        <form method="POST" action="{{ route('admin.classes.processPromotion') }}">
            @csrf
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="kelas_asal_id" class="form-label">Dari Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas_asal_id" id="kelas_asal_id" x-model="selectedKelasAsalId" @change="fetchSiswa()"
                            required class="form-select">
                            <option value="">-- Pilih Kelas Asal --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }} ({{ $k->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="kelas_tujuan_id" class="form-label">Ke Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas_tujuan_id" id="kelas_tujuan_id" x-model="selectedKelasTujuanId" required
                            class="form-select">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" :disabled="selectedKelasAsalId == {{ $k->id }}">
                                    {{ $k->nama_kelas }} ({{ $k->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 4. DAFTAR SISWA (AKAN MUNCUL DI BAWAH DROPDOWN SETELAH KELAS ASAL DIPILIH) --}}
                <div x-show="selectedKelasAsalId">
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Daftar Siswa di Kelas Asal (<span
                            x-text="namaKelasAsal"></span>)</h3>
                    <div x-show="loadingSiswa" class="text-center py-4">
                        {{-- SVG Loading --}}
                        <svg class="animate-spin h-5 w-5 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-sm text-gray-500 mt-1">Memuat data siswa...</p>
                    </div>

                    {{-- Tabel Siswa --}}
                    <div x-show="!loadingSiswa && siswaList.length > 0"
                        class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 w-10 text-center">
                                        <input type="checkbox" x-model="selectAllSiswa" @change="toggleSelectAll()"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    </th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Siswa</th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="siswa in siswaList" :key="siswa.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 text-center">
                                            <input type="checkbox" name="siswa_ids[]" :value="siswa.id"
                                                x-model="selectedSiswaIds"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </td>
                                        <td class="p-3 whitespace-nowrap text-sm text-gray-900" x-text="siswa.name"></td>
                                        <td class="p-3 whitespace-nowrap text-sm text-gray-500" x-text="siswa.email"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="!loadingSiswa && siswaList.length === 0 && selectedKelasAsalId"
                        class="text-center py-6 text-gray-500">
                        Tidak ada siswa aktif yang ditemukan di kelas <span x-text="namaKelasAsal"></span>.
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-4">
                        Catatan: Hanya siswa dengan status "Aktif" yang akan ditampilkan dan diproses untuk kenaikan kelas.
                        Pastikan kelas tujuan sudah benar sebelum melanjutkan.
                    </p>
                    <div class="flex justify-end">
                        <button type="submit"
                            :disabled="!selectedKelasAsalId || !selectedKelasTujuanId || selectedSiswaIds.length === 0 ||
                                loadingSiswa"
                            class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                            Proses Kenaikan Kelas
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('classPromotion', () => ({ // Pastikan nama ini ('classPromotion') sesuai dengan x-data
                selectedKelasAsalId: '',
                selectedKelasTujuanId: '',
                namaKelasAsal: '',
                siswaList: [],
                selectedSiswaIds: [],
                selectAllSiswa: false,
                loadingSiswa: false,
                allKelas: @json($kelas->map->only(['id', 'nama_kelas', 'tingkat'])),

                async fetchSiswa() {
                    if (!this.selectedKelasAsalId) {
                        this.siswaList = [];
                        this.selectedSiswaIds = [];
                        this.namaKelasAsal = '';
                        this.selectAllSiswa = false;
                        return;
                    }

                    const kelasAsalDetail = this.allKelas.find(k => k.id == this
                        .selectedKelasAsalId);
                    this.namaKelasAsal = kelasAsalDetail ?
                        `${kelasAsalDetail.nama_kelas} (${kelasAsalDetail.tingkat})` : '';
                    this.loadingSiswa = true;
                    this.siswaList = [];
                    this.selectedSiswaIds = [];
                    this.selectAllSiswa = false;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content');

                    try {
                        const response = await fetch(
                            `/api/admin/kelas/${this.selectedKelasAsalId}/students`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    ...(csrfToken && {
                                        'X-CSRF-TOKEN': csrfToken
                                    })
                                }
                            });

                        if (!response.ok) {
                            const errorText = await response.text();
                            let errorData;
                            try {
                                errorData = JSON.parse(errorText);
                            } catch (e) {
                                errorData = {
                                    message: `Server error: ${response.status} ${response.statusText}. Response: ${errorText}`
                                };
                            }
                            throw new Error(errorData.message ||
                                `Gagal mengambil data siswa. Status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.siswaList = data;
                    } catch (error) {
                        console.error('Error fetching students:', error);
                        // Anda bisa menambahkan notifikasi error yang lebih user-friendly di sini jika mau
                        // alert('Terjadi kesalahan saat mengambil data siswa: ' + error.message);
                    } finally {
                        this.loadingSiswa = false;
                    }
                },

                toggleSelectAll() {
                    if (this.selectAllSiswa) {
                        this.selectedSiswaIds = this.siswaList.map(siswa => siswa.id.toString());
                    } else {
                        this.selectedSiswaIds = [];
                    }
                },

                init() { // Menggunakan init() standar
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                }
            }));
        });
    </script>
@endpush
