<!DOCTYPE html>
<html lang="id" x-data="presensiApp" x-init="init()">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Penabur Presensi') }} </title> {{-- Judul Dinamis --}}
    <!-- Favicon SVG agar konsisten dengan logo navbar -->
    {{-- href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='indigo' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='9' stroke='indigo' stroke-width='2' fill='white'/%3E%3Cpath d='M12 8v4l3 3' stroke='indigo' stroke-width='2' fill='none'/%3E%3C/svg%3E"> --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo/logo.svg') }}">
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
