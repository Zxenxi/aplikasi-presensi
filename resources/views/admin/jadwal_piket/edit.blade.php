@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Jadwal Piket</h1>
                <a href="{{ route('admin.picket_schedules.index') }}" class="text-sm text-indigo-600 hover:underline">
                    <i data-lucide="arrow-left" class="inline-block w-4 h-4 mr-1"></i>Kembali ke Daftar
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm" role="alert">
                    <strong class="font-bold">Oops! Ada kesalahan:</strong>
                    <ul class="mt-2 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <form method="POST" action="{{ route('admin.picket_schedules.update', $picket_schedule->id) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Pilih Guru --}}
                    <div>
                        <label for="user_id" class="form-label">Guru Piket <span class="text-red-500">*</span></label>
                        <select name="user_id" id="user_id" required
                            class="form-select @error('user_id') border-red-500 @enderror">
                            <option value="">-- Pilih Guru --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ old('user_id', $picket_schedule->user_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hari Ke --}}
                    <div>
                        <label for="hari_ke" class="form-label">Hari Piket <span class="text-red-500">*</span></label>
                        <select name="hari_ke" id="hari_ke" required
                            class="form-select @error('hari_ke') border-red-500 @enderror">
                            <option value="">-- Pilih Hari --</option>
                            <option value="1" {{ old('hari_ke', $picket_schedule->hari_ke) == '1' ? 'selected' : '' }}>
                                Senin</option>
                            <option value="2" {{ old('hari_ke', $picket_schedule->hari_ke) == '2' ? 'selected' : '' }}>
                                Selasa</option>
                            <option value="3" {{ old('hari_ke', $picket_schedule->hari_ke) == '3' ? 'selected' : '' }}>
                                Rabu</option>
                            <option value="4"
                                {{ old('hari_ke', $picket_schedule->hari_ke) == '4' ? 'selected' : '' }}>Kamis</option>
                            <option value="5"
                                {{ old('hari_ke', $picket_schedule->hari_ke) == '5' ? 'selected' : '' }}>Jumat</option>
                            <option value="6"
                                {{ old('hari_ke', $picket_schedule->hari_ke) == '6' ? 'selected' : '' }}>Sabtu</option>
                            <option value="7"
                                {{ old('hari_ke', $picket_schedule->hari_ke) == '7' ? 'selected' : '' }}>Minggu</option>
                        </select>
                        @error('hari_ke')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Mulai & Jam Selesai --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <label for="jam_mulai" class="form-label">Jam Mulai (Opsional)</label>
                            <input type="time" id="jam_mulai" name="jam_mulai"
                                value="{{ old('jam_mulai', $picket_schedule->jam_mulai ? \Carbon\Carbon::parse($picket_schedule->jam_mulai)->format('H:i') : '') }}"
                                class="form-input @error('jam_mulai') border-red-500 @enderror">
                            @error('jam_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jam_selesai" class="form-label">Jam Selesai (Opsional)</label>
                            <input type="time" id="jam_selesai" name="jam_selesai"
                                value="{{ old('jam_selesai', $picket_schedule->jam_selesai ? \Carbon\Carbon::parse($picket_schedule->jam_selesai)->format('H:i') : '') }}"
                                class="form-input @error('jam_selesai') border-red-500 @enderror">
                            @error('jam_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 -mt-4">Kosongkan jam jika piket berlaku seharian atau tidak ada waktu
                        spesifik.</p>

                    {{-- Keterangan Tugas --}}
                    <div>
                        <label for="keterangan_tugas" class="form-label">Keterangan Tugas (Opsional)</label>
                        <textarea id="keterangan_tugas" name="keterangan_tugas" rows="3"
                            class="form-input @error('keterangan_tugas') border-red-500 @enderror"
                            placeholder="Contoh: Mengawasi gerbang utama, penanganan siswa terlambat, dll.">{{ old('keterangan_tugas', $picket_schedule->keterangan_tugas) }}</textarea>
                        @error('keterangan_tugas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t mt-8">
                        <a href="{{ route('admin.picket_schedules.index') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i data-lucide="save" class="w-4 h-4 mr-1.5 -ml-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        /* Style bisa dipindah ke CSS global */
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input,
        .form-select {
            display: block;
            width: 100%;
            border-radius: 0.375rem;
            border-width: 1px;
            --tw-border-opacity: 1;
            border-color: rgba(209, 213, 219, var(--tw-border-opacity));
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
        }

        .form-input:focus,
        .form-select:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
            --tw-ring-inset: var(--tw-empty,
                    /*!*/
                    /*!*/
                );
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: #4f46e5;
            border-color: #4f46e5;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
