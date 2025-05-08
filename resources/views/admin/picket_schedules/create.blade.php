@extends('layouts.admin') {{-- Gunakan layout admin Anda --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto"> {{-- Batasi lebar form --}}
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Tambah Jadwal Piket Baru</h1>
                <a href="{{ route('admin.picket-schedules.index') }}" class="text-sm text-indigo-600 hover:underline">Kembali
                    ke Daftar Jadwal</a>
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
                <form method="POST" action="{{ route('admin.picket-schedules.store') }}" class="space-y-6">
                    @csrf

                    {{-- Pilih Petugas --}}
                    <div>
                        <label for="user_id" class="form-label">Petugas Piket <span class="text-red-500">*</span></label>
                        <select name="user_id" id="user_id" required
                            class="form-select @error('user_id') border-red-500 @enderror">
                            <option value="">-- Pilih Petugas --</option>
                            {{-- Loop data $potentialOfficers dari controller --}}
                            @foreach ($potentialOfficers as $officer)
                                <option value="{{ $officer->id }}" {{ old('user_id') == $officer->id ? 'selected' : '' }}>
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
                        <input type="date" id="duty_date" name="duty_date" value="{{ old('duty_date', date('Y-m-d')) }}"
                            {{-- Default tanggal hari ini --}} min="{{ date('Y-m-d') }}" {{-- Minimal tanggal hari ini --}} required
                            class="form-input @error('duty_date') border-red-500 @enderror">
                        @error('duty_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea id="notes" name="notes" rows="4" class="form-input @error('notes') border-red-500 @enderror"
                            placeholder="Misal: Fokus pemeriksaan gerbang depan, Cek kelengkapan kelas, dll.">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.picket-schedules.index') }}" class="btn-secondary"> {{-- Gunakan class button Anda --}}
                            Batal
                        </a>
                        <button type="submit" class="btn-primary"> {{-- Gunakan class button Anda --}}
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Style (jika belum global) --}}
    <style>
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input,
        .form-select,
        textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .form-input:focus,
        .form-select:focus,
        textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.3);
        }

        .btn-primary {
            display: inline-flex;
            items-center;
            padding: 0.5rem 1rem;
            background-color: #4f46e5;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 0.75rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            /* ... sesuaikan ... */
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            display: inline-flex;
            items-center;
            padding: 0.5rem 1rem;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 0.75rem;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            /* ... sesuaikan ... */
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
        }
    </style>
@endsection
