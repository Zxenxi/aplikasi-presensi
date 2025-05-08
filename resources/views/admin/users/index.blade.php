@extends('layouts.admin') {{-- Menggunakan layout admin --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data siswa, guru, dan petugas piket.</p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{-- Gunakan class btn jika ada --}}
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Pengguna
                </a>
            @endif
        </div>

        {{-- Pesan Sukses/Error --}}
        {{-- @include('partials.common._alert') Atau tampilkan manual --}}

        {{-- Form Filter & Search --}}
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
            {{-- Gunakan method GET agar filter muncul di URL --}}
            <form method="GET" action="{{ route('admin.users.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
                {{-- Search Input --}}
                <div class="sm:col-span-2 md:col-span-2 lg:col-span-2">
                    <label for="search" class="form-label">Cari Nama/Email</label>
                    <input type="text" name="search" id="search" value="{{ $search ?? '' }}" class="form-input"
                        placeholder="Masukkan nama atau email...">
                </div>

                {{-- Filter Role (dengan Alpine untuk kontrol filter kelas) --}}
                <div class="col-span-1" x-data="{ selectedRole: '{{ $filterRole ?? '' }}' }">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" x-model="selectedRole" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Petugas Piket">Petugas Piket</option>
                        <option value="Guru">Guru</option>
                        <option value="Siswa">Siswa</option>
                    </select>
                </div>

                {{-- Filter Kelas (hanya tampil jika role = Siswa) --}}
                <div class="col-span-1" x-show="selectedRole === 'Siswa'" x-transition>
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select name="kelas_id" id="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ ($filterKelasId ?? '') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Aksi Filter --}}
                <div class="col-span-1 flex space-x-2 justify-end lg:justify-start">
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="search" class="w-4 h-4 mr-1.5 -ml-1 mx-auto"></i> Cari
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset
                    </a>
                </div>
            </form>
        </div>


        {{-- Tabel Pengguna --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 user-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                            </th> {{-- Ubah padding --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kelas/Info</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                {{-- Kolom Nama & Avatar --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <img class="h-10 w-10 rounded-full object-cover flex-shrink-0 ring-1 ring-gray-200"
                                            {{-- Besarkan avatar sedikit --}}
                                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff&size=128"
                                            alt="{{ $user->name }} avatar" />
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                            {{-- Tampilkan email kecil di bawah nama jika perlu --}}
                                            {{-- <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p> --}}
                                        </div>
                                    </div>
                                </td>
                                {{-- Kolom Email --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                {{-- Kolom Role --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        // Definisikan $roleBadge menggunakan if/elseif/else
                                        $role = $user->role; // Ambil role
                                        $roleBadge = ''; // Inisialisasi

                                        if ($role === 'Super Admin') {
                                            $roleBadge = 'badge-red'; // Sesuaikan warna badge jika perlu
                                        } elseif ($role === 'Petugas Piket') {
                                            $roleBadge = 'badge-purple';
                                        } elseif ($role === 'Guru') {
                                            $roleBadge = 'badge-cyan';
                                        } elseif ($role === 'Siswa') {
                                            $roleBadge = 'badge-indigo';
                                        } else {
                                            // Default
                                            $roleBadge = 'badge-gray';
                                        }
                                    @endphp
                                    {{-- Gunakan $roleBadge di sini --}}
                                    <span class="status-badge {{ $roleBadge }}">{{ $user->role }}</span>
                                </td>
                                {{-- Kolom Kelas/Info --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->isSiswa() ? $user->kelas->nama_kelas ?? 'Belum ada kelas' : '-' }}
                                </td>
                                {{-- Kolom Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        {{-- Tombol View Detail (jika ada) --}}
                                        {{-- <a href="{{ route('admin.users.show', $user) }}" title="Lihat Detail" class="action-button"><i data-lucide="eye"></i></a> --}}
                                        @if (auth()->user()->isSuperAdmin())
                                            <a href="{{ route('admin.users.edit', $user) }}" title="Edit"
                                                class="action-button"><i data-lucide="edit-2"></i></a>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="..." class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Hapus"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50"><i
                                                        data-lucide="trash-2"></i></button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">Tidak ada data pengguna
                                    ditemukan.</td>
                            </tr> {{-- Sesuaikan colspan --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Link Paginasi (dengan filter) --}}
            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{-- appends(request()->query()) akan menambahkan semua parameter GET saat ini ke link paginasi --}}
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Style --}}
    <style>
        /* ... (class form-label, form-input, form-select) ... */
        /* ... (class status-badge & warnanya) ... */
        /* ... (class action-button) ... */
        /* ... (class btn-primary, btn-secondary) ... */
        /* ... (class alert) ... */
    </style>
@endsection
