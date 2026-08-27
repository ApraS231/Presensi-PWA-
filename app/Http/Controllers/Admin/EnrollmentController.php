<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Menampilkan daftar dan status pendaftaran biometrik seluruh karyawan.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'karyawan')
            ->with('faceDescriptor');

        // Filter Pencarian Nama / NIK
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter Status Enrollment
        if ($status = $request->input('status')) {
            $query->where('enrollment_status', $status);
        }

        // Filter Departemen
        if ($dept = $request->input('department')) {
            $query->where('department', $dept);
        }

        $employees = $query->orderBy('name')->paginate(20);

        // Statistik Ringkasan
        $totalEmployees = User::where('role', 'karyawan')->count();
        $totalEnrolled  = User::where('role', 'karyawan')->where('enrollment_status', 'enrolled')->count();
        $totalPending   = User::where('role', 'karyawan')->where('enrollment_status', 'pending')->count();
        $departments    = User::whereNotNull('department')->distinct()->pluck('department');

        return view('admin.enrollment.index', compact(
            'employees',
            'totalEmployees',
            'totalEnrolled',
            'totalPending',
            'departments'
        ));
    }

    /**
     * Mereset status pendaftaran biometrik karyawan.
     */
    public function reset(User $user, NotificationService $notificationService): RedirectResponse
    {
        if ($user->faceDescriptor) {
            // Hapus file foto dari storage jika ada
            if ($user->faceDescriptor->sample_photo) {
                Storage::disk('public')->delete($user->faceDescriptor->sample_photo);
            }

            $user->faceDescriptor->delete();
        }

        $user->update([
            'enrollment_status' => 'pending',
        ]);

        // Kirim notifikasi peringatan ke karyawan
        $notificationService->send(
            $user,
            'Pendaftaran Biometrik Di-reset',
            'Data pendaftaran biometrik wajah Anda telah di-reset oleh HRD. Silakan lakukan pendaftaran wajah baru melalui menu Pendaftaran Biometrik.',
            'enrollment_required'
        );

        return back()->with('success', "Pendaftaran biometrik karyawan {$user->name} ({$user->nik}) berhasil di-reset menjadi Pending.");
    }
}
