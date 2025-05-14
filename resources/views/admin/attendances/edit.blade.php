{{-- Contoh di resources/views/admin/attendances/edit.blade.php --}}

@extends('layouts.admin') {{-- Sesuaikan layout Anda --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Data Presensi</h1>
                <a href="{{ route('admin.attendances.index') }}" class="text-sm text-indigo-600 hover:underline">
                    Kembali ke Daftar Presensi
                </a>
            </div>

            {{-- Pesan opsional: Hanya tampil jika user berhak (Super Admin ATAU petugas piket hari ini) --}}
            {{-- Menggunakan @can langsung karena Gate manageTodayAttendanceAdmin sudah menyertakan cek Super Admin --}}
            @can('manageTodayAttendanceAdmin')
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">
                    Anda sedang mengedit data presensi. Perubahan akan tersimpan ke database.
                    {{-- Pesan tambahan jika ini adalah guru piket hari ini (bukan Super Admin) --}}
                    @if (!Auth::user()->isSuperAdmin())
                        <br>Sebagai petugas piket hari ini, Anda hanya bisa mengedit data untuk tanggal hari ini.
                    @endif
                </div>
            @else
                {{-- Ini seharusnya tidak tercapai karena controller sudah dilindungi oleh $this->authorize --}}
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                    Anda tidak memiliki izin untuk mengakses halaman ini.
                </div>
            @endcan

            {{-- Tampilkan Error Validasi (pastikan ini sudah ada) --}}
            @if ($errors->any())
                {{-- ... kode menampilkan error ... --}}
            @endif
            @if (session('error'))
                {{-- ... kode menampilkan session error ... --}}
            @endif


            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <form method="POST" action="{{ route('admin.attendances.update', $attendance) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Tampilkan User dan Tanggal (Baca Saja) --}}
                    {{-- Pastikan ini menggunakan data dari $attendance --}}
                    <div>
                        <label class="form-label">User:</label>
                        <p class="form-input-static">{{ $attendance->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="form-label">Tanggal:</label>
                        <p class="form-input-static">{{ $attendance->tanggal->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>


                    {{-- Field yang bisa diedit --}}
                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required
                            class="form-select @error('status') border-red-500 @enderror">
                            <option value="Hadir" {{ old('status', $attendance->status) == 'Hadir' ? 'selected' : '' }}>
                                Hadir</option>
                            <option value="Telat" {{ old('status', $attendance->status) == 'Telat' ? 'selected' : '' }}>
                                Telat</option>
                            <option value="Izin" {{ old('status', $attendance->status) == 'Izin' ? 'selected' : '' }}>Izin
                            </option>
                            <option value="Sakit" {{ old('status', $attendance->status) == 'Sakit' ? 'selected' : '' }}>
                                Sakit</option>
                            <option value="Absen" {{ old('status', $attendance->status) == 'Absen' ? 'selected' : '' }}>
                                Absen</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ... kode input jam_masuk dan keterangan ... --}}
                    {{-- Pastikan input jam_masuk namanya 'jam_masuk' dan input keterangan namanya 'keterangan' --}}
                    <div>
                        <label for="jam_masuk" class="form-label">Jam Masuk (HH:MM)</label>
                        <input type="time" id="jam_masuk" name="jam_masuk"
                            value="{{ old('jam_masuk', $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '') }}"
                            class="form-input @error('jam_masuk') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika status bukan Hadir/Telat.</p>
                        @error('jam_masuk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea id="keterangan" name="keterangan" rows="3"
                            class="form-input @error('keterangan') border-red-500 @enderror"
                            placeholder="Tambahkan keterangan jika status Izin/Sakit/Absen">{{ old('keterangan', $attendance->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.attendances.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Asumsikan style form-label, form-input, btn-primary, btn-secondary sudah global atau di sini --}}
    {{-- ... kode style ... --}}
@endsection
