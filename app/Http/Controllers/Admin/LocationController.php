<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Menampilkan daftar seluruh titik lokasi kantor dan proyek geofence.
     */
    public function index(Request $request): View
    {
        $query = Location::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $locations = $query->orderBy('name')->paginate(15);
        $totalActive = Location::where('is_active', true)->count();
        $totalInactive = Location::where('is_active', false)->count();

        return view('admin.locations.index', compact('locations', 'totalActive', 'totalInactive'));
    }

    /**
     * Menyimpan titik lokasi geofence baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active'     => 'nullable|boolean',
        ], [
            'name.required'          => 'Nama titik lokasi wajib diisi.',
            'latitude.required'      => 'Koordinat Latitude wajib diisi.',
            'longitude.required'     => 'Koordinat Longitude wajib diisi.',
            'radius_meters.required' => 'Radius geofence meter wajib diisi.',
            'radius_meters.min'      => 'Radius minimal adalah 10 meter.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Location::create($validated);

        return redirect()->route('admin.locations.index')->with('success', 'Titik lokasi geofence baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data titik lokasi geofence.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $location->update($validated);

        return redirect()->route('admin.locations.index')->with('success', "Titik lokasi {$location->name} berhasil diperbarui.");
    }

    /**
     * Mengubah status aktif / nonaktif titik lokasi.
     */
    public function toggleStatus(Location $location): RedirectResponse
    {
        $location->update(['is_active' => !$location->is_active]);
        $statusText = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Titik lokasi {$location->name} berhasil {$statusText}.");
    }

    /**
     * Menghapus titik lokasi.
     */
    public function destroy(Location $location): RedirectResponse
    {
        if ($location->attendances()->exists()) {
            $location->update(['is_active' => false]);
            return back()->with('warning', "Titik lokasi {$location->name} memiliki riwayat presensi, sehingga otomatis dinonaktifkan alih-alih dihapus permanen.");
        }

        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Titik lokasi geofence berhasil dihapus.');
    }
}
