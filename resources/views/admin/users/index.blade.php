@extends('layouts.admin') {{-- Menggunakan layout admin --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul Halaman dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data siswa, guru, dan petugas piket.</p>
            </div>
            {{-- Tombol Tambah hanya untuk Super Admin --}}
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Pengguna
                </a>
            @endif
        </div>

        {{-- Pesan Sukses/Error --}}
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

        {{-- TODO: Tambahkan Filter & Search di sini nanti jika diperlukan --}}

        {{-- Tabel Pengguna --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 user-table">
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Hapus checkbox bulk action untuk sementara --}}
                            {{-- <th scope="col" class="text-center">...</th> --}}
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            {{-- <th scope="col">ID Pengguna</th> --}} {{-- Tambahkan jika ada kolom ID khusus --}}
                            <th scope="col">Role</th>
                            <th scope="col">Kelas/Info</th>
                            {{-- <th scope="col" class="text-center">Status</th> --}} {{-- Tambahkan jika ada status aktif/nonaktif --}}
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                {{-- Kolom Nama & Avatar --}}
                                <td>
                                    <div class="flex items-center space-x-3">
                                        {{-- Avatar Placeholder (bisa diganti jika ada field avatar) --}}
                                        <img class="user-avatar"
                                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff"
                                            alt="{{ $user->name }} avatar" />
                                        <div class="min-w-0 user-name-col">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                {{-- Kolom Email --}}
                                <td class="text-sm text-gray-500">{{ $user->email }}</td>
                                {{-- Kolom ID Pengguna (jika ada) --}}
                                {{-- <td class="text-sm text-gray-500">{{ $user->nomor_induk ?? '-' }}</td> --}}
                                {{-- Kolom Role (dengan badge) --}}
                                <td class="text-sm text-gray-500">
                                    @php
                                        $roleBadge = match ($user->role) {
                                            'Super Admin' => 'badge-red', // Sesuaikan warna badge
                                            'Petugas Piket' => 'badge-purple',
                                            'Guru' => 'badge-cyan',
                                            'Siswa' => 'badge-indigo',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $roleBadge }}">{{ $user->role }}</span>
                                </td>
                                {{-- Kolom Kelas/Info --}}
                                <td class="text-sm text-gray-500">
                                    {{ $user->isSiswa() ? $user->kelas->nama_kelas ?? 'Belum ada kelas' : '-' }}
                                </td>
                                {{-- Kolom Status (jika ada) --}}
                                {{-- <td class="text-center">...</td> --}}
                                {{-- Kolom Aksi --}}
                                <td class="text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        {{-- Tombol View Detail (jika ada halaman show) --}}
                                        {{-- <a href="{{ route('admin.users.show', $user) }}" title="Lihat Detail" class="action-button"><i data-lucide="eye"></i></a> --}}

                                        {{-- Hanya Super Admin yang bisa Edit & Hapus --}}
                                        @if (auth()->user()->isSuperAdmin())
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('admin.users.edit', $user) }}" title="Edit"
                                                class="action-button">
                                                <i data-lucide="edit-2"></i>
                                            </a>
                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('PERINGATAN: Menghapus user ini tidak dapat dibatalkan. Yakin ingin melanjutkan?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @else
                                            - {{-- Tampilkan strip jika bukan Super Admin --}}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- Tampilan jika tidak ada user --}}
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-500">
                                    Tidak ada data pengguna ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Link Paginasi --}}
            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
