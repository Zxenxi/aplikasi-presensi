@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Pengguna: {{ $user->name }}</h1>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:underline">Kembali ke
                    Daftar</a>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                {{-- Arahkan form ke route update dengan method PUT --}}
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT') {{-- Method Spoofing untuk PUT --}}

                    {{-- Nama --}}
                    <div>
                        <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="form-input @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            required class="form-input @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password (Opsional) --}}
                    <div>
                        <label for="password" class="form-label">Password Baru (Opsional)</label>
                        <input type="password" id="password" name="password"
                            class="form-input @error('password') border-red-500 @enderror"
                            placeholder="Kosongkan jika tidak ingin diubah">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
                    </div>

                    {{-- Role & Kelas (dengan Alpine) --}}
                    <div x-data="{ selectedRole: '{{ old('role', $user->role) }}' }">
                        {{-- Role --}}
                        <div class="mb-4">
                            <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="role" x-model="selectedRole" required
                                class="form-select @error('role') border-red-500 @enderror">
                                {{-- Tandai opsi yang sesuai dengan role user saat ini --}}
                                <option value="Siswa" {{ old('role', $user->role) == 'Siswa' ? 'selected' : '' }}>Siswa
                                </option>
                                <option value="Guru" {{ old('role', $user->role) == 'Guru' ? 'selected' : '' }}>Guru
                                </option>
                                <option value="Petugas Piket"
                                    {{ old('role', $user->role) == 'Petugas Piket' ? 'selected' : '' }}>Petugas Piket
                                </option>
                                <option value="Super Admin"
                                    {{ old('role', $user->role) == 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kelas (hanya muncul jika role = Siswa) --}}
                        <div x-show="selectedRole === 'Siswa'" x-transition>
                            <label for="kelas_id" class="form-label">Kelas (Untuk Siswa) <span
                                    x-show="selectedRole === 'Siswa'" class="text-red-500">*</span></label>
                            <select name="kelas_id" id="kelas_id"
                                class="form-select @error('kelas_id') border-red-500 @enderror">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    {{-- Tandai opsi yang sesuai dengan kelas user saat ini --}}
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id', $user->kelas_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Tambahkan style jika perlu --}}
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
