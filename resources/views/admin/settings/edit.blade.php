@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-2xl mx-auto"> {{-- Lebarkan sedikit untuk form ini --}}
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Pengaturan Aplikasi</h1>
                {{-- Tidak perlu tombol kembali jika menu navigasi sudah jelas --}}
            </div>

            {{-- Pesan Sukses --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Tampilkan Error Validasi Umum --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Oops! Ada kesalahan:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT') {{-- Gunakan method PUT untuk update --}}

                    <h2 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Pengaturan Lokasi & Radius</h2>

                    {{-- Latitude Sekolah --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="school_latitude" class="form-label">Latitude Sekolah <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="any" {{-- Izinkan desimal --}} id="school_latitude"
                                name="school_latitude" value="{{ old('school_latitude', $settings->school_latitude) }}"
                                required class="form-input @error('school_latitude') border-red-500 @enderror"
                                placeholder="Contoh: -6.200000">
                            @error('school_latitude')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Gunakan format desimal. Contoh: -6.200000 (untuk Jakarta).
                            </p>
                        </div>

                        {{-- Longitude Sekolah --}}
                        <div>
                            <label for="school_longitude" class="form-label">Longitude Sekolah <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="any" id="school_longitude" name="school_longitude"
                                value="{{ old('school_longitude', $settings->school_longitude) }}" required
                                class="form-input @error('school_longitude') border-red-500 @enderror"
                                placeholder="Contoh: 106.816666">
                            @error('school_longitude')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Contoh: 106.816666 (untuk Jakarta).</p>
                        </div>
                    </div>

                    {{-- Radius Toleransi --}}
                    <div>
                        <label for="allowed_radius_meters" class="form-label">Radius Area Presensi (meter) <span
                                class="text-red-500">*</span></label>
                        <input type="number" min="10" id="allowed_radius_meters" name="allowed_radius_meters"
                            value="{{ old('allowed_radius_meters', $settings->allowed_radius_meters) }}" required
                            class="form-input @error('allowed_radius_meters') border-red-500 @enderror"
                            placeholder="Contoh: 150">
                        @error('allowed_radius_meters')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Jarak maksimal dari titik lokasi sekolah agar presensi
                            dianggap valid.</p>
                    </div>

                    <h2 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4 pt-4">Pengaturan Waktu Presensi</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Jam Mulai Presensi --}}
                        <div>
                            <label for="attendance_start_time" class="form-label">Jam Mulai Presensi <span
                                    class="text-red-500">*</span></label>
                            <input type="time" id="attendance_start_time" name="attendance_start_time"
                                value="{{ old('attendance_start_time', $settings->attendance_start_time ? \Carbon\Carbon::parse($settings->attendance_start_time)->format('H:i') : '') }}"
                                required class="form-input @error('attendance_start_time') border-red-500 @enderror">
                            @error('attendance_start_time')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam Selesai Presensi --}}
                        <div>
                            <label for="attendance_end_time" class="form-label">Jam Selesai Presensi <span
                                    class="text-red-500">*</span></label>
                            <input type="time" id="attendance_end_time" name="attendance_end_time"
                                value="{{ old('attendance_end_time', $settings->attendance_end_time ? \Carbon\Carbon::parse($settings->attendance_end_time)->format('H:i') : '') }}"
                                required class="form-input @error('attendance_end_time') border-red-500 @enderror">
                            @error('attendance_end_time')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Batas Waktu Telat --}}
                        <div>
                            <label for="late_threshold_time" class="form-label">Batas Waktu Telat <span
                                    class="text-red-500">*</span></label>
                            <input type="time" id="late_threshold_time" name="late_threshold_time"
                                value="{{ old('late_threshold_time', $settings->late_threshold_time ? \Carbon\Carbon::parse($settings->late_threshold_time)->format('H:i') : '') }}"
                                required class="form-input @error('late_threshold_time') border-red-500 @enderror">
                            @error('late_threshold_time')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Gunakan format 24 jam (HH:MM). Contoh: 07:00, 16:30.</p>


                    {{-- Tombol Simpan --}}
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Style untuk form (jika belum global) --}}
    <style>
        .form-label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.3);
        }
    </style>
@endsection
