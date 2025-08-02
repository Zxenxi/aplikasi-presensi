<!DOCTYPE html>
<html lang="id" x-data="presensiApp" x-init="init()">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Penabur Presensi') }} - Admin</title> {{-- Judul Dinamis --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Load CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-900 antialiased font-sans" x-data="presensiApp"> {{-- Tambah x-data --}}
    <div class="flex flex-col min-h-screen">

        @include('partials.admin._header') {{-- Masukkan Header --}}

        <main class="flex-1 overflow-y-auto pb-16 md:pb-0">
            @yield('content') {{-- Konten Halaman akan di sini --}}
        </main>

        @include('partials.admin._footer') {{-- Masukkan Footer --}}

    </div>

    {{-- Navigasi Bawah Mobile (di luar flex-col min-h-screen) --}}
    @include('partials.admin._bottomnav')

    {{-- Tempat untuk modal (jika dipisah) --}}
    {{-- @include('partials.admin._modals') --}}

    {{-- Script tambahan per halaman jika perlu --}}
    @stack('scripts')
</body>

</html>
