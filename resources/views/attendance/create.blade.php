@extends('layouts.admin') {{-- Gunakan layout admin --}}

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        {{-- Judul Halaman --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Lakukan Presensi</h1>
            <p class="text-sm text-gray-500 mt-1">Ambil foto selfie dan pastikan lokasi Anda akurat.</p>
        </div>

        {{-- Kontainer Utama Form (dibatasi lebarnya) --}}
        <div class="max-w-md mx-auto">
            <div class="bg-white overflow-hidden shadow-md rounded-xl border border-gray-200">
                <div class="p-6">

                    {{-- Tampilkan Error Umum dari Controller (jika ada redirect back with error) --}}
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                    {{-- Tampilkan Error Validasi Laravel --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded text-sm">
                            <strong class="font-bold">Oops! Ada kesalahan:</strong>
                            <ul class="mt-1 list-disc list-inside text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    {{-- Status Area untuk Pesan dari JavaScript --}}
                    <div id="statusArea" class="mb-4 p-3 bg-gray-100 text-gray-600 text-sm rounded hidden" role="status"
                        aria-live="polite">
                        Memuat...
                    </div>

                    {{-- Tampilan Kamera --}}
                    <div
                        class="mb-4 relative aspect-video bg-gray-800 rounded-lg overflow-hidden border border-gray-300 shadow-inner">
                        {{-- Video Feed --}}
                        <video id="cameraFeed" autoplay playsinline muted
                            class="w-full h-full object-cover transform scale-x-[-1]"> {{-- scale-x-[-1] untuk mirror --}}
                            Browser Anda tidak mendukung video tag.
                        </video>
                        {{-- Pesan Error Kamera --}}
                        <p id="cameraError"
                            class="absolute inset-0 flex items-center justify-center text-white bg-black bg-opacity-50 hidden p-4 text-center text-sm font-semibold">
                            Gagal mengakses kamera. Pastikan Anda memberikan izin.
                        </p>
                        {{-- Overlay Loading Kamera --}}
                        <div id="cameraLoading"
                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>Memuat Kamera...</span>
                        </div>
                    </div>

                    {{-- Canvas Tersembunyi untuk mengambil frame foto --}}
                    <canvas id="photoCanvas" class="hidden"></canvas>

                    {{-- Form Tersembunyi untuk mengirim data ke Controller --}}
                    <form id="attendanceForm" method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="selfie_image_base64" id="selfie_image_base64">
                    </form>

                    {{-- Tombol Aksi Presensi --}}
                    <button id="captureBtn" type="button" disabled
                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                        {{-- Icon Loading (tampil saat proses) --}}
                        <svg id="loadingIcon" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{-- Icon Kamera (tampil normal) --}}
                        <i id="cameraIcon" data-lucide="camera" class="w-5 h-5 mr-2"></i>
                        <span id="buttonText">Ambil Foto & Presensi</span>
                    </button>
                    <p class="text-xs text-center text-gray-500 mt-2">Pastikan wajah terlihat jelas dan izin lokasi aktif.
                    </p>

                </div>
            </div>
            {{-- Link ke Riwayat --}}
            <div class="text-center mt-6">
                <a href="{{ route('attendance.history') }}" class="text-sm text-indigo-600 hover:underline">Lihat Riwayat
                    Presensi Saya</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Pastikan script ini dieksekusi setelah DOM siap
        document.addEventListener('DOMContentLoaded', () => {

            // --- Variabel Elemen DOM ---
            const videoEl = document.getElementById('cameraFeed');
            const canvasEl = document.getElementById('photoCanvas');
            const captureBtnEl = document.getElementById('captureBtn');
            const formEl = document.getElementById('attendanceForm');
            const latitudeInputEl = document.getElementById('latitude');
            const longitudeInputEl = document.getElementById('longitude');
            const imageInputEl = document.getElementById('selfie_image_base64');
            const statusAreaEl = document.getElementById('statusArea');
            const cameraErrorEl = document.getElementById('cameraError');
            const cameraLoadingEl = document.getElementById('cameraLoading');
            const loadingIconEl = document.getElementById('loadingIcon');
            const cameraIconEl = document.getElementById('cameraIcon');
            const buttonTextEl = document.getElementById('buttonText');

            // --- State Aplikasi Mini ---
            let activeStream = null;
            let isLocationReady = false;
            let isCameraReady = false;
            let isProcessing = false; // Untuk mencegah double click

            // --- Fungsi Helper ---
            const showStatus = (message, type = 'info') => {
                statusAreaEl.textContent = message;
                statusAreaEl.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-yellow-100',
                    'text-yellow-700', 'bg-green-100', 'text-green-700', 'bg-gray-100', 'text-gray-600');
                switch (type) {
                    case 'error':
                        statusAreaEl.classList.add('bg-red-100', 'text-red-700');
                        break;
                    case 'warning':
                        statusAreaEl.classList.add('bg-yellow-100', 'text-yellow-700');
                        break;
                    case 'success':
                        statusAreaEl.classList.add('bg-green-100', 'text-green-700');
                        break;
                    default: // info
                        statusAreaEl.classList.add('bg-gray-100', 'text-gray-600');
                }
            };

            const setButtonState = (enabled, processing = false) => {
                isProcessing = processing;
                captureBtnEl.disabled = !enabled || processing;
                loadingIconEl.classList.toggle('hidden', !processing);
                cameraIconEl.classList.toggle('hidden', processing);
                buttonTextEl.textContent = processing ? 'MEMPROSES...' : 'Ambil Foto & Presensi';
            };

            const checkAndEnableButton = () => {
                if (isLocationReady && isCameraReady && !isProcessing) {
                    setButtonState(true);
                    showStatus('Kamera & lokasi siap. Silakan ambil foto.');
                } else if (!isProcessing) {
                    setButtonState(false); // Tetap disabled jika salah satu belum siap
                }
            };

            // --- Logika Utama ---

            // 1. Inisialisasi Kamera
            const initCamera = async () => {
                showStatus('Menginisialisasi kamera...');
                cameraLoadingEl.classList.remove('hidden');
                cameraErrorEl.classList.add('hidden');
                isCameraReady = false;
                setButtonState(false);

                // Hentikan stream lama jika ada
                if (activeStream) {
                    activeStream.getTracks().forEach(track => track.stop());
                }

                const constraints = {
                    video: {
                        facingMode: 'user', // Kamera depan
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        }
                    },
                    audio: false
                };

                try {
                    activeStream = await navigator.mediaDevices.getUserMedia(constraints);
                    videoEl.srcObject = activeStream;
                    videoEl.onloadedmetadata = () => {
                        canvasEl.width = videoEl.videoWidth;
                        canvasEl.height = videoEl.videoHeight;
                        isCameraReady = true;
                        cameraLoadingEl.classList.add('hidden'); // Sembunyikan loading
                        console.log(`Kamera siap: ${videoEl.videoWidth}x${videoEl.videoHeight}`);
                        checkAndEnableButton();
                    };
                } catch (err) {
                    console.error("Error Kamera:", err);
                    cameraErrorEl.textContent =
                        `Error: ${err.name}. Tidak bisa akses kamera. Pastikan izin diberikan dan tidak ada aplikasi lain yang menggunakan kamera.`;
                    cameraErrorEl.classList.remove('hidden');
                    cameraLoadingEl.classList.add('hidden');
                    showStatus(`Error Kamera: ${err.name}`, 'error');
                    isCameraReady = false;
                    setButtonState(false);
                }
            };

            // 2. Dapatkan Lokasi GPS
            const getLocation = () => {
                showStatus('Mendapatkan lokasi GPS...');
                isLocationReady = false;
                setButtonState(false);

                if (!navigator.geolocation) {
                    showStatus("Browser tidak mendukung Geolocation.", 'error');
                    setButtonState(false);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latitudeInputEl.value = position.coords.latitude;
                        longitudeInputEl.value = position.coords.longitude;
                        isLocationReady = true;
                        console.log(
                            `Lokasi ditemukan: ${latitudeInputEl.value}, ${longitudeInputEl.value}`);
                        checkAndEnableButton();
                    },
                    (error) => {
                        console.error("Error Lokasi:", error);
                        let errorMsg = 'Gagal mendapatkan lokasi: ';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg += "Izin lokasi ditolak.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg += "Informasi lokasi tidak tersedia.";
                                break;
                            case error.TIMEOUT:
                                errorMsg += "Waktu permintaan lokasi habis.";
                                break;
                            default:
                                errorMsg += "Terjadi kesalahan tidak diketahui.";
                                break;
                        }
                        showStatus(errorMsg, 'error');
                        isLocationReady = false;
                        setButtonState(false);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 20000,
                        maximumAge: 0
                    } // Timeout 20 detik
                );
            };

            // 3. Aksi Saat Tombol Capture Ditekan
            captureBtnEl.addEventListener('click', () => {
                if (!isLocationReady || !isCameraReady || isProcessing) {
                    showStatus('Lokasi atau kamera belum siap, atau sedang diproses.', 'warning');
                    return;
                }

                setButtonState(false, true); // Nonaktifkan & tunjukkan loading
                showStatus('Mengambil gambar dan mengirim data...');

                try {
                    // Ambil gambar dari video ke canvas
                    const context = canvasEl.getContext('2d');
                    // Gambar dengan mirroring agar sesuai preview
                    context.translate(canvasEl.width, 0);
                    context.scale(-1, 1);
                    context.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                    context.setTransform(1, 0, 0, 1, 0, 0); // Reset transform

                    // Konversi ke base64 PNG
                    const imageDataUrl = canvasEl.toDataURL('image/png');
                    imageInputEl.value = imageDataUrl;

                    // Submit form
                    console.log("Mengirim form presensi...");
                    formEl.submit();

                    // --- Catatan: Jika menggunakan AJAX ---
                    // const formData = new FormData(formEl);
                    // fetch(formEl.action, { method: 'POST', body: formData })
                    //     .then(response => {
                    //         if (!response.ok) throw new Error('Network response was not ok');
                    //         return response.json(); // Asumsi controller return JSON
                    //     })
                    //     .then(data => {
                    //         if(data.success) {
                    //              showStatus(data.message || 'Presensi berhasil!', 'success');
                    //              // Mungkin redirect atau disable form
                    //              window.location.href = '{{ route('attendance.history') }}'; // Redirect ke riwayat
                    //         } else {
                    //              showStatus(data.message || 'Gagal mencatat presensi.', 'error');
                    //              setButtonState(true); // Aktifkan lagi jika gagal
                    //         }
                    //     })
                    //     .catch(error => {
                    //         console.error('Fetch Error:', error);
                    //         showStatus('Terjadi kesalahan koneksi saat mengirim data.', 'error');
                    //         setButtonState(true);
                    //     });
                    // --- Akhir Catatan AJAX ---

                } catch (error) {
                    console.error("Error saat capture/submit:", error);
                    showStatus('Gagal memproses gambar atau data.', 'error');
                    setButtonState(true); // Aktifkan lagi jika error
                }
            });

            // --- Panggil Inisialisasi ---
            initCamera();
            getLocation();

            // Render Ikon Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                console.warn("Lucide library not found for icon rendering.");
            }

        }); // Akhir DOMContentLoaded
    </script>
@endpush
