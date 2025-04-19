@extends('layouts.admin')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Judul dan Tombol Tambah --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manajemen Kelas</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data kelas di sekolah.</p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <button type="button" @click="openCreateClassModal()" {{-- Panggil fungsi Alpine --}}
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5 -ml-1"></i> Tambah Kelas
                </button>
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
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Oops! Ada kesalahan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- TODO: Tambahkan filter kelas nanti --}}

        {{-- Tabel Kelas --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 class-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col">Nama Kelas</th>
                            <th scope="col" class="text-center">Tingkat</th>
                            <th scope="col">Jurusan</th>
                            <th scope="col">Wali Kelas</th>
                            <th scope="col" class="text-center">Jumlah Siswa</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($kelas as $item)
                            <tr>
                                <td class="text-sm font-medium text-gray-900">{{ $item->nama_kelas }}</td>
                                <td class="text-sm text-gray-500 text-center">{{ $item->tingkat }}</td>
                                <td class="text-sm text-gray-500">{{ $item->jurusan ?? '-' }}</td>
                                <td class="text-sm text-gray-500">{{ $item->waliKelas->name ?? '-' }}</td>
                                <td class="text-sm text-gray-500 text-center">{{ $item->students->count() }}</td>
                                {{-- Hitung jumlah siswa --}}
                                <td class="text-center">
                                    <div class="flex justify-center items-center space-x-1">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{-- Tombol Edit: Pass data sebagai JSON ke fungsi Alpine --}}
                                            <button type="button" title="Edit Kelas" class="action-button"
                                                @click="openEditClassModal({
                                                id: {{ $item->id }},
                                                nama: '{{ addslashes($item->nama_kelas) }}',
                                                tingkat: {{ $item->tingkat }},
                                                jurusan: '{{ addslashes($item->jurusan ?? '') }}',
                                                waliKelasId: {{ $item->wali_kelas_id ?? 'null' }}
                                            })">
                                                <i data-lucide="edit-2"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.classes.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('PERINGATAN: Menghapus kelas ini tidak dapat dibatalkan (jika tidak ada siswa). Yakin?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Kelas"
                                                    class="action-button text-red-400 hover:text-red-600 hover:bg-red-50">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-500">
                                    Belum ada data kelas.
                                    @if (auth()->user()->isSuperAdmin())
                                        <button type="button" @click="openCreateClassModal()"
                                            class="text-indigo-600 hover:underline ml-2">Tambah Kelas Baru</button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Paginasi tidak dipakai karena kita get() all --}}
        </div>

        {{-- Include Modal --}}
        @include('admin.classes._modal')

    </div>

    {{-- Style untuk modal & form (jika belum ada di global) --}}
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

        [x-cloak] {
            display: none !important;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .modal-body {
            margin-bottom: 24px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }
    </style>
@endsection
