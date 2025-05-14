@extends('layouts.admin') {{-- Gunakan layout admin Anda --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                {{-- Judul dinamis berdasarkan data yang diedit --}}
                <h1 class="text-2xl font-semibold text-gray-800">
                    Edit Jadwal Piket: {{ $picketSchedule->user?->name ?? 'N/A' }}
                    ({{ $picketSchedule->duty_date->isoFormat('D MMM Y') }})
                </h1>
                <a href="{{ route('admin.picket-schedules.index', ['month' => $picketSchedule->duty_date->format('Y-m')]) }}"
                    {{-- Kembali ke bulan yg relevan --}} class="text-sm text-indigo-600 hover:underline">
                    Kembali ke Daftar Jadwal
                </a>
            </div>

            {{-- Tampilkan Error Validasi --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">
                    <strong class="font-bold">Oops! Ada kesalahan:</strong>
                    <ul class="mt-1 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">
                    {{ session('error') }}
                </div>
            @endif


            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                {{-- Form mengarah ke route update dengan method PUT --}}
                <form method="POST" action="{{ route('admin.picket-schedules.update', $picketSchedule) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT') {{-- Method spoofing untuk update --}}

                    {{-- Pilih Petugas --}}
                    <div>
                        <label for="user_id" class="form-label">Petugas Piket <span class="text-red-500">*</span></label>
                        <select name="user_id" id="user_id" required
                            class="form-select @error('user_id') border-red-500 @enderror">
                            <option value="">-- Pilih Petugas --</option>
                            @foreach ($potentialOfficers as $officer)
                                {{-- Pilih petugas yang sesuai dengan data lama atau input sebelumnya --}}
                                <option value="{{ $officer->id }}"
                                    {{ old('user_id', $picketSchedule->user_id) == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }} ({{ $officer->role }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Tugas --}}
                    <div>
                        <label for="duty_date" class="form-label">Tanggal Tugas <span class="text-red-500">*</span></label>
                        <input type="date" id="duty_date" name="duty_date" {{-- Isi dengan tanggal dari data lama atau input sebelumnya --}}
                            value="{{ old('duty_date', $picketSchedule->duty_date->format('Y-m-d')) }}" required
                            class="form-input @error('duty_date') border-red-500 @enderror">
                        @error('duty_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea id="notes" name="notes" rows="4" class="form-input @error('notes') border-red-500 @enderror"
                            placeholder="Misal: Fokus pemeriksaan gerbang depan, Cek kelengkapan kelas, dll.">{{ old('notes', $picketSchedule->notes) }}</textarea> {{-- Isi dengan data lama --}}
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.picket-schedules.index', ['month' => $picketSchedule->duty_date->format('Y-m')]) }}"
                            class="btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Style (jika belum global) --}}
    <style>
        /* ... (copy style dari create.blade.php) ... */
    </style>
@endsection
