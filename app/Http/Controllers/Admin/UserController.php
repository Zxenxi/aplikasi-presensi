<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas; // Untuk dropdown kelas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth; // Import Auth Facade

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Terima $request
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Otorisasi
        if (!$user->isSuperAdmin() && !$user->isPetugasPiket()) {
             abort(403, 'Akses Ditolak');
         }

        // Ambil nilai filter dari request
        $search = $request->input('search');
        $filterRole = $request->input('role');
        $filterKelasId = $request->input('kelas_id');

        // Query dasar dengan eager loading kelas
        $query = User::query()->with('kelas');

        // Terapkan filter pencarian (Nama atau Email)
        $query->when($search, function ($q, $search) {
            return $q->where(function($subq) use ($search) {
                $subq->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
            });
        });

        // Terapkan filter Role
        $query->when($filterRole, function ($q, $role) {
            return $q->where('role', $role);
        });

        // Terapkan filter Kelas (hanya jika role adalah Siswa)
        if ($filterRole === 'Siswa') {
            $query->when($filterKelasId, function ($q, $kelasId) {
                return $q->where('kelas_id', $kelasId);
            });
        }

        // Urutkan dan lakukan paginasi
        $users = $query->orderBy('name')->paginate(15)->withQueryString(); // withQueryString() agar filter terbawa di paginasi

        // Ambil data kelas untuk dropdown filter
        $kelas = Kelas::orderBy('nama_kelas')->get();

        // Kirim data users, kelas, dan filter aktif ke view
        return view('admin.users.index', compact('users', 'kelas', 'search', 'filterRole', 'filterKelasId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
            /** @var \App\Models\User $user */ // <-- PHPDoc Hint
            $user = Auth::user();

            // Otorisasi
            if (!$user->isSuperAdmin()) abort(403, 'Akses Ditolak');
        $kelas = Kelas::orderBy('nama_kelas')->get(); // Ambil semua kelas untuk dropdown
        return view('admin.users.create', compact('kelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
  // Di dalam Admin/UserController.php

public function store(Request $request)
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Akses Ditolak');
    }

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role' => ['required', 'in:Super Admin,Guru,Siswa'], // Hapus Petugas Piket jika sudah tidak ada
        'kelas_id' => ['nullable', 'required_if:role,Siswa', 'exists:kelas,id'],
        'is_active' => ['sometimes', 'boolean'], // 'sometimes' berarti jika ada di request, 'boolean' memastikan nilainya 0 atau 1
    ], [
        // ... (pesan validasi lainnya) ...
    ]);

    $validated['password'] = Hash::make($validated['password']);
    if ($validated['role'] !== 'Siswa') {
        $validated['kelas_id'] = null;
    }

    // Set default is_active jika tidak dikirim dari form (misal, checkbox tidak dicentang akan mengirim null)
    $validated['is_active'] = $request->has('is_active'); // Jika checkbox 'is_active' ada dan dicentang, nilainya true

    User::create($validated);

    return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
}

    /**
     * Display the specified resource.
     * (Kita tidak pakai halaman show terpisah di struktur ini)
     */
    public function show(User $user)
    {
        // Jika diperlukan, tambahkan view dan logika di sini
        // Contoh: return view('admin.users.show', compact('user'));
        return redirect()->route('admin.users.index'); // Redirect ke index saja
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user) // Route Model Binding: Laravel otomatis cari User berdasarkan ID di URL
    {
               /** @var \App\Models\User $currentUser */ // <-- PHPDoc Hint
               $currentUser = Auth::user();

               // Otorisasi
               if (!$currentUser->isSuperAdmin()) abort(403, 'Akses Ditolak');
       
        $kelas = Kelas::orderBy('nama_kelas')->get(); // Ambil kelas untuk dropdown
        return view('admin.users.edit', compact('user', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
// Di dalam Admin/UserController.php

public function update(Request $request, User $user)
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Akses Ditolak');
    }

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        'role' => ['required', 'in:Super Admin,Guru,Siswa'], // Hapus Petugas Piket jika sudah tidak ada
        'kelas_id' => ['nullable', 'required_if:role,Siswa', 'exists:kelas,id'],
        'is_active' => ['sometimes', 'boolean'],
    ], [
        // ... (pesan validasi lainnya) ...
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    if ($validated['role'] !== 'Siswa') {
        $validated['kelas_id'] = null;
    }

    $validated['is_active'] = $request->has('is_active');

    // Pencegahan: Super Admin tidak bisa menonaktifkan akunnya sendiri
    if ($user->id === $currentUser->id && !$validated['is_active']) {
        return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.')->withInput();
    }

    $user->update($validated);

    return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
          /** @var \App\Models\User $currentUser */ // <-- PHPDoc Hint
          $currentUser = Auth::user();

          // Otorisasi
          if (!$currentUser->isSuperAdmin()) abort(403, 'Akses Ditolak');
  
        // Pencegahan: jangan sampai menghapus akun sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Logika tambahan jika diperlukan (misal cek relasi lain sebelum hapus)

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
    // Di dalam Admin/UserController.php

public function toggleStatus(User $user)
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    if (!$currentUser->isSuperAdmin()) {
        return back()->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
    }

    // Pencegahan: Super Admin tidak bisa menonaktifkan akunnya sendiri
    if ($user->id === $currentUser->id && $user->is_active) {
        return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
    }

    $user->is_active = !$user->is_active;
    $user->save();

    $message = $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';
    return back()->with('success', $message);
}
}