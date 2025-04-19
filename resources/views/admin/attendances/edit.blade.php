@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Data Presensi: {{ $attendance->user->name ?? 'N/A' }}
                </h1>
                <a href="{{ route('attendances.index') }}" class="text-sm text-indigo-600 hover:underline">Kembali ke
                    Daftar</a>
            </div>

            {{-- Include Partial Alert --}}
            {{-- @include('partials.common._alert') --}}

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <form method="POST" action="{{ route('attendances.update', $attendance) }}" class="space-y-6"
                    x-data="{ selectedStatus: '{{ old('status', $attendance->status) }}' }">
                    @csrf
                    @method('PUT')

                    {{-- Info User (Readonly) --}}
                    <div>
                        <label class="form-label">Pengguna</label>
                        <p class="mt-1 text-sm text-gray-700 font-medium">{{ $attendance->user->name ?? 'N/A' }}
                            ({{ $attendance->user->role ?? 'N/A' }})</p>
                        {{-- Kirim user_id asli sebagai hidden input --}}
                        <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="form-label">Tanggal Presensi <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="tanggal" name="tanggal"
                            value="{{ old('tanggal', $attendance->tanggal->format('Y-m-d')) }}" required
                            class="form-input @error('tanggal') border-red-500 @enderror">
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" x-model="selectedStatus" required
                            class="form-select @error('status') border-red-500 @enderror">
                            <option value="Izin" {{ old('status', $attendance->status) == 'Izin' ? 'selected' : '' }}>Izin
                            </option>
                            <option value="Sakit" {{ old('status', $attendance->status) == 'Sakit' ? 'selected' : '' }}>
                                Sakit</option>
                            <option value="Hadir" {{ old('status', $attendance->status) == 'Hadir' ? 'selected' : '' }}>
                                Hadir (Manual)</option>
                            <option value="Telat" {{ old('status', $attendance->status) == 'Telat' ? 'selected' : '' }}>
                                Telat (Manual)</option>
                            <option value="Absen" {{ old('status', $attendance->status) == 'Absen' ? 'selected' : '' }}>
                                Absen</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Masuk (hanya jika Hadir/Telat) --}}
                    <div x-show="selectedStatus === 'Hadir' || selectedStatus === 'Telat'" x-transition>
                        <label for="jam_masuk" class="form-label">Jam Masuk <span class="text-red-500">*</span></label>
                        <input type="time" id="jam_masuk" name="jam_masuk"
                            value="{{ old('jam_masuk', $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '') }}"
                            class="form-input @error('jam_masuk') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Wajib diisi jika status Hadir atau Telat.</p>
                        @error('jam_masuk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea id="keterangan" name="keterangan" rows="3"
                            class="form-input @error('keterangan') border-red-500 @enderror"
                            placeholder="Contoh: Izin acara keluarga, Sakit demam, dll.">{{ old('keterangan', $attendance->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info Tambahan (Readonly dari data presensi asli) --}}
                    <div class="space-y-1 text-xs text-gray-500 border-t pt-4 mt-4">
                        <p>Selfie:
                            @if ($attendance->selfie_path && Storage::disk('public')->exists($attendance->selfie_path))
                                <a href="{{ Storage::url($attendance->selfie_path) }}" target="_blank"
                                    class="text-indigo-600 hover:underline">(Lihat Foto)</a>
                            @else
                                Tidak Ada
                            @endif
                        </p>
                        <p>Lokasi:
                            {{ is_null($attendance->is_location_valid) ? 'N/A' : ($attendance->is_location_valid ? 'Valid' : 'Tidak Valid') }}
                        </p>
                        <p>Koordinat:
                            {{ $attendance->latitude ? number_format($attendance->latitude, 5) . ', ' . number_format($attendance->longitude, 5) : '-' }}
                        </p>
                    </div>


                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('attendances.index') }}" class="btn-secondary">Batal</a>
                        {{-- Tombol Simpan dengan state loading --}}
                        {{-- <button type="submit" x-data="{ submitting: false }" x-on:click="submitting = true" :disabled="submitting"
                            class="btn-primary">
                            <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">...</svg>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        </button> --}}
                        <button type="submit" class="btn-primary ...">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Include style jika perlu --}}
    {{-- @include('partials.common._styles') --}}
@endsection
