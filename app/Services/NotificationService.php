<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Mengirim notifikasi tunggal ke seorang pengguna.
     */
    public function send(User|int $user, string $title, string $message, ?string $type = null): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'is_read' => false,
        ]);
    }

    /**
     * Mengirim notifikasi ke kumpulan pengguna (massal).
     *
     * @param iterable<User|int> $users
     */
    public function sendBulk(iterable $users, string $title, string $message, ?string $type = null): void
    {
        $records = [];
        $now = now();

        foreach ($users as $user) {
            $userId = $user instanceof User ? $user->id : $user;
            $records[] = [
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'type'       => $type,
                'is_read'    => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            Notification::insert($records);
        }
    }

    /**
     * Mengirim broadcast pengumuman ke seluruh karyawan aktif (atau per departemen tertentu).
     * Mengembalikan jumlah karyawan yang menerima notifikasi.
     */
    public function sendBroadcast(string $title, string $message, ?string $department = null): int
    {
        $query = User::where('is_active', true);

        if ($department) {
            $query->where('department', $department);
        }

        $userIds = $query->pluck('id');

        if ($userIds->isEmpty()) {
            return 0;
        }

        $this->sendBulk($userIds, $title, $message, 'system_announcement');

        return $userIds->count();
    }
}
