// resources/js/app.js
import "./bootstrap"; // Bawaan Laravel

import Alpine from "alpinejs";
import { createIcons, icons, TestTube } from "lucide"; // Import Lucide
// resources/js/app.js (lanjutan)
import Chart from "chart.js/auto"; // Import Chart.js
window.Chart = Chart; // Buat global jika script chart terpisah
// Pindahkan fungsi initCharts, getChartColors, dll. ke sini atau file terpisah

window.Alpine = Alpine;

// === Fungsi Alpine.js Anda ===
function presensiAppData() {
    // Salin semua isi fungsi dari <script> di index versi 2.html
    // KECUALI: Bagian data hardcoded (users, classes) - ini akan dari Laravel
    // Contoh:
    return {
        // ... (state lain seperti activeTab, isMobileMenuOpen, dll) ...

        // State untuk Modal Kelas
        isClassModalOpen: false,
        isEditingClass: false,
        // Pastikan properti sesuai dengan yang digunakan di x-model dan saat pass data dari Blade
        currentClass: {
            id: null,
            nama: "",
            tingkat: "",
            jurusan: "",
            waliKelasId: null,
            jumlahSiswa: 0,
        },

        // Fungsi untuk mereset data form modal
        resetCurrentClass() {
            this.currentClass = {
                id: null,
                nama: "",
                tingkat: "",
                jurusan: "",
                waliKelasId: null,
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
                // Ganti 'YOUR_APP_URL' dengan URL aplikasi Anda atau gunakan cara lain (Ziggy)
                form.action = "/admin/classes"; // Sesuaikan dengan route('admin.classes.store')
                methodInput.value = "POST";
            } else {
                console.error(
                    "Form atau Method Input Modal Kelas tidak ditemukan!"
                );
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
            this.currentClass = { ...classData };
            // Pastikan waliKelasId adalah string kosong jika null dari Blade
            if (this.currentClass.waliKelasId === null) {
                this.currentClass.waliKelasId = "";
            }
            this.isEditingClass = true;
            const form = document.getElementById("classModalForm");
            const methodInput = document.getElementById("classModalMethod");
            if (form && methodInput) {
                // Ganti 'YOUR_APP_URL' dengan URL aplikasi Anda
                form.action = `/admin/classes/${classData.id}`; // Sesuaikan dengan route('admin.classes.update', id)
                methodInput.value = "PUT";
            } else {
                console.error(
                    "Form atau Method Input Modal Kelas tidak ditemukan!"
                );
            }
            this.isClassModalOpen = true;
            this.$nextTick(() => {
                document.getElementById("nama_kelas")?.focus();
            });
        },

        // Fungsi untuk menutup modal
        closeClassModal() {
            this.isClassModalOpen = false;
            // Tidak perlu reset di sini karena direset saat buka create
            // this.resetCurrentClass();
            // this.isEditingClass = false;
        },

        // Fungsi yang dipanggil saat form modal di-submit
        saveClass() {
            console.log("Submitting class form...");
            const form = document.getElementById("classModalForm");
            if (form) {
                // Lakukan submit form secara manual
                form.submit();
                // Opsional: nonaktifkan tombol simpan, tampilkan loading
                // Opsional: tutup modal setelah submit (atau tunggu halaman reload)
                // this.closeClassModal();
            } else {
                console.error("Form Modal Kelas tidak ditemukan saat save!");
            }
        },

        // ... (sisa fungsi Alpine lainnya seperti init, renderIcons, changeTab, dll.)
        activeTab: "dashboard", // Atau ambil dari URL/data backend nanti
        isMobileMenuOpen: false,
        isUserMenuOpen: false,
        dashboardUserType: "semua",
        // ... state lain (tanpa data users/classes) ...

        // ... methods (init, renderIcons, changeTab, dll.) ...
        init() {
            console.log("Alpine component initialized.");
            this.$nextTick(() => {
                this.renderIcons(); // Panggil renderIcons saat init
                console.log("Initial icons rendered.");
            });
            // ... sisa init() ...
            // PENTING: Hapus this.users = [...] dan this.classes = [...]
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
        // ... sisa methods ...
        // PENTING: Hapus/modifikasi method yg bergantung pada data user/kelas hardcoded (misal filteredUsers, saveClass)
        // Ini akan ditangani Laravel atau dimodifikasi nanti

        // Contoh modifikasi (data kelas akan dari Blade, modal dikontrol saja)
        isClassModalOpen: false,
        isEditingClass: false,
        currentClass: {
            id: null,
            nama: "",
            tingkat: "",
            jurusan: "",
            waliKelasId: null,
            jumlahSiswa: 0,
        }, // Sesuaikan field
        openCreateClassModal() {
            /* ... logika reset & buka modal ... */ this.isClassModalOpen = true;
        },
        openEditClassModal(classData) {
            /* ... set currentClass dari data yg di-pass, buka modal ...*/ this.currentClass =
                classData;
            this.isEditingClass = true;
            this.isClassModalOpen = true;
        },
        closeClassModal() {
            this.isClassModalOpen = false;
            this.isEditingClass = false; /* reset currentClass */
        },
        saveClass() {
            // Submit form-nya secara manual (akan dibuat di Blade)
            // Kita tidak proses save di JS lagi
            // Contoh: document.getElementById('classModalForm').submit();
            console.log("Submit form triggered from Alpine...");
        },
    };
}
// Daftarkan komponen Alpine
document.addEventListener("alpine:init", () => {
    Alpine.data("presensiApp", presensiAppData);
});

// Mulai Alpine
Alpine.start();

// Panggil createIcons sekali lagi setelah DOM siap (jika perlu)
// Atau pastikan dipanggil di Alpine init()
// createIcons({ icons });

// Test pakai js yang original
