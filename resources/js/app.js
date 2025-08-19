// resources/js/app.js
import "./bootstrap"; // Bawaan Laravel

import Alpine from "alpinejs";
import { createIcons, icons } from "lucide"; // Import Lucide (TestTube tidak perlu jika tidak dipakai)
import Chart from "chart.js/auto"; // Import Chart.js

window.Chart = Chart; // Buat global jika script chart terpisah
window.Alpine = Alpine;

// === Fungsi Alpine.js Anda (Sudah Diperbaiki) ===
function presensiAppData() {
    return {
        // --- State Aplikasi Umum ---
        activeTab: "dashboard", // Atau ambil dari URL/data backend nanti
        isMobileMenuOpen: false,
        isUserMenuOpen: false,
        dashboardUserType: "semua",
        // ... state lain (tanpa data users/classes hardcoded) ...

        // --- State untuk Modal Kelas ---
        isClassModalOpen: false,
        isEditingClass: false,
        // Pastikan properti sesuai dengan yang digunakan di x-model dan saat pass data dari Blade
        currentClass: {
            id: null,
            nama: "",
            tingkat: "",
            jurusan: "",
            waliKelasId: null, // Gunakan null untuk representasi data, akan diubah jadi "" untuk select jika perlu
            jumlahSiswa: 0, // Ini mungkin tidak di-pass dari create, hanya relevan untuk edit
        },

        // --- Methods Aplikasi Umum ---
        init() {
            console.log("Alpine component initialized.");
            this.$nextTick(() => {
                this.renderIcons(); // Panggil renderIcons saat init
                console.log("Initial icons rendered.");
            });
            // ... sisa init() jika ada ...
            // PENTING: Hapus this.users = [...] dan this.classes = [...] jika ada dari versi sebelumnya
        },

        renderIcons() {
            console.log("Rendering Lucide icons...");
            try {
                // Panggil createIcons dari Lucide
                createIcons({ icons });
            } catch (e) {
                console.error("Error rendering Lucide icons:", e);
            }
        },

        changeTab(tabName) {
            this.activeTab = tabName;
            // Mungkin perlu render ulang icon/chart jika konten tab berubah drastis
            this.$nextTick(() => this.renderIcons());
        },

        // --- Methods untuk Modal Kelas ---

        // Fungsi untuk mereset data form modal
        resetCurrentClass() {
            this.currentClass = {
                id: null,
                nama: "",
                tingkat: "", // Reset ke string kosong atau nilai default select
                jurusan: "",
                waliKelasId: "", // Reset ke string kosong agar cocok dengan opsi "-- Tidak Ada --"
                jumlahSiswa: 0,
            };
        },

        // Fungsi untuk membuka modal tambah
        openCreateClassModal() {
            this.resetCurrentClass();
            this.isEditingClass = false;
            const form = document.getElementById("classModalForm");
            const methodInput = document.getElementById("classModalMethod");
            if (form && methodInput) {
                // URL untuk store (sesuaikan jika base path aplikasi berbeda)
                form.action = "/admin/classes"; // Pastikan route ini benar
                methodInput.value = "POST";
            } else {
                console.error(
                    "Form (#classModalForm) atau Method Input (#classModalMethod) Modal Kelas tidak ditemukan!"
                );
                return; // Hentikan jika elemen penting tidak ada
            }
            this.isClassModalOpen = true;
            // Fokus ke input pertama setelah modal terbuka
            this.$nextTick(() => {
                document.getElementById("nama_kelas")?.focus();
            });
        },

        // Fungsi untuk membuka modal edit (menerima data dari Blade)
        openEditClassModal(classData) {
            // Isi state Alpine dengan data dari parameter
            // Gunakan spread operator untuk menyalin properti
            this.currentClass = {
                ...this.currentClass, // Ambil struktur default (jika ada properti tambahan)
                ...classData, // Timpa dengan data yang di-pass
            };

            // Pastikan waliKelasId adalah string kosong jika null/undefined dari Blade agar cocok select option
            if (
                this.currentClass.waliKelasId === null ||
                this.currentClass.waliKelasId === undefined
            ) {
                this.currentClass.waliKelasId = "";
            } else {
                // Pastikan tipenya string jika ID dari blade berupa number agar x-model select bekerja baik
                this.currentClass.waliKelasId = String(
                    this.currentClass.waliKelasId
                );
            }

            // Pastikan tingkat juga string jika dari blade berupa number
            if (
                this.currentClass.tingkat !== null &&
                this.currentClass.tingkat !== undefined
            ) {
                this.currentClass.tingkat = String(this.currentClass.tingkat);
            }

            this.isEditingClass = true;
            const form = document.getElementById("classModalForm");
            const methodInput = document.getElementById("classModalMethod");
            if (form && methodInput) {
                // URL untuk update (sesuaikan jika base path berbeda)
                form.action = `/admin/classes/${classData.id}`; // Pastikan route ini benar
                methodInput.value = "PUT";
            } else {
                console.error(
                    "Form (#classModalForm) atau Method Input (#classModalMethod) Modal Kelas tidak ditemukan!"
                );
                return; // Hentikan jika elemen penting tidak ada
            }
            this.isClassModalOpen = true;
            this.$nextTick(() => {
                document.getElementById("nama_kelas")?.focus();
            });
        },

        // Fungsi untuk menutup modal
        closeClassModal() {
            this.isClassModalOpen = false;
            // Sebaiknya reset form saat modal ditutup, atau minimal saat dibuka untuk create
            // this.resetCurrentClass(); // Anda bisa uncomment ini jika ingin reset saat tutup
        },
        // In resources/js/app.js

        // ... other functions ...

        // Fungsi yang dipanggil saat form modal di-submit
        saveClass() {
            console.log("Submitting class form via Fetch...");
            const form = document.getElementById("classModalForm");
            const method = this.isEditingClass ? "PUT" : "POST";
            const url = this.isEditingClass
                ? `/admin/classes/${this.currentClass.id}`
                : "/admin/classes";

            // Clear previous errors
            const errorElement = document.getElementById("nama_kelas_error");
            const inputElement = document.getElementById("nama_kelas");
            errorElement.textContent = "";
            inputElement.classList.remove("border-red-500");

            // Get the CSRF token from the meta tag (make sure you have this in your main layout)
            // <meta name="csrf-token" content="{{ csrf_token() }}">
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch(url, {
                method: method,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                // Send only the necessary data from the Alpine state
                body: JSON.stringify({
                    nama_kelas: this.currentClass.nama,
                    tingkat: this.currentClass.tingkat,
                    jurusan: this.currentClass.jurusan,
                }),
            })
                .then((response) => {
                    // We need the JSON body regardless of success or failure
                    return response
                        .json()
                        .then((data) => ({
                            status: response.status,
                            body: data,
                        }));
                })
                .then(({ status, body }) => {
                    if (status === 422) {
                        // Validation Error
                        console.error("Validation failed:", body.errors);
                        if (body.errors && body.errors.nama_kelas) {
                            errorElement.textContent =
                                body.errors.nama_kelas[0];
                            inputElement.classList.add("border-red-500");
                        }
                        // You can add handling for other fields here if needed
                    } else if (status >= 200 && status < 300) {
                        // Success
                        console.log("Success:", body.success);
                        // On success, close the modal and reload the page to see the changes
                        this.closeClassModal();
                        window.location.reload();
                    } else {
                        // Other server errors (403, 500, etc.)
                        console.error(
                            "An unexpected error occurred:",
                            body.message
                        );
                        // Optionally, show a generic error message to the user
                        alert(
                            "Terjadi kesalahan pada server. Silakan coba lagi."
                        );
                    }
                })
                .catch((error) => {
                    console.error("Fetch error:", error);
                    alert(
                        "Tidak dapat terhubung ke server. Periksa koneksi Anda."
                    );
                });
        },

        // ... rest of your Alpine component ...
        // Fungsi yang dipanggil saat form modal di-submit
        // saveClass() {
        //     console.log("Submitting class form via Fetch...");
        //     const form = document.getElementById("classModalForm");
        //     const method = document.getElementById('classModalMethod').value;

        //     let url;
        //     if (this.isEditingClass) {
        //         url = `/admin/classes/${this.currentClass.id}`;
        //     } else {
        //         url = '/admin/classes';
        //     }

        //     // Data to be sent as JSON
        //     const requestData = {
        //         _token: document.querySelector('input[name="_token"]').value,
        //         nama_kelas: this.currentClass.nama,
        //         tingkat: this.currentClass.tingkat,
        //         jurusan: this.currentClass.jurusan,
        //     };

        //     // If it's an update (PUT), include the _method field for Laravel's method spoofing
        //     // Although for JSON, Laravel often infers method from the actual HTTP method,
        //     // including _method can be a good fallback for consistency.
        //     if (method === 'PUT') {
        //         requestData._method = 'PUT';
        //     }

        //     // Clear previous errors
        //     document.getElementById('nama_kelas_error').textContent = '';
        //     document.getElementById('nama_kelas').classList.remove('border-red-500');

        //     fetch(url, {
        //         method: method, // Use the actual HTTP method (POST or PUT)
        //         headers: {
        //             'X-CSRF-TOKEN': requestData._token, // Get CSRF token from the data object
        //             'Accept': 'application/json',
        //             'Content-Type': 'application/json', // Crucial for sending JSON
        //         },
        //         body: JSON.stringify(requestData) // Send data as JSON string
        //     })
        //     .then(response => {
        //         if (response.ok) {
        //             window.location.reload();
        //         } else if (response.status === 422) {
        //             return response.json().then(data => {
        //                 if (data.errors && data.errors.nama_kelas) {
        //                     const errorElement = document.getElementById('nama_kelas_error');
        //                     const inputElement = document.getElementById('nama_kelas');
        //                     errorElement.textContent = data.errors.nama_kelas[0];
        //                     inputElement.classList.add('border-red-500');
        //                 }
        //             });
        //         } else {
        //             // Handle other errors
        //             console.error('An unexpected error occurred.');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Fetch error:', error);
        //     });
        // },

        // ... (sisa fungsi Alpine lainnya jika ada) ...
    };
}

// Daftarkan komponen Alpine
document.addEventListener("alpine:init", () => {
    Alpine.data("presensiApp", presensiAppData);
});

// Mulai Alpine
Alpine.start();

// Panggil createIcons sekali lagi setelah DOM siap jika diperlukan di luar komponen Alpine
// createIcons({ icons });
// Namun, pemanggilan di init() dalam komponen biasanya sudah cukup jika ikon ada di dalam x-data.
