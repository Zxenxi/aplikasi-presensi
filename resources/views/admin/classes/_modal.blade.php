{{-- Modal untuk Tambah/Edit Kelas --}}
<div x-show="isClassModalOpen" x-cloak class="modal-overlay" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="z-index: 100;" {{-- Pastikan z-index tinggi --}}
    aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div class="modal-content" @click.away="closeClassModal()"> {{-- Tutup modal jika klik di luar --}}
        <h3 class="modal-title" id="modal-title" x-text="isEditingClass ? 'Edit Kelas' : 'Tambah Kelas Baru'"></h3>

        <div class="modal-body">
            {{-- Form akan di-submit oleh Alpine --}}
            <form id="classModalForm" method="POST" action="" @submit.prevent="saveClass()" class="space-y-4">
                {{-- Action akan diisi Alpine --}}
                @csrf
                <input type="hidden" name="_method" id="classModalMethod" value="POST"> {{-- Method diisi Alpine (POST/PUT) --}}

                {{-- Nama Kelas --}}
                <div class="form-group">
                    <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_kelas" name="nama_kelas" x-model="currentClass.nama" required
                        class="form-input" placeholder="Contoh: 10 IPA 1">
                    {{-- Error handling bisa ditambahkan di sini jika submit via AJAX,
                        jika pakai full page refresh, error akan tampil di halaman index --}}
                </div>

                {{-- Tingkat --}}
                <div class="form-group">
                    <label for="tingkat" class="form-label">Tingkat <span class="text-red-500">*</span></label>
                    <select id="tingkat" name="tingkat" x-model.number="currentClass.tingkat" required
                        class="form-select">
                        <option value="" disabled>Pilih Tingkat</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        {{-- Tambahkan tingkat lain jika perlu --}}
                    </select>
                </div>

                {{-- Jurusan --}}
                <div class="form-group">
                    <label for="jurusan" class="form-label">Jurusan</label>
                    <input type="text" id="jurusan" name="jurusan" x-model="currentClass.jurusan"
                        class="form-input" placeholder="Contoh: IPA, IPS, Bahasa, Umum (Opsional)">
                </div>

                {{-- Wali Kelas --}}
                <div class="form-group">
                    <label for="modal_wali_kelas_id" class="form-label">Wali Kelas (Opsional)</label>
                    {{-- Dropdown ini diisi dari $guru yang di-pass ke view index --}}
                    <select name="wali_kelas_id" id="modal_wali_kelas_id" x-model="currentClass.waliKelasId"
                        class="form-select">
                        <option value="">-- Tidak Ada Wali Kelas --</option>
                        {{-- Pastikan variabel $guru ada saat include modal ini --}}
                        @isset($guru)
                            @foreach ($guru as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                    {{-- Error spesifik untuk wali_kelas_id bisa ditampilkan di sini jika pakai AJAX --}}
                </div>

                {{-- Jumlah Siswa (Hanya Tampilan di Modal Edit) --}}
                <div class="form-group" x-show="isEditingClass && currentClass.jumlahSiswa !== undefined">
                    <label class="form-label">Jumlah Siswa Saat Ini</label>
                    <p class="text-sm text-gray-600" x-text="currentClass.jumlahSiswa"></p>
                </div>


                {{-- Footer Modal: Tombol Aksi --}}
                <div class="modal-footer">
                    <button type="button" @click="closeClassModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </button>
                    <button type="submit" {{-- Tombol ini sekarang men-trigger @submit.prevent di form --}}
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
