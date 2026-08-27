<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar seluruh notifikasi milik pengguna terautentikasi.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($notifications);
        }

        $unreadCount = $user->notifications()->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mengambil jumlah notifikasi yang belum dibaca (asynchronous badge counter).
     */
    public function unreadCount(): JsonResponse
    {
        $unreadCount = Auth::user()->notifications()->unread()->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Menandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        // Validasi Otorisasi Kepemilikan Data
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk notifikasi ini.');
        }

        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Notifikasi telah ditandai sebagai dibaca.',
                'unread_count' => Auth::user()->notifications()->unread()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Menandai semua notifikasi pengguna sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        Auth::user()->notifications()->unread()->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Seluruh notifikasi telah ditandai sebagai dibaca.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Seluruh notifikasi berhasil ditandai sebagai dibaca.');
    }
}
