<section class="space-y-6">
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800">
            Hapus Akun
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, silakan unduh data atau informasi yang ingin Anda pertahankan.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
    >Hapus Akun</button>

    <div x-data="{ show: false }" 
         x-show="show" 
         x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true" 
         x-on:keydown.escape.window="show = false" 
         x-on:click.away="show = false"
         class="modal-overlay" 
         style="display: none;">
        <div class="modal-content" x-on:click.stop>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <h2 class="modal-title text-red-600">
                    Apakah Anda yakin ingin menghapus akun Anda?
                </h2>

                <div class="modal-body">
                    <p class="text-sm text-gray-600">
                        Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Silakan masukkan password Anda untuk mengkonfirmasi bahwa Anda ingin menghapus akun secara permanen.
                    </p>

                    <div class="mt-4">
                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-input"
                            placeholder="Masukkan password Anda"
                        />
                        @error('password', 'userDeletion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Batal
                    </button>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
