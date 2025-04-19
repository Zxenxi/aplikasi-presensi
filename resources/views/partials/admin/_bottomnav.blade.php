<div class="md:hidden fixed bottom-0 inset-x-0 z-20">
    <nav class="relative bg-white border-t border-gray-200 shadow-lg bottom-nav-container">
        <div class="flex justify-around items-center h-full max-w-md mx-auto px-2" id="bottom-navigation">
            <a href="#" @click.prevent="changeTab('dashboard')"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1"
                :class="{ 'active': activeTab === 'dashboard' }"
                :aria-current="activeTab === 'dashboard' ? 'page' : undefined"><i data-lucide="layout-dashboard"
                    class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Dashboard</span></a>
            <a href="#" @click.prevent="changeTab('pengguna')"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1"
                :class="{ 'active': activeTab === 'pengguna' }"
                :aria-current="activeTab === 'pengguna' ? 'page' : undefined"><i data-lucide="users"
                    class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Pengguna</span></a>
            <a href="#" @click.prevent="changeTab('kelas')"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1"
                :class="{ 'active': activeTab === 'kelas' }"
                :aria-current="activeTab === 'kelas' ? 'page' : undefined"><i data-lucide="building"
                    class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Kelas</span></a>
            <a href="#" @click.prevent="changeTab('laporan')"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1"
                :class="{ 'active': activeTab === 'laporan' }"
                :aria-current="activeTab === 'laporan' ? 'page' : undefined"><i data-lucide="bar-chart-3"
                    class="h-5 w-5 mb-1"></i><span class="text-xs font-medium">Laporan</span></a>
        </div>
    </nav>
</div>
