<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman formulir pengaturan profil akun karyawan.
     */
    public function edit(): View
    {
        $user = Auth::user();
        return view('karyawan.profile', compact('user'));
    }

    /**
     * Memperbarui informasi profil mandiri karyawan (Nama, Email, No Telp, Avatar).
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_telp'       => ['nullable', 'string', 'max:30'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Alamat email sudah digunakan oleh pengguna lain.',
            'avatar.image'   => 'Berkas avatar harus berupa gambar.',
            'avatar.mimes'   => 'Format avatar harus JPG, JPEG, atau PNG.',
            'avatar.max'     => 'Ukuran berkas avatar maksimal 2MB.',
        ]);

        $updateData = [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'no_telp' => $validated['no_telp'] ?? null,
        ];

        // Hapus avatar jika diminta
        if (!empty($validated['remove_avatar']) && $validated['remove_avatar']) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = null;
        }

        // Upload avatar baru jika ada
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('karyawan.profile')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Memperbarui kata sandi akun karyawan.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required'         => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi lama yang Anda masukkan tidak sesuai.',
            'password.required'                 => 'Kata sandi baru wajib diisi.',
            'password.confirmed'                => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('karyawan.profile')->with('success', 'Kata sandi akun Anda berhasil diubah.');
    }
}
