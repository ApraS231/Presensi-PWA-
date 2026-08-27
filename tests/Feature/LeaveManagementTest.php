<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');

        $this->karyawan = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Ahmad Fauzi',
            'email'             => 'ahmad@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_employee_can_submit_leave_application_with_attachment(): void
    {
        $file = UploadedFile::fake()->create('surat_dokter.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->karyawan)->post('/karyawan/izin', [
            'type'            => 'sakit',
            'start_date'      => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'end_date'        => Carbon::today('Asia/Makassar')->addDays(2)->format('Y-m-d'),
            'reason'          => 'Demam tinggi dan flu berat sesuai resep dokter.',
            'attachment_file' => $file,
        ]);

        $response->assertRedirect(route('karyawan.izin.index'));
        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->karyawan->id,
            'type'    => 'sakit',
            'status'  => 'pending',
        ]);

        $leave = Leave::where('user_id', $this->karyawan->id)->first();
        $this->assertNotNull($leave->attachment_file);
        Storage::disk('public')->assertExists($leave->attachment_file);
    }

    public function test_retroactive_validation_rejects_dates_beyond_setting_limit(): void
    {
        // Pengajuan tanggal lampau 10 hari yang lalu (melebihi limit default 3 hari)
        $pastDate = Carbon::today('Asia/Makassar')->subDays(10)->format('Y-m-d');

        $response = $this->actingAs($this->karyawan)->post('/karyawan/izin', [
            'type'       => 'izin',
            'start_date' => $pastDate,
            'end_date'   => $pastDate,
            'reason'     => 'Urusan keluarga mendadak minggu lalu.',
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseMissing('leaves', [
            'user_id' => $this->karyawan->id,
        ]);
    }

    public function test_employee_can_edit_and_cancel_pending_leave(): void
    {
        $leave = Leave::create([
            'user_id'    => $this->karyawan->id,
            'type'       => 'izin',
            'start_date' => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'end_date'   => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'reason'     => 'Keperluan keluarga mendesak.',
            'status'     => 'pending',
        ]);

        // 1. Edit Pending Leave
        $responseEdit = $this->actingAs($this->karyawan)->put("/karyawan/izin/{$leave->id}", [
            'type'       => 'cuti',
            'start_date' => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'end_date'   => Carbon::today('Asia/Makassar')->addDay()->format('Y-m-d'),
            'reason'     => 'Cuti tahunan yang diperbarui.',
        ]);
        $responseEdit->assertRedirect(route('karyawan.izin.index'));
        $this->assertEquals('cuti', $leave->fresh()->type);

        // 2. Cancel Pending Leave
        $responseCancel = $this->actingAs($this->karyawan)->patch("/karyawan/izin/{$leave->id}/cancel");
        $responseCancel->assertRedirect(route('karyawan.izin.index'));
        $this->assertEquals('cancelled', $leave->fresh()->status);
    }

    public function test_employee_cannot_edit_or_cancel_approved_leave(): void
    {
        $leave = Leave::create([
            'user_id'     => $this->karyawan->id,
            'type'        => 'cuti',
            'start_date'  => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'end_date'    => Carbon::today('Asia/Makassar')->format('Y-m-d'),
            'reason'      => 'Cuti tahunan.',
            'status'      => 'approved',
            'approved_by' => $this->admin->id,
        ]);

        // Coba edit izin yang sudah approved
        $responseEdit = $this->actingAs($this->karyawan)->get("/karyawan/izin/{$leave->id}/edit");
        $responseEdit->assertRedirect(route('karyawan.izin.index'));

        // Coba batalkan izin yang sudah approved
        $responseCancel = $this->actingAs($this->karyawan)->patch("/karyawan/izin/{$leave->id}/cancel");
        $responseCancel->assertRedirect(route('karyawan.izin.index'));
        $this->assertEquals('approved', $leave->fresh()->status);
    }

    public function test_hrd_can_approve_leave_and_generates_working_day_attendances(): void
    {
        // Rentang tanggal: 24 Agustus 2026 (Senin) s/d 30 Agustus 2026 (Minggu)
        // Hari kerja (Senin-Jumat): 24, 25, 26, 27, 28 Agustus (5 hari)
        // Libur (Sabtu-Minggu): 29, 30 Agustus (2 hari)
        $leave = Leave::create([
            'user_id'    => $this->karyawan->id,
            'type'       => 'cuti',
            'start_date' => '2026-08-24',
            'end_date'   => '2026-08-30',
            'reason'     => 'Cuti tahunan ke luar kota.',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/leaves/{$leave->id}/process", [
            'action'      => 'approve',
            'review_note' => 'Disetujui sesuai kuota cuti tahunan.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $leave->fresh()->status);
        $this->assertEquals($this->admin->id, $leave->fresh()->approved_by);

        // Verifikasi record attendances terbuat untuk 5 hari kerja
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-24',
            'status'  => 'cuti',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-28',
            'status'  => 'cuti',
        ]);

        // Verifikasi akhir pekan TIDAK dibuatkan record
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-29',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-30',
        ]);

        $attendanceCount = Attendance::where('user_id', $this->karyawan->id)->count();
        $this->assertEquals(5, $attendanceCount);

        // Verifikasi notifikasi terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->karyawan->id,
            'title'   => 'Pengajuan Izin Disetujui',
        ]);
    }

    public function test_hrd_can_reject_leave_with_review_note(): void
    {
        $leave = Leave::create([
            'user_id'    => $this->karyawan->id,
            'type'       => 'izin',
            'start_date' => '2026-08-26',
            'end_date'   => '2026-08-26',
            'reason'     => 'Izin urusan pribadi.',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/leaves/{$leave->id}/process", [
            'action'      => 'reject',
            'review_note' => 'Mohon sertakan surat bukti keterangan pendukung.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $leave->fresh()->status);
        $this->assertEquals('Mohon sertakan surat bukti keterangan pendukung.', $leave->fresh()->review_note);

        // Tidak ada record attendances yang dibuat jika ditolak
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-26',
        ]);

        // Verifikasi notifikasi penolakan terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->karyawan->id,
            'title'   => 'Pengajuan Izin Ditolak',
        ]);
    }
}
