<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Penting untuk form POST --}}

    <title>Login - Presensi {{ config('app.name', 'SMK Kristen Penabur Purworejo') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    {{-- Link favicon lain jika ada --}}

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Muat Tailwind & JS --}}

    {{-- Style tambahan khusus halaman login jika perlu --}}
    <style>
        /* Style untuk form (bisa juga ditaruh di app.css) */
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input {
            appearance: none;
            width: 100%;
            padding: 0.75rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input.pl-10 {
            padding-left: 2.5rem;
        }

        /* Padding kiri untuk ikon */
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        /* Dark mode styles (opsional) */
        .dark .form-label {
            color: #d1d5db;
        }

        .dark .form-input {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .dark .form-input::placeholder {
            color: #6b7280;
        }

        /* Style untuk alert (contoh) */
        .alert-danger {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
            font-size: 0.875rem;
        }

        .alert-danger ul {
            margin-top: 0.25rem;
            padding-left: 1.25rem;
            list-style: disc;
        }
    </style>
</head>

<body class="font-sans antialiased">
    {{-- Kontainer Utama - Menengahkan Konten --}}
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-indigo-100 via-gray-50 to-sky-100 dark:from-gray-800 dark:via-gray-900 dark:to-black px-4 py-8 sm:py-12">

        {{-- Branding Sekolah --}}
        <div class="mb-6 text-center">
            <a href="/" class="inline-block">
                <div class="flex flex-col items-center text-gray-800 dark:text-gray-200">
                    {{-- Ganti dengan <img> logo jika ada --}}
                    {{-- <img src="{{ asset('logo.png') }}" alt="Logo Sekolah" class="w-20 h-20 mb-2"> --}}
                    <svg class="h-12 w-auto text-indigo-600 mb-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{-- <span class="text-2xl font-bold tracking-tight">{{ config('app.name', 'Presensi Online') }}</span> --}}
                    <span class="text-2xl font-semibold text-indigo-700 dark:text-indigo-400 mt-1">SMK Kristen Penabur
                        Purworejo</span>
                </div>
            </a>
        </div>

        {{-- Card Form Login --}}
        <div
            class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-8 sm:px-10">
                <h2 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200 mb-6">
                    Silakan Login
                </h2>

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-danger">
                        <ul class="mt-1 list-disc list-inside text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div class="relative">
                        <label for="email" class="form-label sr-only">Email</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input id="email" class="block w-full form-input pl-10" type="email" name="email"
                            value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="Alamat Email" />
                    </div>

                    <div class="relative">
                        <label for="password" class="form-label sr-only">Password</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input id="password" class="block w-full form-input pl-10" type="password" name="password"
                            required autocomplete="current-password" placeholder="Password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600"
                                name="remember">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 rounded-md focus:outline-none focus:ring-indigo-500"
                                href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <div class="pt-2"> {{-- Sedikit jarak atas --}}
                        <button type="submit"
                            class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-widest transition duration-150 ease-in-out transform hover:scale-[1.01]">
                            Log in
                        </button>
                    </div>
                </form> {{-- Akhir Form --}}
            </div> {{-- Akhir Padding Card --}}
        </div> {{-- Akhir Card Form --}}

        {{-- Footer (di luar card) --}}
        <footer class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} SMK Kristen Penabur Purworejo.<br class="sm:hidden"> Hak Cipta Dilindungi.
        </footer>

    </div> {{-- Akhir Kontainer Utama --}}

    {{-- Panggil Lucide (jika belum di app.js) --}}
    @stack('scripts')
    <script>
        // Render ikon saat DOM siap jika diperlukan
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
