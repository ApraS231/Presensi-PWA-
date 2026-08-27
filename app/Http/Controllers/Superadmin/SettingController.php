<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Menampilkan formulir pengaturan kebijakan kerja sistem.
     */
    public function index(): View
    {
        $settings = [
            'jam_masuk'           => Setting::getValue('jam_masuk', '08:00'),
            'jam_pulang'          => Setting::getValue('jam_pulang', '17:00'),
            'toleransi_terlambat' => Setting::getValue('toleransi_terlambat', '15'),
            'jam_istirahat'       => Setting::getValue('jam_istirahat', '60'),
            'hari_kerja'          => Setting::getValue('hari_kerja', 'senin,selasa,rabu,kamis,jumat'),
            'max_retroaktif_izin' => Setting::getValue('max_retroaktif_izin', '3'),
        ];

        return view('superadmin.settings.index', compact('settings'));
    }

    /**
     * Memperbarui batch konfigurasi kebijakan kerja sistem.
     */
    public function updateBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings.jam_masuk'           => 'required|date_format:H:i',
            'settings.jam_pulang'          => 'required|date_format:H:i|after:settings.jam_masuk',
            'settings.toleransi_terlambat' => 'required|integer|min:0|max:120',
            'settings.jam_istirahat'       => 'required|integer|min:0|max:180',
            'settings.hari_kerja'          => 'required|string',
            'settings.max_retroaktif_izin' => 'required|integer|min:0|max:30',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Konfigurasi kebijakan sistem presensi berhasil disimpan dan langsung diterapkan.');
    }
}
