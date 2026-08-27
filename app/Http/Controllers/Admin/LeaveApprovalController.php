<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    /**
     * Menampilkan daftar perizinan karyawan untuk evaluasi HRD.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = Leave::with(['user', 'approver'])->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(15);

        $pendingCount = Leave::where('status', 'pending')->count();
        $approvedCount = Leave::where('status', 'approved')->count();
        $rejectedCount = Leave::where('status', 'rejected')->count();

        return view('admin.leaves.index', compact(
            'leaves',
            'status',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Memproses evaluasi dan persetujuan (Approve / Reject) perizinan.
     */
    public function process(
        Request $request,
        Leave $leave,
        NotificationService $notificationService
    ): RedirectResponse {
        $validated = $request->validate([
            'action'      => 'required|in:approve,reject',
            'review_note' => 'nullable|string|max:500',
        ]);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan perizinan ini sudah pernah diproses sebelumnya.');
        }

        DB::transaction(function () use ($validated, $leave, $notificationService) {
            $newStatus = $validated['action'] === 'approve' ? 'approved' : 'rejected';

            $leave->update([
                'status'      => $newStatus,
                'approved_by' => Auth::id(),
                'review_note' => $validated['review_note'] ?? null,
            ]);

            // Jika perizinan disetujui (Approve), sinkronkan record ke tabel attendances untuk seluruh hari kerja
            if ($newStatus === 'approved') {
                $dayNames = [
                    1 => 'senin',
                    2 => 'selasa',
                    3 => 'rabu',
                    4 => 'kamis',
                    5 => 'jumat',
                    6 => 'sabtu',
                    7 => 'minggu',
                ];

                $activeDaysSetting = Setting::getValue('hari_kerja', 'senin,selasa,rabu,kamis,jumat');
                $activeDays = array_map('trim', explode(',', strtolower($activeDaysSetting)));

                $current = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);

                while ($current->lte($end)) {
                    $dayName = $dayNames[$current->dayOfWeekIso] ?? 'senin';

                    // Hanya buat record presensi pada hari kerja aktif
                    if (in_array($dayName, $activeDays)) {
                        Attendance::updateOrCreate(
                            [
                                'user_id' => $leave->user_id,
                                'date'    => $current->toDateString(),
                            ],
                            [
                                'status'          => $leave->type, // izin, sakit, cuti
                                'time_in'         => null,
                                'time_out'        => null,
                                'distance_meters' => null,
                            ]
                        );
                    }

                    $current->addDay();
                }
            }

            // Kirim notifikasi in-app ke karyawan
            if ($leave->user) {
                $statusIndo = $newStatus === 'approved' ? 'disetujui' : 'ditolak';
                $title = $newStatus === 'approved' ? 'Pengajuan Izin Disetujui' : 'Pengajuan Izin Ditolak';
                $noteMsg = !empty($validated['review_note']) ? " Catatan HRD: {$validated['review_note']}" : '';
                $message = "Pengajuan {$leave->type} Anda untuk tanggal {$leave->start_date} s/d {$leave->end_date} telah {$statusIndo} oleh HRD.{$noteMsg}";

                $notificationService->send(
                    $leave->user,
                    $title,
                    $message,
                    $newStatus === 'approved' ? 'leave_approved' : 'leave_rejected'
                );
            }
        });

        $actionText = $validated['action'] === 'approve' ? 'disetujui' : 'ditolak';
        return back()->with('success', "Pengajuan perizinan {$leave->user->name} berhasil {$actionText}.");
    }
}
