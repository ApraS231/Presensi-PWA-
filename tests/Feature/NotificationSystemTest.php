<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_notification_service_creates_notification(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $service = new NotificationService();

        $notif = $service->send(
            $user,
            'Izin Disetujui',
            'Pengajuan izin Anda telah disetujui oleh HRD.',
            'leave_approved'
        );

        $this->assertInstanceOf(Notification::class, $notif);
        $this->assertDatabaseHas('notifications', [
            'id'      => $notif->id,
            'user_id' => $user->id,
            'title'   => 'Izin Disetujui',
            'type'    => 'leave_approved',
            'is_read' => false,
        ]);
    }

    public function test_notification_service_sends_broadcast_to_active_users(): void
    {
        $activeUser1 = User::create([
            'nik'               => 'KAR101',
            'name'              => 'Karyawan 1',
            'email'             => 'kar1@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $activeUser2 = User::create([
            'nik'               => 'KAR102',
            'name'              => 'Karyawan 2',
            'email'             => 'kar2@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $inactiveUser = User::create([
            'nik'               => 'KAR103',
            'name'              => 'Karyawan Nonaktif',
            'email'             => 'kar3@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => false,
            'enrollment_status' => 'pending',
        ]);

        $service = new NotificationService();
        $sentCount = $service->sendBroadcast(
            'Pengumuman Libur Nasional',
            'Besok operasional kantor libur.',
            'Operasional'
        );

        $this->assertEquals(2, $sentCount);
        $this->assertDatabaseHas('notifications', ['user_id' => $activeUser1->id, 'title' => 'Pengumuman Libur Nasional']);
        $this->assertDatabaseHas('notifications', ['user_id' => $activeUser2->id, 'title' => 'Pengumuman Libur Nasional']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $inactiveUser->id, 'title' => 'Pengumuman Libur Nasional']);
    }

    public function test_user_can_view_their_notifications(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $service = new NotificationService();
        $service->send($user, 'Pemberitahuan Khusus', 'Ini adalah isi pesan.');

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Khusus');
        $response->assertSee('Ini adalah isi pesan.');
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user1 = User::where('nik', 'SA001')->first();
        $user2 = User::where('nik', 'ADM001')->first();

        $service = new NotificationService();
        $notifUser2 = $service->send($user2, 'Notif User 2', 'Pesan rahasia');

        $response = $this->actingAs($user1)->patch("/notifications/{$notifUser2->id}/read");
        $response->assertStatus(403);
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $service = new NotificationService();
        $notif = $service->send($user, 'Notif Baca', 'Pesan belum dibaca');

        $this->assertFalse($notif->is_read);

        $response = $this->actingAs($user)->patch("/notifications/{$notif->id}/read");
        $response->assertRedirect();

        $this->assertTrue($notif->fresh()->is_read);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $service = new NotificationService();
        $service->send($user, 'Notif 1', 'Pesan 1');
        $service->send($user, 'Notif 2', 'Pesan 2');
        $service->send($user, 'Notif 3', 'Pesan 3');

        $this->assertEquals(3, $user->notifications()->unread()->count());

        $response = $this->actingAs($user)->post('/notifications/read-all');
        $response->assertRedirect();

        $this->assertEquals(0, $user->notifications()->unread()->count());
    }

    public function test_unread_count_endpoint_returns_accurate_count(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $service = new NotificationService();
        $service->send($user, 'Notif A', 'Pesan A');
        $service->send($user, 'Notif B', 'Pesan B');

        $response = $this->actingAs($user)->getJson('/notifications/unread-count');
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 2]);
    }
}
