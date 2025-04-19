<nav class="flex space-x-2" id="top-navigation">
    {{-- <a href="#" @click.prevent="changeTab('dashboard')"
        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm"
        :class="{ 'active': activeTab === 'dashboard' }" :aria-current="activeTab === 'dashboard' ? 'page' : undefined"><i
            data-lucide="layout-dashboard" class="w-4 h-4"></i><span>Dashboard</span></a> --}}
    <a href="{{ route('admin.dashboard') }}"
        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        :class="{ 'active': activeTab === 'dashboard' }" {{-- Sesuaikan dengan Alpine jika perlu --}} @click.prevent="changeTab('dashboard')"
        {{-- Hapus jika navigasi pakai reload halaman penuh --}}>
        <i data-lucide="layout-dashboard" class="w-4 h-4"></i><span>Dashboard</span>
    </a>
    <a href="#" @click.prevent="changeTab('pengguna')"
        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm"
        :class="{ 'active': activeTab === 'pengguna' }" :aria-current="activeTab === 'pengguna' ? 'page' : undefined"><i
            data-lucide="users" class="w-4 h-4"></i><span>Pengguna</span></a>
    <a href="#" @click.prevent="changeTab('kelas')"
        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm"
        :class="{ 'active': activeTab === 'kelas' }" :aria-current="activeTab === 'kelas' ? 'page' : undefined"><i
            data-lucide="building" class="w-4 h-4"></i><span>Kelas</span></a>
    <a href="#" @click.prevent="changeTab('laporan')"
        class="top-nav-item flex items-center space-x-2 px-3 py-2 rounded-md text-sm"
        :class="{ 'active': activeTab === 'laporan' }" :aria-current="activeTab === 'laporan' ? 'page' : undefined"><i
            data-lucide="bar-chart-3" class="w-4 h-4"></i><span>Laporan</span></a>

</nav>
