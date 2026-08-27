<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Menampilkan riwayat pengajuan perizinan karyawan.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $leaves = Leave::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('karyawan.izin.index', compact('leaves'));
    }

    /**
     * Menampilkan formulir pengajuan izin baru.
     */
    public function create(): View
    {
        $maxRetro = (int) Setting::getValue('max_retroaktif_izin', '3');
        $minDate = Carbon::today('Asia/Makassar')->subDays($maxRetro)->format('Y-m-d');

        return view('karyawan.izin.create', compact('maxRetro', 'minDate'));
    }

    /**
     * Menyimpan pengajuan izin baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'            => 'required|in:izin,sakit,cuti',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'reason'          => 'required|string|min:5|max:1000',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'type.required'            => 'Jenis perizinan wajib dipilih.',
            'start_date.required'      => 'Tanggal mulai izin wajib diisi.',
            'end_date.required'        => 'Tanggal selesai izin wajib diisi.',
            'end_date.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required'          => 'Keterangan alasan izin wajib diisi.',
            'reason.min'               => 'Keterangan alasan minimal 5 karakter.',
            'attachment_file.mimes'    => 'Berkas lampiran harus berformat PDF, JPG, JPEG, atau PNG.',
            'attachment_file.max'      => 'Ukuran berkas lampiran maksimal 2MB.',
        ]);

        // Validasi batas tanggal retroaktif
        $maxRetro = (int) Setting::getValue('max_retroaktif_izin', '3');
        $minAllowedDate = Carbon::today('Asia/Makassar')->subDays($maxRetro)->format('Y-m-d');

        if ($validated['start_date'] < $minAllowedDate) {
            return back()
                ->withErrors(['start_date' => "Pengajuan tanggal lampau maksimal {$maxRetro} hari ke belakang (minimal tanggal {$minAllowedDate})."])
                ->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('leaves', 'public');
        }

        Leave::create([
            'user_id'         => Auth::id(),
            'type'            => $validated['type'],
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'reason'          => $validated['reason'],
            'attachment_file' => $attachmentPath,
            'status'          => 'pending',
        ]);

        return redirect()->route('karyawan.izin.index')->with('success', 'Pengajuan perizinan berhasil dikirim dan menunggu persetujuan HRD.');
    }

    /**
     * Menampilkan formulir edit pengajuan izin yang berstatus pending.
     */
    public function edit(Leave $leave): View|RedirectResponse
    {
        if ($leave->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->route('karyawan.izin.index')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah.');
        }

        $maxRetro = (int) Setting::getValue('max_retroaktif_izin', '3');
        $minDate = Carbon::today('Asia/Makassar')->subDays($maxRetro)->format('Y-m-d');

        return view('karyawan.izin.edit', compact('leave', 'maxRetro', 'minDate'));
    }

    /**
     * Memperbarui pengajuan izin yang berstatus pending.
     */
    public function update(Request $request, Leave $leave): RedirectResponse
    {
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'type'            => 'required|in:izin,sakit,cuti',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'reason'          => 'required|string|min:5|max:1000',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Validasi batas tanggal retroaktif
        $maxRetro = (int) Setting::getValue('max_retroaktif_izin', '3');
        $minAllowedDate = Carbon::today('Asia/Makassar')->subDays($maxRetro)->format('Y-m-d');

        if ($validated['start_date'] < $minAllowedDate) {
            return back()
                ->withErrors(['start_date' => "Pengajuan tanggal lampau maksimal {$maxRetro} hari ke belakang."])
                ->withInput();
        }

        $attachmentPath = $leave->attachment_file;
        if ($request->hasFile('attachment_file')) {
            if ($leave->attachment_file && Storage::disk('public')->exists($leave->attachment_file)) {
                Storage::disk('public')->delete($leave->attachment_file);
            }
            $attachmentPath = $request->file('attachment_file')->store('leaves', 'public');
        }

        $leave->update([
            'type'            => $validated['type'],
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'reason'          => $validated['reason'],
            'attachment_file' => $attachmentPath,
        ]);

        return redirect()->route('karyawan.izin.index')->with('success', 'Pengajuan perizinan berhasil diperbarui.');
    }

    /**
     * Membatalkan pengajuan izin yang berstatus pending.
     */
    public function cancel(Leave $leave): RedirectResponse
    {
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            return redirect()->route('karyawan.izin.index')->with('error', 'Hanya pengajuan pending yang dapat dibatalkan.');
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('karyawan.izin.index')->with('success', 'Pengajuan perizinan berhasil dibatalkan.');
    }
}
