# Sprint 1.4 - Service Notifikasi & In-App Notification API

- **Fase:** 1 (Fondasi Proyek & Infrastruktur)
- **Estimasi Durasi:** 2 Hari
- **Prasyarat:** Sprint 1.1 (Tabel notifications) & Sprint 1.3 (Layout PWA/Admin)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-NOTIF-01`: Service backend `NotificationService` untuk mengotomasi pengiriman notifikasi terstruktur ke pengguna tertentu.
- `FR-NOTIF-02`: Endpoint API polling notifikasi (`GET /api/notifications`) untuk mengambil daftar notifikasi belum dibaca dan counter badge.
- `FR-NOTIF-03`: Endpoint API mark-as-read (`POST /api/notifications/{id}/read` dan `POST /api/notifications/read-all`) untuk menandai status baca notifikasi.
- `FR-NOTIF-04`: Tampilan UI Badge Counter notifikasi di Top Bar PWA Mobile dan Header Admin.
- `FR-NOTIF-05`: Dropdown/Panel Notifikasi M3 yang menampilkan judul, pesan, tipe (`leave_approved`, `leave_rejected`, `reminder`), dan waktu relatif (contoh: "5 menit yang lalu").

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Endpoint polling ringan dengan response time < 100ms dan payload JSON minimalis.
- `NFR-SEC-01`: Keamanan data notifikasi — pengguna hanya dapat melihat dan memodifikasi notifikasi milik akunnya sendiri (`user_id = Auth::id()`).

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Notification Service Backend
Target: `app/Services/NotificationService.php`

```php
namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $title, string $message, ?string $type = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'is_read' => false,
        ]);
    }
}
```

### Langkah 2: Controller API Notifikasi
Target: `app/Http/Controllers/Api/NotificationController.php`

1. Method `index()`:
   ```php
   public function index()
   {
       $user = auth()->user();
       $notifications = Notification::where('user_id', $user->id)
           ->orderByDesc('created_at')
           ->limit(20)
           ->get();

       $unreadCount = Notification::where('user_id', $user->id)
           ->where('is_read', false)
           ->count();

       return response()->json([
           'unread_count' => $unreadCount,
           'notifications' => $notifications,
       ]);
   }
   ```
2. Method `markAsRead($id)` & `markAllAsRead()`.

### Langkah 3: Script Polling Client-Side
Target: `public/js/notifications.js`

1. Request polling berkala setiap 30 detik menggunakan `fetch()` ke `/api/notifications`.
2. Update angka badge counter pada elemen DOM `.md-badge-notification`.
3. Render daftar notifikasi ke panel dropdown saat ikon lonceng diklik.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Method `NotificationService::send()` berhasil menyimpan baris notifikasi ke database.
- [ ] Endpoint `/api/notifications` mengembalikan data valid sesuai user yang sedang login.
- [ ] Badge counter di navbar otomatis bertambah saat ada notifikasi baru tanpa reload halaman.
- [ ] Klik notifikasi menandai record menjadi `is_read = true` dan mengurangi counter unread.
