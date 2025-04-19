@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Tambah Data Presensi Manual</h1>
                <a href="{{ route('attendances.index') }}" class="text-sm text-indigo-600 hover:underline">Kembali ke
                    Daftar</a>
            </div>

            {{-- Include Partial Alert --}}
            {{-- @include('partials.common._alert') --}}

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                {{-- Alpine data untuk kontrol jam masuk --}}
                <form method="POST" action="{{ route('attendances.store') }}" class="space-y-6" x-data="{ selectedStatus: '{{ old('status', 'Izin') }}' }">
                    @csrf

                    {{-- Pilih User --}}
                    <div>
                        <label for="user_id" class="form-label">Pengguna (Guru/Siswa) <span
                                class="text-red-500">*</span></label>
                        <select name="user_id" id="user_id" required
                            class="form-select @error('user_id') border-red-500 @enderror">
                            <option value="">-- Pilih Pengguna --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="form-label">Tanggal Presensi <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                            required class="form-input @error('tanggal') border-red-500 @enderror">
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" x-model="selectedStatus" required
                            class="form-select @error('status') border-red-500 @enderror">
                            {{-- Urutkan berdasarkan yang paling umum --}}
                            <option value="Izin" {{ old('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Sakit" {{ old('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="Hadir" {{ old('status') == 'Hadir' ? 'selected' : '' }}>Hadir (Manual)</option>
                            <option value="Telat" {{ old('status') == 'Telat' ? 'selected' : '' }}>Telat (Manual)</option>
                            <option value="Absen" {{ old('status') == 'Absen' ? 'selected' : '' }}>Absen</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Masuk (hanya jika Hadir/Telat) --}}
                    <div x-show="selectedStatus === 'Hadir' || selectedStatus === 'Telat'" x-transition>
                        <label for="jam_masuk" class="form-label">Jam Masuk <span class="text-red-500">*</span></label>
                        <input type="time" id="jam_masuk" name="jam_masuk" value="{{ old('jam_masuk') }}"
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
                            placeholder="Contoh: Izin acara keluarga, Sakit demam, dll.">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('attendances.index') }}" class="btn-secondary">Batal</a>
                        {{-- Tombol Simpan dengan state loading --}}
                        {{-- <button type="submit" x-data="{ submitting: false }" x-on:click="submitting = true" :disabled="submitting"
                            class="btn-primary">
                            <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Data'"></span>
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
