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

        // Fungsi yang dipanggil saat form modal di-submit
        saveClass() {
            console.log("Submitting class form...");
            const form = document.getElementById("classModalForm");
            if (form) {
                // Lakukan submit form secara manual
                // Action dan Method sudah diatur saat modal dibuka
                form.submit();

                // Opsional: Anda bisa menonaktifkan tombol simpan di sini
                // atau menampilkan indikator loading
            } else {
                console.error(
                    "Form Modal Kelas (#classModalForm) tidak ditemukan saat save!"
                );
            }
        },

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
