{{-- File: resources/views/partials/admin/_bottomnav.blade.php --}}
<div class="md:hidden fixed bottom-0 inset-x-0 z-20">
    <nav class="relative bg-white border-t border-gray-200 shadow-lg bottom-nav-container" style="height: 64px;">
        {{-- Pastikan style height ada --}}
        <div class="flex justify-around items-center h-full max-w-md mx-auto px-2" id="bottom-navigation">
            @auth
                @if (Auth::user()->isSuperAdmin() || Auth::user()->isPetugasPiket())
                    {{-- === Menu Mobile Admin & Piket (Pilih 4-5 utama) === --}}
                    <a href="{{ route('admin.dashboard') }}" title="Dashboard"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/5 pt-1 {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                        {{-- Bagi jadi 5 (w-1/5) --}}
                        <i data-lucide="layout-dashboard" class="h-5 w-5 mb-1"></i><span
                            class="text-xs font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" title="Pengguna"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/5 pt-1 {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i data-lucide="users" class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Pengguna</span>
                    </a>
                    <a href="{{ route('admin.classes.index') }}" title="Kelas"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/5 pt-1 {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
                        <i data-lucide="building" class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Kelas</span>
                    </a>
                    <a href="{{ route('admin.attendances.index') }}" title="Presensi Manual"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/5 pt-1 {{ request()->routeIs('admin.attendances*') ? 'active' : '' }}">
                        <i data-lucide="calendar-check" class="h-5 w-5 mb-1"></i><span
                            class="text-xs font-medium">Presensi</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" title="Laporan"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/5 pt-1 {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i data-lucide="bar-chart-3" class="h-5 w-5 mb-1"></i><span
                            class="text-xs font-medium">Laporan</span>
                    </a>
                    {{-- Pengaturan mungkin tidak muat di sini, bisa diakses dari menu user --}}
                @elseif(Auth::user()->isGuru() || Auth::user()->isSiswa())
                    {{-- === Menu Mobile Guru & Siswa === --}}
                    {{-- Mungkin cukup 2 menu utama --}}
                    <a href="{{ route('attendance.create') }}" title="Lakukan Presensi"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/2 pt-1 {{ request()->routeIs('attendance.create') ? 'active' : '' }}">
                        {{-- Bagi 2 --}}
                        <i data-lucide="camera" class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Presensi</span>
                    </a>
                    <a href="{{ route('attendance.history') }}" title="Riwayat Presensi"
                        class="bottom-nav-item flex flex-col items-center justify-center w-1/2 pt-1 {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                        <i data-lucide="history" class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Riwayat</span>
                    </a>
                @endif
            @endauth
        </div>
    </nav>
</div>

{{-- Style untuk navigasi bottom (jika belum global) --}}
<style>
    .bottom-nav-item {
        color: #6b7280;
        transition: color 0.2s ease-in-out;
    }

    .bottom-nav-item:hover:not(.active) {
        color: #4f46e5;
    }

    .bottom-nav-item.active {
        color: #4f46e5;
    }
</style>
