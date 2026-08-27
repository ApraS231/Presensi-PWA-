<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\FaceDescriptor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Menampilkan antarmuka pemindai webcam enrollment biometrik wajah.
     */
    public function showForm(): View
    {
        $user = Auth::user();
        $faceDescriptor = $user->faceDescriptor;

        return view('karyawan.enrollment', compact('user', 'faceDescriptor'));
    }

    /**
     * Menyimpan data vektor embedding biometrik 128-float dan foto sampel.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'descriptor_data'   => 'required|array|size:128',
            'descriptor_data.*' => 'numeric',
            'sample_photo'      => 'required|string',
        ], [
            'descriptor_data.required' => 'Vektor biometrik wajah wajib disertakan.',
            'descriptor_data.size'     => 'Vektor biometrik harus berupa array 128 float neural descriptor.',
            'sample_photo.required'    => 'Foto snapshot wajah wajib disertakan.',
        ]);

        $user = Auth::user();

        // 1. Dekode base64 foto snapshot
        $photoData = $request->input('sample_photo');
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
            $extension = strtolower($type[1]); // jpg, png, etc.
        } else {
            $extension = 'jpg';
        }

        $decodedImage = base64_decode($photoData);
        if ($decodedImage === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format foto snapshot tidak valid.',
            ], 422);
        }

        // 2. Simpan file foto ke storage publik
        $fileName = "faces/{$user->id}_" . time() . ".{$extension}";
        Storage::disk('public')->put($fileName, $decodedImage);

        // Hapus foto sampel lama jika ada
        if ($user->faceDescriptor && $user->faceDescriptor->sample_photo) {
            Storage::disk('public')->delete($user->faceDescriptor->sample_photo);
        }

        // 3. Simpan atau perbarui data FaceDescriptor
        FaceDescriptor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'descriptor_data' => $request->input('descriptor_data'),
                'sample_photo'    => $fileName,
            ]
        );

        // 4. Perbarui status enrollment pengguna
        $user->update([
            'enrollment_status' => 'enrolled',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Pendaftaran biometrik wajah Anda telah berhasil disimpan!',
                'redirect' => route('karyawan.dashboard'),
            ]);
        }

        return redirect()->route('karyawan.dashboard')->with('success', 'Pendaftaran biometrik wajah berhasil.');
    }
}
