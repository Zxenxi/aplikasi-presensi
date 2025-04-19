<div class="md:hidden fixed bottom-0 inset-x-0 z-20">
    <nav class="relative bg-white border-t border-gray-200 shadow-lg bottom-nav-container">
        <div class="flex justify-around items-center h-full max-w-md mx-auto px-2" id="bottom-navigation">
            <a href="{{ route('admin.dashboard') }}"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="h-5 w-5 mb-1"></i>
                <span class="text-xs font-medium">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i data-lucide="users" class="h-5 w-5 mb-1"></i>
                <span class="text-xs font-medium">Pengguna</span>
            </a>

            <a href="{{ route('admin.classes.index') }}"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1 {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i data-lucide="building" class="h-5 w-5 mb-1"></i>
                <span class="text-xs font-medium">Kelas</span>
            </a>
            <a href="{{ route('reports.index') }}"
                class="bottom-nav-item flex flex-col items-center justify-center w-1/4 pt-1 {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3" class="h-5 w-5 mb-1"></i>
                <span class="text-xs font-medium">Kelas</span>
            </a>

        </div>
    </nav>
</div>
