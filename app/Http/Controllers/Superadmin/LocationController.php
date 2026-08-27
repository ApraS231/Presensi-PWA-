<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Menampilkan daftar master lokasi & geofence.
     */
    public function index(): View
    {
        $locations = Location::orderBy('name')->paginate(10);
        return view('superadmin.locations.index', compact('locations'));
    }

    /**
     * Menampilkan form penambahan titik lokasi baru.
     */
    public function create(): View
    {
        return view('superadmin.locations.create');
    }

    /**
     * Menyimpan titik lokasi baru ke basis data.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active'     => 'nullable|boolean',
        ]);

        $location = Location::create([
            'name'          => $validated['name'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'is_active'     => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('superadmin.locations.index')
            ->with('success', "Titik lokasi geofence \"{$location->name}\" berhasil ditambahkan.");
    }

    /**
     * Menampilkan form pengeditan titik lokasi.
     */
    public function edit(Location $location): View
    {
        return view('superadmin.locations.edit', compact('location'));
    }

    /**
     * Memperbarui data titik lokasi.
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

        $location->update([
            'name'          => $validated['name'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'is_active'     => $request->has('is_active') ? (bool) $request->is_active : false,
        ]);

        return redirect()->route('superadmin.locations.index')
            ->with('success', "Data titik lokasi \"{$location->name}\" berhasil diperbarui.");
    }

    /**
     * Mengubah status aktif/non-aktif titik lokasi.
     */
    public function toggleStatus(Location $location): RedirectResponse
    {
        $newStatus = !$location->is_active;
        $location->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Titik lokasi \"{$location->name}\" berhasil {$statusText}.");
    }

    /**
     * Menghapus titik lokasi dari basis data.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $name = $location->name;
        $location->delete();

        return redirect()->route('superadmin.locations.index')
            ->with('success', "Titik lokasi \"{$name}\" berhasil dihapus.");
    }
}
