<header class="bg-white shadow-sm sticky top-0 z-30 border-b border-gray-200" @click.away="isUserMenuOpen = false">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex justify-between items-center h-16">
            <div class="flex-shrink-0 flex items-center">
                <svg class="h-8 w-auto text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="ml-2 text-xl font-bold text-gray-800 hidden sm:inline">Penabur Presensi</span>
                <span class="ml-2 text-lg font-bold text-gray-800 sm:hidden">Presensi</span>
            </div>
            {{-- dekstop View --}}
            <div class="hidden md:flex md:absolute md:inset-y-0 md:left-1/2 md:transform md:-translate-x-1/2">
                @auth
                    @if (Auth::user()->isSuperAdmin() || Auth::user()->isPetugasPiket())
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="font-medium">Pengguna</span>
                        </a>

                        <a href="{{ route('admin.classes.index') }}"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.classes.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="building" class="w-5 h-5"></i>
                            <span class="font-medium">Kelas</span>
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            <span class="font-medium">Laporan</span>
                        </a>
                        <a href="{{ route('admin.attendances.index') }}" title="Presensi Manual"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.attendances*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="calendar-check" class="h-5 w-5"></i>
                            <span class="font-medium">Presensi</span>
                        </a>
                        {{-- Link Pengaturan hanya untuk Super Admin --}}
                    @elseif(Auth::user()->isGuru() || Auth::user()->isSiswa())
                        {{-- === Menu untuk Guru & Siswa === --}}
                        <a href="{{ route('attendance.create') }}" title="Lakukan Presensi"
                            class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('attendance.create') ? 'active' : '' }}">
                            <i data-lucide="camera" class="w-5 h-5"></i><span>Presensi</span>
                        </a>
                        <a href="{{ route('attendance.history') }}" title="Riwayat Presensi Anda"
                            class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                            <i data-lucide="history" class="w-5 h-5"></i><span>Riwayat</span>
                        </a>
                    @endif
                @endauth
            </div>
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="flex items-center space-x-3">
                    <button type="button" title="Notifikasi"
                        class="relative text-gray-400 hover:text-gray-600 focus:outline-none p-1.5 rounded-full hover:bg-gray-100">
                        <span class="sr-only">Notifikasi</span><i data-lucide="bell" class="w-5 h-5"></i>
                        <span
                            class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-1 ring-white"></span>
                    </button>
                    <div class="relative">
                        <button @click="isUserMenuOpen = !isUserMenuOpen" type="button"
                            class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            id="user-menu-button" :aria-expanded="isUserMenuOpen.toString()" aria-haspopup="true">
                            <span class="sr-only">Buka menu user</span>
                            <img class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-300"
                                src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random&color=fff&size=128"
                                {{-- Avatar dinamis berdasarkan nama --}} alt="Avatar Pengguna"
                                onerror="this.onerror=null; this.src='https://placehold.co/100x100/cccccc/ffffff?text=Err';" />
                            {{-- Menampilkan nama user yang login --}}
                            <span class="hidden md:block ml-2 text-sm font-medium text-gray-900 truncate max-w-[150px]">
                                {{ Auth::user()->name }}
                            </span>
                            <i data-lucide="chevron-down" class="hidden md:block ml-1 h-4 w-4 text-gray-400"></i>
                        </button>
                        {{-- Dropdown Menu --}}
                        <div x-show="isUserMenuOpen" x-cloak x-transition
                            class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                            role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button"
                            tabindex="-1">
                            <a href="{{ route('profile.edit') }}" {{-- Link ke profil --}}
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                role="menuitem" tabindex="-1"><i data-lucide="user-circle"
                                    class="w-4 h-4 mr-2"></i>Profil Anda</a>

                            @if (Auth::user()->isSuperAdmin())
                                {{-- Hanya Super Admin --}}
                                <a href="{{ route('admin.settings.edit') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.settings.edit') ? 'active' : '' }}">
                                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i><span>Pengaturan</span>
                                </a>
                            @endif

                            {{-- Form Logout --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                    role="menuitem" tabindex="-1">
                                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>Keluar
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center sm:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" id="mobile-menu-button" type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                    aria-controls="mobile-menu" :aria-expanded="isMobileMenuOpen.toString()">
                    <span class="sr-only">Buka menu utama</span>
                    <i x-show="!isMobileMenuOpen" data-lucide="menu" class="block h-6 w-6" aria-hidden="true"></i>
                    <i x-show="isMobileMenuOpen" data-lucide="x" class="block h-6 w-6" x-cloak
                        aria-hidden="true"></i>
                </button>
            </div>
        </div>
        {{-- end dekstop view  --}}
    </div>
    {{-- mobile view  --}}
    <div x-show="isMobileMenuOpen" x-cloak class="sm:hidden border-t border-gray-200" id="mobile-menu" x-transition>
        <div class="px-2 pt-2 pb-3 space-y-1">
            @auth
                @if (Auth::user()->isSuperAdmin() || Auth::user()->isPetugasPiket())
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        <span>Pengguna</span>
                    </a>

                    <a href="{{ route('admin.classes.index') }}"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.classes.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="building" class="w-5 h-5"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span>Laporan</span>
                    </a>
                    <a href="{{ route('admin.attendances.index') }}" title="Presensi Manual"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.attendances.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="calendar-check" class="h-5 w-5"></i>
                        <span>Presensi</span>
                    </a>
                    {{-- Link Pengaturan hanya untuk Super Admin --}}
                    @if (Auth::user()->isSuperAdmin())
                        <a href="{{ route('admin.settings.edit') }}" title="Pengaturan Aplikasi"
                            class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.settings*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                            <span>Pengaturan</span>
                        </a>
                    @endif
                @elseif(Auth::user()->isGuru() || Auth::user()->isSiswa())
                    {{-- === Menu untuk Guru & Siswa === --}}
                    <a href="{{ route('attendance.create') }}" title="Lakukan Presensi"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('attendance.create*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="camera" class="w-5 h-5"></i><span>Presensi</span>
                    </a>
                    <a href="{{ route('attendance.history') }}" title="Riwayat Presensi Anda"
                        class="flex items-center space-x-2  px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('attendance.history*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="history" class="w-5 h-5"></i><span>Riwayat</span>
                    </a>
                @endif

            @endauth
            <div class="border-t border-gray-100 pt-3 mt-2">
                <a href="#"
                    class="flex items-center space-x-2 text-gray-600 hover:bg-gray-50 hover:text-gray-900  px-3 py-2 rounded-md text-base font-medium"><i
                        data-lucide="user-circle" class="w-5 h-5"></i><span>Profil Anda</span></a>
                {{-- Contoh di dalam dropdown user menu --}}
                {{-- Tombol logout mobile --}}
                <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-gray-200">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                        class="flex items-center px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100">
                        <i data-lucide="log-out" class="w-5 h-5 mr-2"></i>Keluar
                    </a>
                </form>

            </div>
        </div>
    </div>
</header>
