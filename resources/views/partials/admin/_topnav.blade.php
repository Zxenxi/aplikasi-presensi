{{-- File: resources/views/partials/admin/_topnav.blade.php --}}
<nav class="hidden md:flex md:absolute md:inset-y-0 md:left-1/2 md:transform md:-translate-x-1/2" id="top-navigation">
    <div class="flex space-x-1"> {{-- Kurangi space jika perlu --}}
        @auth
            @if (Auth::user()->isSuperAdmin() || Auth::user()->isPetugasPiket())
                {{-- === Menu untuk Admin & Petugas Piket === --}}
                <a href="{{ route('admin.dashboard') }}" title="Dashboard Admin"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" title="Manajemen Pengguna"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i><span>Pengguna</span>
                </a>
                <a href="{{ route('admin.classes.index') }}" title="Manajemen Kelas"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
                    <i data-lucide="building" class="w-4 h-4"></i><span>Kelas</span>
                </a>
                <a href="{{ route('admin.attendances.index') }}" title="Manajemen Presensi Manual"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.attendances*') ? 'active' : '' }}">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i><span>Presensi Manual</span> {{-- Ganti ikon jika perlu --}}
                </a>
                <a href="{{ route('admin.reports.index') }}" title="Laporan Presensi"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i><span>Laporan</span>
                </a>
                {{-- Link Pengaturan hanya untuk Super Admin --}}
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.settings.edit') }}" title="Pengaturan Aplikasi"
                        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <i data-lucide="settings" class="w-4 h-4"></i><span>Pengaturan</span>
                    </a>
                @endif
            @elseif(Auth::user()->isGuru() || Auth::user()->isSiswa())
                {{-- === Menu untuk Guru & Siswa === --}}
                <a href="{{ route('attendance.create') }}" title="Lakukan Presensi"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('attendance.create') ? 'active' : '' }}">
                    <i data-lucide="camera" class="w-4 h-4"></i><span>Presensi</span>
                </a>
                <a href="{{ route('attendance.history') }}" title="Riwayat Presensi Anda"
                    class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                    <i data-lucide="history" class="w-4 h-4"></i><span>Riwayat</span>
                </a>
            @endif
        @endauth
    </div>
</nav>

{{-- Style untuk navigasi top (jika belum global) --}}
<style>
    .top-nav-item {
        color: #6b7280;
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }

    .top-nav-item:hover:not(.active) {
        background-color: #f3f4f6;
        color: #1f2937;
    }

    .top-nav-item.active {
        background-color: #eef2ff;
        color: #4f46e5;
        font-weight: 600;
    }
</style>
