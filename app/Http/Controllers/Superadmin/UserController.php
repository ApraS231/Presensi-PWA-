<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna / karyawan sistem.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Filter Search (Nama, NIK, Email)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Role
        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        // Filter Departemen
        if ($department = $request->query('department')) {
            $query->where('department', $department);
        }

        // Filter Status Aktif
        if ($request->has('status') && $request->query('status') !== '') {
            $query->where('is_active', $request->query('status') === '1');
        }

        // Filter Status Biometrik
        if ($enrollment = $request->query('enrollment')) {
            $query->where('enrollment_status', $enrollment);
        }

        $users = $query->orderBy('name')->paginate(15);

        $departments = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('superadmin.users.index', compact('users', 'departments'));
    }

    /**
     * Menampilkan form pembuatan pengguna baru.
     */
    public function create(): View
    {
        return view('superadmin.users.create');
    }

    /**
     * Menyimpan pengguna baru ke basis data.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik'        => 'required|string|max:50|unique:users,nik',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'nullable|string|min:6',
            'role'       => 'required|in:karyawan,admin,superadmin',
            'department' => 'nullable|string|max:100',
            'jabatan'    => 'nullable|string|max:100',
            'no_telp'    => 'nullable|string|max:30',
        ]);

        $password = $request->filled('password') ? $request->password : 'ptcak123';

        User::create([
            'nik'               => $validated['nik'],
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($password),
            'role'              => $validated['role'],
            'department'        => $validated['department'],
            'jabatan'           => $validated['jabatan'] ?? null,
            'no_telp'           => $validated['no_telp'] ?? null,
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', "Akun pengguna {$validated['name']} (NIK: {$validated['nik']}) berhasil ditambahkan dengan password default.");
    }

    /**
     * Menampilkan form pengeditan data pengguna.
     */
    public function edit(User $user): View
    {
        return view('superadmin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nik'        => 'required|string|max:50|unique:users,nik,' . $user->id,
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:6',
            'role'       => 'required|in:karyawan,admin,superadmin',
            'department' => 'nullable|string|max:100',
            'jabatan'    => 'nullable|string|max:100',
            'no_telp'    => 'nullable|string|max:30',
        ]);

        $updateData = [
            'nik'        => $validated['nik'],
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'role'       => $validated['role'],
            'department' => $validated['department'],
            'jabatan'    => $validated['jabatan'] ?? null,
            'no_telp'    => $validated['no_telp'] ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('superadmin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan / menonaktifkan status akun pengguna secara instan.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusText}.");
    }

    /**
     * Mereset pendaftaran biometrik wajah karyawan.
     */
    public function resetFace(User $user): RedirectResponse
    {
        $user->faceDescriptor()?->delete();
        $user->update(['enrollment_status' => 'pending']);

        return back()->with('success', "Biometrik wajah pengguna {$user->name} berhasil direset. Pengguna wajib mendaftar ulang.");
    }

    /**
     * Menghapus akun pengguna dari sistem.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', "Akun pengguna {$userName} berhasil dihapus dari sistem.");
    }
}
