<!DOCTYPE html>
<html lang="id" x-data="presensiApp" x-init="init()">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Laravel') }} - Admin</title> {{-- Judul Dinamis --}}

    {{-- Load CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Hapus <script> dan <style> inline dari HTML asli --}}
    {{-- Style CSS sudah di app.css (Tailwind), JS di app.js (Alpine, Lucide) --}}
    <style>
        /* Gaya Scrollbar, Navigasi, Badge, dll. (Sama seperti sebelumnya) */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .top-nav-item.active {
            background-color: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
        }

        .top-nav-item {
            color: #6b7280;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        .top-nav-item:hover:not(.active) {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .bottom-nav-container {
            height: 64px;
        }

        .bottom-nav-item.active {
            color: #4f46e5;
        }

        .bottom-nav-item:not(.active) {
            color: #6b7280;
        }

        .bottom-nav-item:hover:not(.active) {
            color: #4f46e5;
        }

        [x-cloak] {
            display: none !important;
        }

        .detail-link,
        .notification-link {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s ease;
        }

        .detail-link:hover,
        .notification-link:hover {
            color: #4f46e5;
        }

        .detail-link i,
        .notification-link i {
            width: 0.875rem;
            height: 0.875rem;
            margin-left: 4px;
        }

        .notification-link i.leading-icon {
            margin-left: 0;
            margin-right: 4px;
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-yellow {
            background-color: #fef9c3;
            color: #f59e0b;
        }

        .badge-orange {
            background-color: #ffedd5;
            color: #f97316;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        .badge-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        /* Aktif */
        .badge-gray {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* Nonaktif */
        .badge-purple {
            background-color: #ede9fe;
            color: #7c3aed;
        }

        /* Petugas Piket */
        .badge-cyan {
            background-color: #cffafe;
            color: #0891b2;
        }

        /* Guru */
        .badge-indigo {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        /* Siswa */
        canvas {
            max-width: 100%;
            height: auto;
        }

        .user-table th,
        .user-table td,
        .class-table th,
        .class-table td {
            padding: 10px 16px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .user-table th,
        .class-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            text-align: left;
        }

        .user-table th.text-center,
        .user-table td.text-center,
        .class-table th.text-center,
        .class-table td.text-center {
            text-align: center;
        }

        .user-table th:first-child,
        .user-table td:first-child {
            padding-left: 16px;
            padding-right: 8px;
            width: 1%;
        }

        .user-table th:last-child,
        .user-table td:last-child,
        .class-table th:last-child,
        .class-table td:last-child {
            padding-right: 16px;
            width: 1%;
            text-align: center;
        }

        .user-table tbody tr:nth-child(even),
        .class-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .user-table tbody tr:hover,
        .class-table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .user-table .action-button,
        .class-table .action-button {
            color: #9ca3af;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .user-table .action-button:hover,
        .class-table .action-button:hover {
            color: #4f46e5;
            background-color: #eef2ff;
        }

        .user-table .action-button i,
        .class-table .action-button i {
            width: 1rem;
            height: 1rem;
        }

        .user-table .role-badge {
            margin-left: 6px;
            margin-top: 4px;
            display: inline-block;
        }

        .user-table .user-name-col p {
            line-height: 1.3;
        }

        .user-table .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            object-fit: cover;
            flex-shrink: 0;
            ring: 1px solid #e5e7eb;
        }

        .user-table input[type="checkbox"],
        .class-table input[type="checkbox"] {
            border-radius: 4px;
            border-color: #d1d5db;
            color: #4f46e5;
            transition: border-color 0.2s ease;
        }

        .user-table input[type="checkbox"]:focus,
        .class-table input[type="checkbox"]:focus {
            ring: 2px;
            ring-offset: 0;
            ring-indigo-500;
            border-color: #4f46e5;
        }

        .user-table input[type="checkbox"]:checked,
        .class-table input[type="checkbox"]:checked {
            border-color: #4f46e5;
            background-color: #4f46e5;
        }

        .bulk-actions-dropdown {
            position: relative;
        }

        .bulk-actions-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 4px;
            min-width: 160px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 10;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .bulk-actions-dropdown:hover .bulk-actions-dropdown-menu,
        .bulk-actions-dropdown button:focus+.bulk-actions-dropdown-menu,
        .bulk-actions-dropdown button[aria-expanded='true']+.bulk-actions-dropdown-menu {
            display: block;
        }

        .bulk-actions-dropdown-item {
            display: block;
            width: 100%;
            padding: 8px 12px;
            font-size: 0.875rem;
            color: #374151;
            text-align: left;
            white-space: nowrap;
            background: none;
            border: none;
            cursor: pointer;
        }

        .bulk-actions-dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .bulk-actions-dropdown-item.delete {
            color: #dc2626;
        }

        .bulk-actions-dropdown-item.delete:hover {
            background-color: #fee2e2;
        }

        /* Gaya Modal */
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

        .form-group {
            margin-bottom: 16px;
        }
    </style>
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
